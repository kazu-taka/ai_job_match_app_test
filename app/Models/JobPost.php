<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPost extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'employment_type_id',
        'work_style_id',
        'industry_id',
        'location_id',
        'working_hours',
        'salary',
        'number_of_positions',
        'posted_at',
        'expires_at',
    ];

    /**
     * キャストする属性
     */
    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * 企業ユーザーとの多対1リレーション
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    /**
     * 勤務地との多対1リレーション
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * 雇用形態コードを取得（メモ化）
     */
    public function employmentType(): ?string
    {
        static $cache = [];

        if (! isset($cache[$this->employment_type_id])) {
            $cache[$this->employment_type_id] = Code::query()
                ->where('type', 1)
                ->where('type_id', $this->employment_type_id)
                ->value('name');
        }

        return $cache[$this->employment_type_id];
    }

    /**
     * 勤務形態コードを取得（メモ化）
     */
    public function workStyle(): ?string
    {
        static $cache = [];

        if (! isset($cache[$this->work_style_id])) {
            $cache[$this->work_style_id] = Code::query()
                ->where('type', 2)
                ->where('type_id', $this->work_style_id)
                ->value('name');
        }

        return $cache[$this->work_style_id];
    }

    /**
     * 業種コードを取得（メモ化）
     */
    public function industry(): ?string
    {
        static $cache = [];

        if (! isset($cache[$this->industry_id])) {
            $cache[$this->industry_id] = Code::query()
                ->where('type', 3)
                ->where('type_id', $this->industry_id)
                ->value('name');
        }

        return $cache[$this->industry_id];
    }
}
