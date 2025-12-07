<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 企業ユーザー（5件）
        $companies = [
            [
                'name' => '株式会社北海水産',
                'email' => 'hokkai@suisan.co.jp',
                'password' => Hash::make('password'),
                'role' => 'company',
                'email_verified_at' => now(),
            ],
            [
                'name' => '東京テクノロジー',
                'email' => 'tokyo@tech-tokyo.jp',
                'password' => Hash::make('password'),
                'role' => 'company',
                'email_verified_at' => now(),
            ],
            [
                'name' => '大阪商事株式会社',
                'email' => 'osaka@osaka-shoji.com',
                'password' => Hash::make('password'),
                'role' => 'company',
                'email_verified_at' => now(),
            ],
            [
                'name' => '福岡フーズ',
                'email' => 'fukuoka@foods-fukuoka.jp',
                'password' => Hash::make('password'),
                'role' => 'company',
                'email_verified_at' => now(),
            ],
            [
                'name' => '名古屋エンジニアリング',
                'email' => 'nagoya@nagoya-eng.co.jp',
                'password' => Hash::make('password'),
                'role' => 'company',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($companies as $company) {
            User::query()->create($company);
        }

        // ワーカーユーザー（5件）
        $workers = [
            [
                'name' => '佐藤 太郎',
                'email' => 'sato.taro@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'email_verified_at' => now(),
            ],
            [
                'name' => '田中 花子',
                'email' => 'tanaka.hanako@yahoo.co.jp',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'email_verified_at' => now(),
            ],
            [
                'name' => '鈴木 一郎',
                'email' => 'suzuki.ichiro@outlook.com',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'email_verified_at' => now(),
            ],
            [
                'name' => '高橋 美咲',
                'email' => 'takahashi.misaki@icloud.com',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'email_verified_at' => now(),
            ],
            [
                'name' => '渡辺 健太',
                'email' => 'watanabe.kenta@hotmail.com',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($workers as $worker) {
            User::query()->create($worker);
        }
    }
}
