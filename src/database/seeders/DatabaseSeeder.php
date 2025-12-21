<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;   // 一般ユーザー用モデル
use App\Models\Master; // 管理者ユーザー用モデル（作成している場合）
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. 一般ユーザーの作成 (usersテーブル)
        User::create([
            'name'     => '一般 ユーザー',
            'email'    => 'user@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. 管理者ユーザーの作成 (mastersテーブル)
        // 注意: Masterモデルを作成していない場合は DB::table('masters')->insert(...) を使います
        Master::create([
            'name'     => '管理者 ユーザー',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
