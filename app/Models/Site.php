<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['name', 'key', 'status'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];
}
