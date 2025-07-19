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
            'name' => '管理者',
            'email' => 'admin@coachtech.com',
            'password' => Hash::make('adminpass'),
            'is_admin' => 1,
        ]);

        User::create([
            'name'  => '西怜奈',
            'email'     => 'reina.n@coachtech.com',
            'password'  => Hash::make('password123'),
            'is_admin' => 0,
        ]);

        User::create([
            'name'  => '山田太郎',
            'email'     => 'tarou.y@coachtech.com',
            'password'  => Hash::make('password456'),
            'is_admin' => 0,
        ]);

        User::create([
            'name'  => '増田一世',
            'email'     => 'issei.m@coachtech.com',
            'password'  => Hash::make('password789'),
            'is_admin' => 0,
        ]);

        User::create([
            'name'  => '山本敬吉',
            'email'     => 'keikichi.y@coachtech.com',
            'password'  => Hash::make('password012'),
            'is_admin' => 0,
        ]);

        User::create([
            'name'  => '秋田朋美',
            'email'     => 'tomomi.a@coachtech.com',
            'password'  => Hash::make('password987'),
            'is_admin' => 0,
        ]);

        User::create([
            'name'  => '中西教生',
            'email'     => 'norio.n@coachtech.com',
            'password'  => Hash::make('password654'),
            'is_admin' => 0,
        ]);
    }
}
