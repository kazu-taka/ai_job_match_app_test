<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ChatRoom;
use App\Models\JobApplication;
use Illuminate\Database\Seeder;

/**
 * チャットルームシーダー
 *
 * すべての既存応募に対してチャットルームを作成します。
 * 通常は応募時に自動作成されますが、既存データへの補完として実行します。
 */
class ChatRoomSeeder extends Seeder
{
    /**
     * シーダーの実行
     */
    public function run(): void
    {
        $this->command->info('チャットルームの作成を開始します...');

        // すべての応募を取得
        $applications = JobApplication::all();

        $this->command->info("応募件数: {$applications->count()}件");

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($applications as $application) {
            // 既にチャットルームが存在する場合はスキップ
            if (ChatRoom::where('application_id', $application->id)->exists()) {
                $skippedCount++;

                continue;
            }

            // チャットルームを作成
            ChatRoom::create([
                'application_id' => $application->id,
            ]);

            $createdCount++;
        }

        $this->command->info("チャットルーム作成完了: {$createdCount}件");

        if ($skippedCount > 0) {
            $this->command->warn("既存のチャットルームをスキップ: {$skippedCount}件");
        }

        $this->command->newLine();
    }
}
