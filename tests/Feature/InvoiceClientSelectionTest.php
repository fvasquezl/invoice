<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceClientSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TemplateSeeder::class);
    }

    public function test_client_selector_is_visible_when_company_is_selected(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $client = Client::factory()->create(['name' => 'Test Client']);
        $client->companies()->attach($company);
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->assertSee($client->email);
    }

    public function test_client_selector_is_hidden_when_no_company_is_selected(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->assertFormFieldIsHidden('client_id');
    }

    public function test_selecting_client_auto_fills_client_fields(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $client = Client::factory()->create([
            'name' => 'Globex Corp',
            'email' => 'globex@example.com',
            'phone' => '+1 555 9999',
            'address' => '742 Evergreen Terrace',
        ]);
        $client->companies()->attach($company);
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->call('fillFromClient', $client->id)
            ->assertSet('data.client_name', 'Globex Corp')
            ->assertSet('data.client_email', 'globex@example.com')
            ->assertSet('data.client_phone', '+1 555 9999')
            ->assertSet('data.client_address', '742 Evergreen Terrace');
    }

    public function test_clearing_client_resets_client_fields(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $client = Client::factory()->create(['name' => 'Globex Corp']);
        $client->companies()->attach($company);
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->call('fillFromClient', $client->id)
            ->assertSet('data.client_name', 'Globex Corp')
            ->call('fillFromClient', null)
            ->assertSet('data.client_name', '')
            ->assertSet('data.client_id', null);
    }

    public function test_changing_company_resets_client_selection(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $client = Client::factory()->create(['name' => 'Old Client']);
        $client->companies()->attach($company);
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->call('fillFromClient', $client->id)
            ->assertSet('data.client_name', 'Old Client')
            ->call('fillFromCompany', null)
            ->assertSet('data.client_id', null)
            ->assertSet('data.client_name', '');
    }

    public function test_invoice_is_linked_to_selected_client(): void
    {
        Route::get('/invoice/{invoice}/download', fn () => redirect('/'))->name('invoice.download');

        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $client = Client::factory()->create(['name' => 'Test Client']);
        $client->companies()->attach($company);
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->call('fillFromClient', $client->id)
            ->set('data.invoice_date', now()->format('Y-m-d'))
            ->set('data.due_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.items', [[
                'description' => 'Service',
                'quantity' => 1,
                'unit_price' => 100,
            ]])
            ->call('handleDownload');

        $this->assertDatabaseHas('invoices', [
            'client_id' => $client->id,
        ]);
    }

    public function test_new_client_is_created_when_manual_data_is_entered(): void
    {
        Route::get('/invoice/{invoice}/download', fn () => redirect('/'))->name('invoice.download');

        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->set('data.client_name', 'Brand New Client')
            ->set('data.client_email', 'new@client.com')
            ->set('data.invoice_date', now()->format('Y-m-d'))
            ->set('data.due_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.items', [[
                'description' => 'Service',
                'quantity' => 1,
                'unit_price' => 200,
            ]])
            ->call('handleDownload');

        $this->assertDatabaseHas('clients', [
            'name' => 'Brand New Client',
            'email' => 'new@client.com',
        ]);

        $client = Client::where('name', 'Brand New Client')->first();
        $this->assertNotNull($client);

        $this->assertDatabaseHas('client_company', [
            'client_id' => $client->id,
            'company_id' => $company->id,
        ]);

        $this->assertDatabaseHas('invoices', [
            'client_id' => $client->id,
        ]);
    }

    public function test_existing_client_is_reused_when_email_matches(): void
    {
        Route::get('/invoice/{invoice}/download', fn () => redirect('/'))->name('invoice.download');

        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        $existing = Client::factory()->create([
            'name'  => 'Returning Client',
            'email' => 'returning@client.com',
        ]);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->set('data.client_name', 'Returning Client')
            ->set('data.client_email', 'returning@client.com')
            ->set('data.invoice_date', now()->format('Y-m-d'))
            ->set('data.due_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.items', [[
                'description' => 'Service',
                'quantity' => 1,
                'unit_price' => 100,
            ]])
            ->call('handleDownload');

        // No duplicate client created
        $this->assertSame(1, Client::where('email', 'returning@client.com')->count());

        $this->assertDatabaseHas('invoices', [
            'client_id' => $existing->id,
        ]);
    }

    public function test_email_lookup_auto_fills_client_fields(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        $client = Client::factory()->create([
            'name'    => 'Known Client',
            'email'   => 'known@client.com',
            'phone'   => '+1 555 0001',
            'address' => '1 Known Street',
        ]);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->call('lookupClientByEmail', 'known@client.com')
            ->assertSet('data.client_id', $client->id)
            ->assertSet('data.client_name', 'Known Client')
            ->assertSet('data.client_email', 'known@client.com')
            ->assertSet('data.client_phone', '+1 555 0001')
            ->assertSet('data.client_address', '1 Known Street');
    }

    public function test_new_client_created_when_email_is_new(): void
    {
        Route::get('/invoice/{invoice}/download', fn () => redirect('/'))->name('invoice.download');

        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->set('data.client_name', 'First Time Client')
            ->set('data.client_email', 'firsttime@example.com')
            ->set('data.invoice_date', now()->format('Y-m-d'))
            ->set('data.due_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.items', [[
                'description' => 'Service',
                'quantity' => 1,
                'unit_price' => 50,
            ]])
            ->call('handleDownload');

        $this->assertDatabaseHas('clients', [
            'name'  => 'First Time Client',
            'email' => 'firsttime@example.com',
        ]);

        $this->assertSame(1, Client::where('email', 'firsttime@example.com')->count());
    }
}
