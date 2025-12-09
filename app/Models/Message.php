<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * メッセージモデル
 *
 * チャットルーム内のメッセージを管理します。
 * 送信者（企業またはワーカー）と受信者の既読状態を保持します。
 */
class Message extends Model
{
    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chat_room_id',
        'sender_id',
        'message',
        'is_read',
    ];

    /**
     * ネイティブ型へキャストする属性
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    /**
     * チャットルームとのリレーション（多対1）
     */
    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    /**
     * 送信者（ユーザー）とのリレーション（多対1）
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
