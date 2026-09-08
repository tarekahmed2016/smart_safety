<?php

namespace App\Services;

use App\Models\HomepageSection;
use App\Models\Page;
use App\Support\HomepageSectionNavigation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NavigationService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getPublicLinks(bool $isHomePage = true): Collection
    {
        $homePrefix = $isHomePage ? '' : route('home');

        $sectionLinks = HomepageSection::query()
            ->whereIn('key', HomepageSectionNavigation::navigableKeys())
            ->where('is_visible', true)
            ->where('show_in_navigation', true)
            ->orderBy('nav_order')
            ->get()
            ->map(fn (HomepageSection $section) => $this->mapSectionForPublic($section, $homePrefix, $isHomePage));

        $pageLinks = Page::query()
            ->where('is_active', true)
            ->where('show_in_main_menu', true)
            ->orderBy('menu_order')
            ->get()
            ->map(fn (Page $page) => $this->mapPageForPublic($page));

        return $sectionLinks
            ->concat($pageLinks)
            ->sortBy('order')
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getAdminItems(): Collection
    {
        $sections = HomepageSection::query()
            ->whereIn('key', HomepageSectionNavigation::navigableKeys())
            ->orderBy('nav_order')
            ->get()
            ->map(fn (HomepageSection $section) => $this->mapSectionForAdmin($section));

        $pages = Page::query()
            ->orderBy('menu_order')
            ->get()
            ->map(fn (Page $page) => $this->mapPageForAdmin($page));

        return $sections
            ->concat($pages)
            ->sortBy('nav_order')
            ->values();
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function syncNavigation(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                if (($item['source'] ?? '') === 'section') {
                    HomepageSection::query()
                        ->whereIn('key', HomepageSectionNavigation::navigableKeys())
                        ->findOrFail($item['id'])
                        ->update([
                            'show_in_navigation' => (bool) ($item['show_in_navigation'] ?? false),
                            'nav_label_ar' => $item['nav_label_ar'] ?? null,
                            'nav_label_en' => $item['nav_label_en'] ?? null,
                            'nav_order' => (int) ($item['nav_order'] ?? 0),
                            'anchor_id' => $item['anchor_id'] ?? null,
                        ]);

                    continue;
                }

                if (($item['source'] ?? '') === 'page') {
                    Page::query()->findOrFail($item['id'])->update([
                        'show_in_main_menu' => (bool) ($item['show_in_navigation'] ?? false),
                        'menu_title_ar' => $item['nav_label_ar'] ?? null,
                        'menu_title_en' => $item['nav_label_en'] ?? null,
                        'menu_order' => (int) ($item['nav_order'] ?? 0),
                        'open_in_new_tab' => (bool) ($item['open_in_new_tab'] ?? false),
                    ]);
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSectionForPublic(HomepageSection $section, string $homePrefix, bool $isHomePage): array
    {
        $fallback = HomepageSectionNavigation::fallbackForKey($section->key);
        $anchor = trim((string) ($section->anchor_id ?: $fallback['anchor_id']));

        $href = $section->key === 'products' && ! $isHomePage
            ? route('public.products.index')
            : "{$homePrefix}#{$anchor}";

        return [
            'key' => "section-{$section->key}",
            'source' => 'section',
            'order' => (int) $section->nav_order,
            'href' => $href,
            'label_ar' => trim((string) ($section->nav_label_ar ?: $fallback['nav_label_ar'])),
            'label_en' => trim((string) ($section->nav_label_en ?: $fallback['nav_label_en'])),
            'open_in_new_tab' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPageForPublic(Page $page): array
    {
        return [
            'key' => "page-{$page->slug}",
            'source' => 'page',
            'order' => (int) $page->menu_order,
            'href' => route('public.page.show', ['slug' => $page->slug]),
            'label_ar' => trim((string) ($page->menu_title_ar ?: $page->title_ar)),
            'label_en' => trim((string) ($page->menu_title_en ?: $page->title_en)),
            'open_in_new_tab' => (bool) $page->open_in_new_tab,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSectionForAdmin(HomepageSection $section): array
    {
        $fallback = HomepageSectionNavigation::fallbackForKey($section->key);

        return [
            'id' => $section->id,
            'source' => 'section',
            'key' => $section->key,
            'name' => $section->name,
            'is_visible' => (bool) $section->is_visible,
            'show_in_navigation' => (bool) $section->show_in_navigation,
            'nav_label_ar' => $section->nav_label_ar ?? '',
            'nav_label_en' => $section->nav_label_en ?? '',
            'nav_order' => (int) $section->nav_order,
            'anchor_id' => $section->anchor_id ?: $fallback['anchor_id'],
            'default_label_ar' => $fallback['nav_label_ar'],
            'default_label_en' => $fallback['nav_label_en'],
            'open_in_new_tab' => false,
            'slug' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPageForAdmin(Page $page): array
    {
        return [
            'id' => $page->id,
            'source' => 'page',
            'key' => "page-{$page->slug}",
            'name' => $page->title_en ?: $page->title_ar,
            'is_visible' => (bool) $page->is_active,
            'show_in_navigation' => (bool) $page->show_in_main_menu,
            'nav_label_ar' => $page->menu_title_ar ?? '',
            'nav_label_en' => $page->menu_title_en ?? '',
            'nav_order' => (int) $page->menu_order,
            'anchor_id' => null,
            'default_label_ar' => $page->title_ar,
            'default_label_en' => $page->title_en,
            'open_in_new_tab' => (bool) $page->open_in_new_tab,
            'slug' => $page->slug,
        ];
    }
}
