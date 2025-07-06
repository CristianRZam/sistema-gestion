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
            'name' => 'Cristian Rodriguez',
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

        $usuariosExtras = [
            ['name' => 'Luis Perez', 'email' => 'luis.perez@yopmail.com'],
            ['name' => 'Ana Torres', 'email' => 'ana.torres@yopmail.com'],
            ['name' => 'Jorge Rivera', 'email' => 'jorge.rivera@yopmail.com'],
            ['name' => 'Lucia Mendez', 'email' => 'lucia.mendez@yopmail.com'],
            ['name' => 'Carlos Gómez', 'email' => 'carlos.gomez@yopmail.com'],
            ['name' => 'Fernanda Ruiz', 'email' => 'fernanda.ruiz@yopmail.com'],
            ['name' => 'David Salazar', 'email' => 'david.salazar@yopmail.com'],
            ['name' => 'Patricia Quispe', 'email' => 'patricia.quispe@yopmail.com'],
            ['name' => 'Roberto Díaz', 'email' => 'roberto.diaz@yopmail.com'],
        ];

        foreach ($usuariosExtras as $usuario) {
            $nuevoUsuario = User::query()->create([
                'name' => $usuario['name'],
                'email' => $usuario['email'],
                'password' => bcrypt('12345678'),
            ]);

            $nuevoUsuario->assignRole('administrador');
        }
    }

}
