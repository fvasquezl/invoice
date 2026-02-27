<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CompanySeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(ShieldSeeder::class);
        $this->call(TemplateSeeder::class);


        $users = [
            ['name' => 'Faustino Vasquez', 'email' => 'fvasquez@local.com'],
            ['name' => 'Sebastian Vasquez', 'email' => 'svasquez@local.com'],
            ['name' => 'Other User', 'email' => 'other@local.com'],
        ];

        collect($users)->each(function ($user) {
            User::factory()->create($user);
        });


        User::find(1)->assignRole('super_admin');
        User::find(2)->assignRole('cashier');
        User::find(3)->assignRole('cashier');

    }
}
