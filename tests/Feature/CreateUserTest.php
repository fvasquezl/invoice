<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
    }

    public function test_password_is_required_on_create(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New User',
                'email' => 'new@example.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['password' => 'required']);
    }

    public function test_can_create_user_with_minimum_fields(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New User',
                'email' => 'new@example.com',
                'password' => 'password',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_can_create_user_with_role(): void
    {
        $role = Role::where('name', 'cashier')->first();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Cashier User',
                'email' => 'cashier@example.com',
                'password' => 'password',
                'roles' => [$role->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'cashier@example.com')->first();
        $this->assertTrue($user->hasRole('cashier'));
    }

    public function test_can_create_user_with_companies(): void
    {
        $company = Company::factory()->create();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Cashier User',
                'email' => 'cashier@example.com',
                'password' => 'password',
                'companies' => [$company->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'cashier@example.com')->first();
        $this->assertTrue($user->companies->contains($company));
    }

    public function test_can_create_user_with_role_and_companies(): void
    {
        $role = Role::where('name', 'cashier')->first();
        $companies = Company::factory(2)->create();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Cashier User',
                'email' => 'cashier@example.com',
                'password' => 'password',
                'roles' => [$role->id],
                'companies' => $companies->pluck('id')->toArray(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'cashier@example.com')->first();
        $this->assertTrue($user->hasRole('cashier'));
        $this->assertCount(2, $user->companies);
    }
}
