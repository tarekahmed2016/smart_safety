<?php

namespace App\Models;

use Database\Factories\CompanyGoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'text_ar',
    'text_en',
    'ordering',
    'is_active',
])]
class CompanyGoal extends Model
{
    /** @use HasFactory<CompanyGoalFactory> */
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
            'ordering' => 'integer',
        ];
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
