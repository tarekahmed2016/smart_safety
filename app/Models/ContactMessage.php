<?php

namespace App\Models;

use App\Enums\ContactMessages\RequestStatus;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'name',
    'email',
    'phone',
    'subject',
    'message',
    'is_read',
    'read_at',
    'request_status',
])]
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $appends = ['is_read_formatted', 'request_status_formatted'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'request_status' => RequestStatus::class,
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
    protected function isReadFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'value' => (bool) $this->is_read,
                'label' => $this->is_read ? 'مقروء' : 'غير مقروء',
                'name' => $this->is_read ? 'Read' : 'Unread',
            ]
        );
    }

    /**
     * @return Attribute<array{value: string, label: string, label_en: string, name: string}|null, never>
     */
    protected function requestStatusFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                $status = $this->request_status instanceof RequestStatus
                    ? $this->request_status
                    : RequestStatus::tryFrom((string) $this->request_status) ?? RequestStatus::New;

                return [
                    'value' => $status->value,
                    'label' => $status->labelAr(),
                    'label_en' => $status->labelEn(),
                    'name' => $status->name,
                ];
            }
        );
    }
}
