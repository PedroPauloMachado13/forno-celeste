<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Holiday extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'date',
        'name',
        'was_open',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'date' => 'immutable_date',
            'was_open' => 'boolean',
        ];
    }
}
