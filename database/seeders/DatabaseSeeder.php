<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // マスタデータを最初に登録
        $this->call([
            LocationSeeder::class,
            CodeSeeder::class,
        ]);

        // ユーザーデータを登録
        $this->call([
            UserSeeder::class,
        ]);

        // プロフィールデータを登録
        $this->call([
            WorkerProfileSeeder::class,
            CompanyProfileSeeder::class,
        ]);

        // 求人データを登録
        $this->call([
            JobPostSeeder::class,
        ]);
    }
}
