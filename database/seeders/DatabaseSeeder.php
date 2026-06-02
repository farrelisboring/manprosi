<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole; 
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::factory()->create([
            'name' => 'Fadhil Staff',
            'email' => 'staff@rs.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Staff, 
        ]);

        User::factory()->create([
            'name' => 'Admin Manager',
            'email' => 'manager@rs.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Manager, 
        ]);

        User::factory()->create([
            'name' => 'Suster Nurse',
            'email' => 'nurse@rs.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Nurse,
        ]);
    }
}
