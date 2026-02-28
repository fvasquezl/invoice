<?php

namespace Tests\Feature;

use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EditCompanyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);
    }

    public function test_saves_image_when_company_has_no_logo_and_new_image_is_uploaded(): void
    {
        $company = Company::factory()->create(['logo' => null]);

        Livewire::test(EditCompany::class, ['record' => $company->getRouteKey()])
            ->fillForm([
                'name' => $company->name,
                'logo' => [UploadedFile::fake()->image('logo.png')],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertStringStartsWith('data:image/', $company->fresh()->logo);
    }

    public function test_keeps_existing_logo_when_company_logo_is_empty_on_save(): void
    {
        $existingLogo = 'data:image/png;base64,'.base64_encode('fake-logo-content');
        $company = Company::factory()->create(['logo' => $existingLogo]);

        Livewire::test(EditCompany::class, ['record' => $company->getRouteKey()])
            ->fillForm([
                'name' => $company->name,
                'logo' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($existingLogo, $company->fresh()->logo);
    }

    public function test_replaces_existing_logo_when_new_image_is_uploaded(): void
    {
        $existingLogo = 'data:image/png;base64,'.base64_encode('old-logo-content');
        $company = Company::factory()->create(['logo' => $existingLogo]);

        Livewire::test(EditCompany::class, ['record' => $company->getRouteKey()])
            ->fillForm([
                'name' => $company->name,
                'logo' => [UploadedFile::fake()->image('new-logo.jpg')],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedLogo = $company->fresh()->logo;

        $this->assertStringStartsWith('data:image/', $updatedLogo);
        $this->assertNotSame($existingLogo, $updatedLogo);
    }
}
