<?php

namespace Tests\Feature;

use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_client_via_filament(): void
    {
        $company = Company::factory()->create();

        Livewire::test(CreateClient::class)
            ->fillForm([
                'companies' => [$company->id],
                'name' => 'Acme Client',
                'email' => 'client@acme.com',
                'phone' => '+1 555 1234',
                'address' => '123 Client Street',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('clients', [
            'name' => 'Acme Client',
            'email' => 'client@acme.com',
        ]);

        $client = Client::where('name', 'Acme Client')->first();
        $this->assertNotNull($client);
        $this->assertDatabaseHas('client_company', [
            'client_id' => $client->id,
            'company_id' => $company->id,
        ]);
    }

    public function test_can_edit_client_via_filament(): void
    {
        $client = Client::factory()->create(['name' => 'Old Name']);

        Livewire::test(EditClient::class, ['record' => $client->getRouteKey()])
            ->fillForm(['name' => 'New Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('New Name', $client->fresh()->name);
    }

    public function test_name_is_required(): void
    {
        $company = Company::factory()->create();

        Livewire::test(CreateClient::class)
            ->fillForm([
                'companies' => [$company->id],
                'name' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_companies_are_required(): void
    {
        Livewire::test(CreateClient::class)
            ->fillForm([
                'companies' => [],
                'name' => 'Some Client',
            ])
            ->call('create')
            ->assertHasFormErrors(['companies' => 'required']);
    }
}
