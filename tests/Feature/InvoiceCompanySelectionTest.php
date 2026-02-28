<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceCompanySelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TemplateSeeder::class);
    }

    public function test_company_selector_is_visible_for_user_with_companies(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->assertSee($company->company_name);
    }

    public function test_company_selector_is_hidden_for_user_without_companies(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Field is not rendered when user has no companies — no error expected
        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->assertFormFieldIsHidden('company_id');
    }

    public function test_selecting_a_company_fills_company_fields(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'company_name'    => 'Acme Corp',
            'company_email'   => 'acme@example.com',
            'company_phone'   => '+1 555 0001',
            'company_address' => '1 Acme Road',
        ]);
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->set('data.company_id', $company->id)
            ->call('fillFromCompany', $company->id)
            ->assertSet('data.company_name', 'Acme Corp')
            ->assertSet('data.company_email', 'acme@example.com')
            ->assertSet('data.company_phone', '+1 555 0001')
            ->assertSet('data.company_address', '1 Acme Road');
    }

    public function test_selecting_a_company_stores_its_logo(): void
    {
        $logo = 'data:image/png;base64,' . base64_encode('fake-logo');

        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create(['company_logo' => $logo]);
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->assertSet('data.company_logo', $logo);
    }

    public function test_clearing_company_selection_resets_company_fields(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create(['company_name' => 'Acme Corp']);
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->assertSet('data.company_name', 'Acme Corp')
            ->call('fillFromCompany', null)
            ->assertSet('data.company_name', '')
            ->assertSet('data.company_logo', null);
    }

    public function test_company_id_is_saved_on_invoice_creation(): void
    {
        Route::get('/invoice/{invoice}/download', fn () => redirect('/'))->name('invoice.download');

        /** @var User $user */
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        Livewire::actingAs($user)
            ->test('pages::invoice.create')
            ->call('fillFromCompany', $company->id)
            ->set('data.client_name', 'Test Client')
            ->set('data.invoice_date', now()->format('Y-m-d'))
            ->set('data.due_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.items', [[
                'description' => 'Service',
                'quantity'    => 1,
                'unit_price'  => 100,
            ]])
            ->call('handleDownload');

        $this->assertDatabaseHas('invoices', [
            'user_id'    => $user->id,
            'company_id' => $company->id,
        ]);
    }
}
