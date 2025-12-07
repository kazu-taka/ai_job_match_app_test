<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CompanyProfile;
use App\Models\Location;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 所在地のコードからIDを取得
        $locationCodes = [
            '011002' => Location::query()->where('code', '011002')->first()?->id, // 札幌市
            '131016' => Location::query()->where('code', '131016')->first()?->id, // 東京都千代田区
            '271004' => Location::query()->where('code', '271004')->first()?->id, // 大阪市
            '401005' => Location::query()->where('code', '401005')->first()?->id, // 福岡市
            '231002' => Location::query()->where('code', '231002')->first()?->id, // 名古屋市
        ];

        // 企業プロフィール（5件）
        $profiles = [
            [
                'user_id' => 1,
                'location_id' => $locationCodes['011002'],
                'address' => '北海道札幌市中央区北1条西5丁目',
                'representative' => '山田　太郎',
                'phone_number' => '011-123-4567',
            ],
            [
                'user_id' => 2,
                'location_id' => $locationCodes['131016'],
                'address' => '東京都千代田区丸の内1-1-1',
                'representative' => '田中　次郎',
                'phone_number' => '03-1234-5678',
            ],
            [
                'user_id' => 3,
                'location_id' => $locationCodes['271004'],
                'address' => '大阪府大阪市北区梅田1-1-1',
                'representative' => '鈴木　三郎',
                'phone_number' => '06-1234-5678',
            ],
            [
                'user_id' => 4,
                'location_id' => $locationCodes['401005'],
                'address' => '福岡県福岡市博多区博多駅前1-1-1',
                'representative' => '高橋　四郎',
                'phone_number' => '092-123-4567',
            ],
            [
                'user_id' => 5,
                'location_id' => $locationCodes['231002'],
                'address' => '愛知県名古屋市中区栄1-1-1',
                'representative' => '伊藤　五郎',
                'phone_number' => '052-123-4567',
            ],
        ];

        foreach ($profiles as $profile) {
            CompanyProfile::query()->create($profile);
        }
    }
}
