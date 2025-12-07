<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Code;
use Illuminate\Database\Seeder;

class CodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = [
            // コード種類定義（type=0）
            ['type' => 0, 'type_id' => 1, 'name' => '雇用形態', 'description' => null, 'sort_order' => 1],
            ['type' => 0, 'type_id' => 2, 'name' => '勤務形態', 'description' => null, 'sort_order' => 2],
            ['type' => 0, 'type_id' => 3, 'name' => '業種', 'description' => null, 'sort_order' => 3],

            // 雇用形態（type=1）
            ['type' => 1, 'type_id' => 1, 'name' => '正社員', 'description' => null, 'sort_order' => 1],
            ['type' => 1, 'type_id' => 2, 'name' => '契約社員', 'description' => null, 'sort_order' => 2],
            ['type' => 1, 'type_id' => 3, 'name' => 'パート', 'description' => null, 'sort_order' => 3],
            ['type' => 1, 'type_id' => 4, 'name' => 'アルバイト', 'description' => null, 'sort_order' => 4],

            // 勤務形態（type=2）
            ['type' => 2, 'type_id' => 1, 'name' => '出社', 'description' => null, 'sort_order' => 1],
            ['type' => 2, 'type_id' => 2, 'name' => '週3日', 'description' => null, 'sort_order' => 2],
            ['type' => 2, 'type_id' => 3, 'name' => '時短', 'description' => null, 'sort_order' => 3],
            ['type' => 2, 'type_id' => 4, 'name' => 'リモート', 'description' => null, 'sort_order' => 4],

            // 業種（type=3）
            ['type' => 3, 'type_id' => 1, 'name' => '飲食', 'description' => null, 'sort_order' => 1],
            ['type' => 3, 'type_id' => 2, 'name' => '製造', 'description' => null, 'sort_order' => 2],
            ['type' => 3, 'type_id' => 3, 'name' => 'システム開発', 'description' => null, 'sort_order' => 3],
            ['type' => 3, 'type_id' => 4, 'name' => '教育', 'description' => null, 'sort_order' => 4],
        ];

        foreach ($codes as $code) {
            Code::query()->create($code);
        }

        $this->command->info('全15件のコードデータ登録が完了しました');
    }
}
