<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRoleEnums;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => bcrypt('user'),
        ]);

        $vendor = User::factory()->create([
            'name' => 'vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('vendor'),
        ]);

        $vendor->assignRole(UserRoleEnums::Vendor->value);

        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin'),
        ]);

        $admin->assignRole(UserRoleEnums::Admin->value);
    }
}
