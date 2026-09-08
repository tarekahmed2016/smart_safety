<?php

namespace App\Models;

use App\Enums\HomepageSectionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'name',
    'type',
    'title_ar',
    'title_en',
    'ordering',
    'is_visible',
    'settings',
])]
class HomepageSection extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => HomepageSectionType::class,
            'ordering' => 'integer',
            'is_visible' => 'boolean',
            'settings' => 'array',
        ];
    }
}
