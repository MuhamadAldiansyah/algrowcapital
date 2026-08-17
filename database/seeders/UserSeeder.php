<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Developer',
            'username' => 'developer',
            'email' => 'developer@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('aldi'),
            'role' => 'developer',
        ]);

        \App\Models\User::create([
            'name' => 'Administrator 1',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('aldi'),
            'role' => 'admin',
        ]);

        \App\Models\User::create([
            'name' => 'Administrator 2',
            'username' => 'admin2',
            'email' => 'admin2@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('aldi'),
            'role' => 'admin',
        ]);
    }
}
