<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Reguser;   
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Creates or updates the admin by email (prevents duplicates)
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // change if you prefer
            [
                'name' => 'Site Admin',
                'username' => 'admin',          // must be unique in your DB
                'password' => Hash::make('Admin!123'),
                'role' => 'admin',
            ]
        );
    }
}
