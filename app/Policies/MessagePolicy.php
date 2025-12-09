<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

/**
 * メッセージポリシー
 *
 * チャットメッセージへのアクセス制御を管理します。
 * メッセージの送信、閲覧、既読更新の権限を判定します。
 */
class MessagePolicy
{
    /**
     * メッセージの閲覧権限を判定
     *
     * チャットルームに参加しているユーザー（ワーカーまたは企業）のみ閲覧可能
     */
    public function view(User $user, Message $message): bool
    {
        // リレーションを先読み込み
        $message->load(['chatRoom.application.worker', 'chatRoom.application.jobPost.company']);

        // 応募したワーカー本人
        if ($message->chatRoom->application->worker_id === $user->id) {
            return true;
        }

        // 求人を投稿した企業ユーザー
        if ($message->chatRoom->application->jobPost->company_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * メッセージの送信権限を判定
     *
     * チャットルームに参加しているユーザー（ワーカーまたは企業）のみ送信可能
     * この判定は、チャットルームIDを元に行う必要があるため、
     * 実際の使用時はチャットルームモデルを先に取得してから判定する
     */
    public function create(User $user): bool
    {
        // メッセージ送信権限は、チャットルームへのアクセス権限と同じ
        // 実際の判定は、ChatRoomPolicyのviewメソッドを使用
        return true;
    }

    /**
     * メッセージの更新権限を判定
     *
     * 既読状態の更新のみ許可
     * 受信者（チャットルーム参加者で送信者以外）のみ既読更新可能
     */
    public function update(User $user, Message $message): bool
    {
        // リレーションを先読み込み
        $message->load(['chatRoom.application.worker', 'chatRoom.application.jobPost.company']);

        // 送信者本人は更新不可
        if ($message->sender_id === $user->id) {
            return false;
        }

        // 応募したワーカー本人（受信者）
        if ($message->chatRoom->application->worker_id === $user->id) {
            return true;
        }

        // 求人を投稿した企業ユーザー（受信者）
        if ($message->chatRoom->application->jobPost->company_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * メッセージの削除権限を判定
     *
     * メッセージの削除は許可しない
     * チャットの履歴を保持するため
     */
    public function delete(User $user, Message $message): bool
    {
        // メッセージの削除は許可しない
        return false;
    }
}
