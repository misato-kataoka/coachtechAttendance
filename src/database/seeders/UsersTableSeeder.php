<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name'  => '鈴木一郎',
            'email'     => 'testuser@example.com',
            'password'  => Hash::make('password123'),
        ]);

        User::create([
            'name'  => '山田花子',
            'email'     => 'sampleuser@example.com',
            'password'  => Hash::make('password456'),
        ]);
    }
}
