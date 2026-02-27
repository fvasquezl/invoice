<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TemplateSeeder::class);
    }

    public function test_guest_can_access_create_invoice_page(): void
    {
        $this->withoutVite();

        $this->get(route('create-invoice'))
            ->assertOk();
    }

    public function test_authenticated_user_can_access_create_invoice_page(): void
    {
        $this->withoutVite();

        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('create-invoice'))
            ->assertOk();
    }

    public function test_authenticated_user_can_logout(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
