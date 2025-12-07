<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = base_path('files/市区町村コード.csv');

        if (! File::exists($csvPath)) {
            $this->command->error("CSVファイルが見つかりません: {$csvPath}");

            return;
        }

        $file = fopen($csvPath, 'r');
        if ($file === false) {
            $this->command->error('CSVファイルを開けませんでした');

            return;
        }

        // ヘッダー行をスキップ
        fgetcsv($file);

        $locations = [];
        $count = 0;

        while (($row = fgetcsv($file)) !== false) {
            $locations[] = [
                'code' => $row[0], // 団体コード
                'prefecture' => $row[1], // 都道府県名（漢字）
                'city' => empty($row[2]) ? null : $row[2], // 市区町村名（漢字）
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $count++;

            // 500件ごとにバッチ挿入と進捗表示
            if ($count % 500 === 0) {
                Location::query()->insert($locations);
                $locations = [];
                $this->command->info("{$count}件のデータを登録しました");
            }
        }

        // 残りのデータを挿入
        if (! empty($locations)) {
            Location::query()->insert($locations);
            $this->command->info("{$count}件のデータを登録しました");
        }

        fclose($file);

        $this->command->info("全{$count}件のデータ登録が完了しました");
    }
}
