<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => '山田 太郎',
                'email' => 'test1@example.com',
                'password' => Hash::make('password!!'),
            ],
            [
                'name' => '鈴木 花子',
                'email' => 'test2@example.com',
                'password' => Hash::make('password!!'),
            ],
        ];
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
