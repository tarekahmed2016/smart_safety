<?php

use App\Models\HomepageSection;
use App\Models\Service;
use App\Support\HomepageServicesContentDefaults;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $sectionCopy = HomepageServicesContentDefaults::section();

        $section = HomepageSection::query()->where('key', 'services')->first();

        if ($section) {
            $section->update([
                'title_ar' => $sectionCopy['title_ar'],
                'title_en' => $sectionCopy['title_en'],
                'settings' => array_merge($section->settings ?? [], [
                    'highlight_ar' => $sectionCopy['highlight_ar'],
                    'highlight_en' => $sectionCopy['highlight_en'],
                    'subtitle_ar' => $sectionCopy['subtitle_ar'],
                    'subtitle_en' => $sectionCopy['subtitle_en'],
                ]),
            ]);
        }

        $keptIds = [];
        $hasExistingServices = Service::query()->exists();

        foreach (HomepageServicesContentDefaults::items() as $service) {
            $record = Service::query()
                ->where(function ($query) use ($service) {
                    $query->whereIn('name_en', $service['match_en'])
                        ->orWhereIn('name_ar', $service['match_ar']);
                })
                ->first();

            $payload = [
                'name_ar' => $service['name_ar'],
                'name_en' => $service['name_en'],
                'description_ar' => $service['description_ar'],
                'description_en' => $service['description_en'],
                'ordering' => $service['ordering'],
                'is_active' => true,
                'show_on_homepage' => true,
            ];

            if ($record) {
                $record->update($payload);
            } elseif ($hasExistingServices) {
                $record = Service::create($payload);
            } else {
                continue;
            }

            $keptIds[] = $record->id;
        }

        if ($keptIds !== []) {
            Service::query()
                ->whereNotIn('id', $keptIds)
                ->update(['show_on_homepage' => false]);
        }
    }

    public function down(): void
    {
        HomepageSection::query()
            ->where('key', 'services')
            ->update([
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ]);
    }
};
