<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * チャットルームモデル
 *
 * 1つの応募に対して1つのチャットルームを保持します。
 * 企業とワーカー間のメッセージのやり取りを管理します。
 */
class ChatRoom extends Model
{
    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_id',
    ];

    /**
     * 応募情報とのリレーション（1対1）
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    /**
     * メッセージとのリレーション（1対多）
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
