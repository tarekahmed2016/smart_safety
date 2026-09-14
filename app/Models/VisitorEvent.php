<?php

namespace App\Models;

use App\Enums\VisitorEventType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'visitor_hash',
    'session_hash',
    'visited_on',
    'last_seen_at',
    'path',
    'event_type',
])]
class VisitorEvent extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'event_type' => VisitorEventType::class,
        ];
    }
}
