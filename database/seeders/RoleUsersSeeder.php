<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleUsersSeeder extends Seeder
{
    /**
     * Seed one starter user for each supported role.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Staff User',
                'email' => 'staff@example.com',
                'role' => UserRole::Staff,
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'role' => UserRole::Manager,
            ],
            [
                'name' => 'Nurse User',
                'email' => 'nurse@example.com',
                'role' => UserRole::Nurse,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => 'password',
                ],
            );
        }
    }
}
