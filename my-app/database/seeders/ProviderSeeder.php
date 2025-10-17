<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Provider;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $users = [
            [
                'name' => 'Test Provider',
                'username' => 'test_',
                'email' => 'test2025@example.com',
                'password' => '12345678',
                'role' => 'provider',
            ],
            [
                'name' => 'Bob Bobly',
                'username' => 'bob_provider',
                'email' => 'bob@example.com',
                'password' => '12345678',
                'role' => 'provider',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);

            Provider::create([
                'user_id' => $user->id,
                'location' => 'Riga',
                'bio' => 'Lalalalala',
            ]);
        }
    }
}
