<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobApplication extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'job_id',
        'worker_id',
        'motive',
        'status',
        'applied_at',
        'judged_at',
        'declined_at',
    ];

    /**
     * キャストする属性
     */
    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
            'judged_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    /**
     * 求人情報との多対1リレーション
     */
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_id');
    }

    /**
     * ワーカーとの多対1リレーション
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /**
     * チャットルームとの1対1リレーション
     */
    public function chatRoom(): HasOne
    {
        return $this->hasOne(ChatRoom::class, 'application_id');
    }
}
