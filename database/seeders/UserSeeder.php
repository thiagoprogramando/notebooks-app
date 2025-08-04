<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
   
    public function run(): void {
        User::updateOrCreate(
            ['email' => 'admin@school.com'],
            [
                'name'      => 'Admin',
                'password'  => Hash::make('123456'),
                'role'      => 'admin',
                'cpfcnpj'   => '60730811000170'
            ]
        );
    }
}
