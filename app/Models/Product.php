<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable([
    'name_ar',
    'name_en',
    'slug',
    'description_ar',
    'description_en',
    'details_ar',
    'details_en',
    'sizes',
    'specifications_ar',
    'specifications_en',
    'ordering',
    'is_active',
    'show_on_homepage',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $appends = ['is_active_formatted'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
            'ordering' => 'integer',
            'sizes' => 'array',
            'specifications_ar' => 'array',
            'specifications_en' => 'array',
        ];
    }

    /**
     * @return MorphOne<Attachment, $this>
     */
    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }

    /**
     * @return MorphMany<ActivityLog, $this>
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    /**
     * @return Attribute<array{value: bool, label: string, name: string}, never>
     */
    protected function isActiveFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'value' => (bool) $this->is_active,
                'label' => $this->is_active ? 'نشط' : 'غير نشط',
                'name' => $this->is_active ? 'Active' : 'Inactive',
            ]
        );
    }
}
