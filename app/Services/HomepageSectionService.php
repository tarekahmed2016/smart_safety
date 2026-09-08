<?php

namespace App\Services;

use App\Models\HomepageSection;
use App\Support\HomepageSectionDefaults;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HomepageSectionService
{
    /**
     * @return Collection<int, HomepageSection>
     */
    public function getAllSections(): Collection
    {
        $this->ensureDefaultsExist();

        return HomepageSection::query()
            ->orderBy('ordering')
            ->get();
    }

    /**
     * @return Collection<int, array{key: string, type: string, title_ar: string, title_en: string, settings: array<string, mixed>}>
     */
    public function getVisibleSectionsForPublic(): Collection
    {
        return $this->getAllSections()
            ->where('is_visible', true)
            ->values()
            ->map(fn (HomepageSection $section) => $this->mapForPublic($section));
    }

    /**
     * @return array{key: string, type: string, title_ar: string, title_en: string, settings: array<string, mixed>}
     */
    public function mapForPublic(HomepageSection $section): array
    {
        return [
            'key' => $section->key,
            'type' => $section->type->value,
            'title_ar' => $section->title_ar ?? '',
            'title_en' => $section->title_en ?? '',
            'settings' => $section->settings ?? [],
        ];
    }

    /**
     * @param  list<array{id: int, is_visible: bool, ordering: int, title_ar?: string|null, title_en?: string|null}>  $sections
     */
    public function syncSections(array $sections): void
    {
        DB::transaction(function () use ($sections) {
            foreach ($sections as $sectionData) {
                $section = HomepageSection::query()->findOrFail($sectionData['id']);

                $section->update([
                    'is_visible' => (bool) $sectionData['is_visible'],
                    'ordering' => (int) $sectionData['ordering'],
                    'title_ar' => $sectionData['title_ar'] ?? null,
                    'title_en' => $sectionData['title_en'] ?? null,
                ]);
            }
        });
    }

    public function ensureDefaultsExist(): void
    {
        if (HomepageSection::query()->exists()) {
            return;
        }

        foreach (HomepageSectionDefaults::sections() as $section) {
            HomepageSection::query()->create([
                'key' => $section['key'],
                'name' => $section['name'],
                'type' => $section['type'],
                'ordering' => $section['ordering'],
                'is_visible' => $section['is_visible'],
                'title_ar' => $section['title_ar'],
                'title_en' => $section['title_en'],
                'settings' => $section['settings'],
            ]);
        }
    }
}
