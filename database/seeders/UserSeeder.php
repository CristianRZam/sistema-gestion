<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->create([
            'name' => 'Cristian Rodriguez Zambrano',
            'email' => 'pruebarod@yopmail.com',
            'password' => bcrypt('12345678'),
        ]);

        $userDos = User::query()->create([
            'name' => 'Xiomara Sanchez',
            'email' => 'pruebaSan@yopmail.com',
            'password' => bcrypt('12345678'),
        ]);

        $user->assignRole('super administrador');
        $userDos->assignRole('administrador');
    }
}
