<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => '鈴木 花子',
                'email' => 'test2@example.com',
                'password' => Hash::make('password!!'),
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => '佐藤 次郎',
                'email' => 'test3@example.com',
                'password' => Hash::make('password!!'),
                'email_verified_at' => Carbon::now(),
            ],
        ];
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
