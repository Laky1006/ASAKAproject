<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Reguser;
use Illuminate\Support\Facades\Hash;

class ReguserSeeder extends Seeder
{
    public function run(): void
    {

        $regusers = [
            //TEST user
            [
                'name' => 'Test user',
                'username' => 'test',
                'email' => 'test@example.com',
                'password' => 'password',
                'role' => 'reguser',
            ],
            [
                'name' => 'Alise Trololo',
                'username' => 'alice_reguser',
                'email' => 'alice@example.com',
                'password' => 'Password1!',
                'role' => 'reguser',
            ],
            [
                'name' => 'John Learner',
                'username' => 'john123',
                'email' => 'john@example.com',
                'password' => 'Learning@1',
                'role' => 'reguser',
            ],
            [
                'name' => 'Laura Curious',
                'username' => 'curiouslaura',
                'email' => 'laura@example.com',
                'password' => 'Smart#456',
                'role' => 'reguser',
            ],
        ];

        foreach ($regusers as $data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ]);

            Reguser::create([
                'user_id' => $user->id,
            ]);
        }
    }
}
