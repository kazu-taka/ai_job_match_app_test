<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use App\Models\WorkerProfile;
use Illuminate\Database\Seeder;

class WorkerProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 希望勤務地のコードからIDを取得
        $locationCodes = [
            '011002' => Location::query()->where('code', '011002')->first()?->id, // 札幌市
            '131016' => Location::query()->where('code', '131016')->first()?->id, // 東京都千代田区
            '271004' => Location::query()->where('code', '271004')->first()?->id, // 大阪市
            '401005' => Location::query()->where('code', '401005')->first()?->id, // 福岡市
            '231002' => Location::query()->where('code', '231002')->first()?->id, // 名古屋市
        ];

        // ワーカープロフィール（5件）
        $profiles = [
            [
                'user_id' => 6,
                'gender' => 'male',
                'birthdate' => '1990-05-15',
                'skills' => '接客、英語（TOEIC750点）、Excel',
                'experiences' => '飲食店勤務 5年、ホテルフロント 3年',
                'desired_jobs' => '接客・サービス',
                'desired_location_id' => $locationCodes['011002'],
            ],
            [
                'user_id' => 7,
                'gender' => 'female',
                'birthdate' => '1995-08-22',
                'skills' => 'プログラミング（PHP、JavaScript）、Laravel',
                'experiences' => 'Web開発 3年、フリーランス 2年',
                'desired_jobs' => 'エンジニア',
                'desired_location_id' => $locationCodes['131016'],
            ],
            [
                'user_id' => 8,
                'gender' => 'male',
                'birthdate' => '1988-03-10',
                'skills' => '営業、プレゼンテーション、Excel',
                'experiences' => '営業職 8年、チームリーダー 2年',
                'desired_jobs' => '営業・企画',
                'desired_location_id' => $locationCodes['271004'],
            ],
            [
                'user_id' => 9,
                'gender' => 'female',
                'birthdate' => '1992-11-30',
                'skills' => 'デザイン（Photoshop、Illustrator）、UI/UX',
                'experiences' => 'グラフィックデザイン 4年',
                'desired_jobs' => 'デザイナー',
                'desired_location_id' => $locationCodes['401005'],
            ],
            [
                'user_id' => 10,
                'gender' => 'male',
                'birthdate' => '1993-07-05',
                'skills' => '会計、簿記2級、Excel',
                'experiences' => '経理事務 5年',
                'desired_jobs' => '事務・経理',
                'desired_location_id' => $locationCodes['231002'],
            ],
        ];

        foreach ($profiles as $profile) {
            WorkerProfile::query()->create($profile);
        }
    }
}
