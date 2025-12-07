<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'type_id',
        'name',
        'description',
        'sort_order',
    ];
}
