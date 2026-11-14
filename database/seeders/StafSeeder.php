<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StafSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'kasir@kedaisenja.test'],
            [
                'name' => 'Kasir Senja',
                'password' => Hash::make('kasir12345'),
                'role' => 'kasir',
            ]
        );

        User::updateOrCreate(
            ['email' => 'manajer@kedaisenja.test'],
            [
                'name' => 'Manajer Senja',
                'password' => Hash::make('manajer12345'),
                'role' => 'manajer',
            ]
        );
    }
}