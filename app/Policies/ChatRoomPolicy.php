<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ChatRoom;
use App\Models\User;

/**
 * チャットルームポリシー
 *
 * チャットルームへのアクセス制御を管理します。
 * チャットルームには、応募したワーカーと求人を投稿した企業のみがアクセスできます。
 */
class ChatRoomPolicy
{
    /**
     * チャットルームの閲覧権限を判定
     *
     * - 応募したワーカー本人
     * - 求人を投稿した企業ユーザー
     * のいずれかの場合のみ閲覧可能
     */
    public function view(User $user, ChatRoom $chatRoom): bool
    {
        // リレーションを先読み込み
        $chatRoom->load(['application.worker', 'application.jobPost.company']);

        // 応募したワーカー本人
        if ($chatRoom->application->worker_id === $user->id) {
            return true;
        }

        // 求人を投稿した企業ユーザー
        if ($chatRoom->application->jobPost->company_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * チャットルームの作成権限を判定
     *
     * チャットルームは応募時に自動作成されるため、
     * 直接的な作成権限は不要だが、応募権限があるユーザーのみ作成可能
     */
    public function create(User $user): bool
    {
        // ワーカーのみチャットルームを作成可能（応募時に自動作成）
        return $user->role === 'worker';
    }

    /**
     * チャットルームの削除権限を判定
     *
     * チャットルームは応募情報と連動して削除されるため、
     * 直接的な削除権限は不要
     */
    public function delete(User $user, ChatRoom $chatRoom): bool
    {
        // チャットルームの直接削除は許可しない
        // 応募情報の削除に伴うCASCADE削除のみ
        return false;
    }
}
