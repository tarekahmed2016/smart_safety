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
    'show_in_navigation',
    'nav_label_ar',
    'nav_label_en',
    'nav_order',
    'anchor_id',
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
            'show_in_navigation' => 'boolean',
            'nav_order' => 'integer',
            'settings' => 'array',
        ];
    }
}
