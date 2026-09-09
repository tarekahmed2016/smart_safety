<?php

namespace App\Services;

use App\Models\HomepageSection;
use App\Support\HomepageSectionDefaults;
use App\Support\HomepageSectionNavigation;
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
     * @param  list<array{id: int, is_visible: bool, ordering: int, title_ar?: string|null, title_en?: string|null, show_in_navigation?: bool, nav_label_ar?: string|null, nav_label_en?: string|null, nav_order?: int, anchor_id?: string|null}>  $sections
     */
    public function syncSections(array $sections): void
    {
        DB::transaction(function () use ($sections) {
            foreach ($sections as $sectionData) {
                $section = HomepageSection::query()->findOrFail($sectionData['id']);

                $payload = [
                    'is_visible' => (bool) $sectionData['is_visible'],
                    'ordering' => (int) $sectionData['ordering'],
                    'title_ar' => $sectionData['title_ar'] ?? null,
                    'title_en' => $sectionData['title_en'] ?? null,
                ];

                if (HomepageSectionNavigation::isNavigable($section->key)) {
                    $payload = array_merge($payload, [
                        'show_in_navigation' => (bool) ($sectionData['show_in_navigation'] ?? false),
                        'nav_label_ar' => $sectionData['nav_label_ar'] ?? null,
                        'nav_label_en' => $sectionData['nav_label_en'] ?? null,
                        'nav_order' => (int) ($sectionData['nav_order'] ?? $section->nav_order),
                        'anchor_id' => $sectionData['anchor_id'] ?? null,
                    ]);
                }

                $section->update($payload);
            }
        });
    }

    /**
     * @param  array{title_ar?: string|null, title_en?: string|null, max_items?: int|null}  $data
     */
    public function updateSectionContent(string $key, array $data): HomepageSection
    {
        $this->ensureDefaultsExist();

        $section = HomepageSection::query()->where('key', $key)->firstOrFail();
        $payload = [];

        if (array_key_exists('title_ar', $data)) {
            $payload['title_ar'] = $data['title_ar'];
        }

        if (array_key_exists('title_en', $data)) {
            $payload['title_en'] = $data['title_en'];
        }

        if (array_key_exists('max_items', $data)) {
            $payload['settings'] = array_merge($section->settings ?? [], [
                'max_items' => (int) $data['max_items'],
            ]);
        }

        if ($payload !== []) {
            $section->update($payload);
        }

        return $section->fresh();
    }

    public function ensureDefaultsExist(): void
    {
        if (HomepageSection::query()->exists()) {
            return;
        }

        foreach (HomepageSectionDefaults::sections() as $section) {
            $navDefaults = HomepageSectionNavigation::defaultsByKey()[$section['key']] ?? [
                'show_in_navigation' => false,
                'nav_label_ar' => null,
                'nav_label_en' => null,
                'nav_order' => 0,
                'anchor_id' => null,
            ];

            HomepageSection::query()->create([
                'key' => $section['key'],
                'name' => $section['name'],
                'type' => $section['type'],
                'ordering' => $section['ordering'],
                'is_visible' => $section['is_visible'],
                'title_ar' => $section['title_ar'],
                'title_en' => $section['title_en'],
                'settings' => $section['settings'],
                ...$navDefaults,
            ]);
        }
    }
}
