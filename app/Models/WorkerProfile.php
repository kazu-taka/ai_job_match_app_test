<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerProfile extends Model
{
    use HasFactory;

    /**
     * 主キーの設定
     */
    protected $primaryKey = 'user_id';

    /**
     * 主キーの自動増分を無効化
     */
    public $incrementing = false;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'gender',
        'birthdate',
        'skills',
        'experiences',
        'desired_jobs',
        'desired_location_id',
    ];

    /**
     * キャストする属性
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    /**
     * ユーザーとの1対1リレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 希望勤務地との多対1リレーション
     */
    public function desiredLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'desired_location_id');
    }
}
