<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EditUserTest extends TestCase
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

    public function test_password_is_not_required_on_edit(): void
    {
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    public function test_existing_password_is_preserved_when_not_provided_on_edit(): void
    {
        $originalHash = Hash::make('original-password');
        $user = User::factory()->create(['password' => $originalHash]);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($originalHash, $user->fresh()->password);
    }

    public function test_can_update_password_on_edit(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'new-password',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_can_assign_role_on_edit(): void
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'cashier')->first();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$role->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($user->fresh()->hasRole('cashier'));
    }

    public function test_can_change_role_on_edit(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $cashier = Role::where('name', 'cashier')->first();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$cashier->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $user->fresh();
        $this->assertTrue($fresh->hasRole('cashier'));
        $this->assertFalse($fresh->hasRole('admin'));
    }

    public function test_can_assign_companies_on_edit(): void
    {
        $user = User::factory()->create();
        $companies = Company::factory(2)->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'companies' => $companies->pluck('id')->toArray(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertCount(2, $user->fresh()->companies);
    }

    public function test_can_change_companies_on_edit(): void
    {
        $user = User::factory()->create();
        $oldCompany = Company::factory()->create();
        $user->companies()->attach($oldCompany);

        $newCompany = Company::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'companies' => [$newCompany->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $freshCompanies = $user->fresh()->companies;
        $this->assertTrue($freshCompanies->contains($newCompany));
        $this->assertFalse($freshCompanies->contains($oldCompany));
    }

    public function test_can_remove_all_companies_on_edit(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'companies' => [],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertCount(0, $user->fresh()->companies);
    }
}
