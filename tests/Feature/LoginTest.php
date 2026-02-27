<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TemplateSeeder::class);
    }

    // --- Login page ---

    public function test_guest_can_view_login_page(): void
    {
        $this->withoutVite();

        $this->get(route('login'))
            ->assertOk();
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->withoutVite();

        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('create-invoice'));
    }

    // --- Login form (Livewire component) ---

    public function test_user_can_login_with_valid_credentials(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Livewire::test('pages::auth.login')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('create-invoice'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        Livewire::test('pages::auth.login')
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_user_cannot_login_with_unknown_email(): void
    {
        Livewire::test('pages::auth.login')
            ->set('email', 'nobody@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_requires_email(): void
    {
        Livewire::test('pages::auth.login')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email' => 'required']);
    }

    public function test_login_requires_password(): void
    {
        Livewire::test('pages::auth.login')
            ->set('email', 'user@example.com')
            ->call('login')
            ->assertHasErrors(['password' => 'required']);
    }

    // --- Auth modal ---

    public function test_user_can_login_via_modal(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Livewire::test('auth-modal')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_modal_shows_error_on_invalid_credentials(): void
    {
        Livewire::test('auth-modal')
            ->set('email', 'wrong@example.com')
            ->set('password', 'wrong')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_user_can_register_via_modal(): void
    {
        Livewire::test('auth-modal')
            ->set('name', 'New User')
            ->set('email', 'new@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('terms', true)
            ->call('register')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertAuthenticated();
    }
}
