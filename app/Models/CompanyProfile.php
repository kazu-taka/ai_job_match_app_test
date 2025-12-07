<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
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
        'location_id',
        'address',
        'representative',
        'phone_number',
    ];

    /**
     * ユーザーとの1対1リレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 所在地との多対1リレーション
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
