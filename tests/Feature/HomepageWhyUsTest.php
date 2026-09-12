<?php

use App\Enums\HomepagePromoType;
use App\Models\HomepagePromoBlock;
use App\Models\HomepageSection;
use App\Support\HomepageWhyUsContentDefaults;
use Database\Seeders\HomepageSectionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(HomepageSectionSeeder::class);
    HomepagePromoBlock::query()->where('type', HomepagePromoType::WhyUsHighlight)->delete();
});

test('homepage sections include why us after about', function () {
    $about = HomepageSection::query()->where('key', 'about')->firstOrFail();
    $whyUs = HomepageSection::query()->where('key', 'why_us')->firstOrFail();
    $services = HomepageSection::query()->where('key', 'services')->firstOrFail();

    expect($whyUs->is_visible)->toBeTrue()
        ->and($whyUs->show_in_navigation)->toBeTrue()
        ->and($whyUs->title_ar)->toBe('لماذا نحن')
        ->and($whyUs->title_en)->toBe('Why Us')
        ->and($whyUs->anchor_id)->toBe('why-us')
        ->and($whyUs->nav_label_ar)->toBe('لماذا نحن')
        ->and($whyUs->ordering)->toBeGreaterThan($about->ordering)
        ->and($services->ordering)->toBeGreaterThan($whyUs->ordering);
});

test('homepage includes original why us cards from cms', function () {
    foreach (HomepageWhyUsContentDefaults::items() as $item) {
        HomepagePromoBlock::factory()->whyUsHighlight()->create([
            'title_ar' => $item['title_ar'],
            'title_en' => $item['title_en'],
            'description_ar' => $item['description_ar'],
            'description_en' => $item['description_en'],
            'icon' => $item['icon'],
            'ordering' => $item['ordering'],
            'is_active' => true,
        ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', fn ($sections) => collect($sections)->contains(
                fn ($section) => $section['key'] === 'why_us'
                    && $section['title_ar'] === 'لماذا نحن'
                    && ($section['settings']['headline_ar'] ?? null) === 'ما يميز الصناعة الإبداعية'
            ))
            ->has('whyUsHighlights', 3)
            ->where('whyUsHighlights.0.title_ar', 'كوادر عمانية متخصصة')
            ->where('whyUsHighlights.0.icon', 'flag')
            ->where('whyUsHighlights.1.title_ar', 'تنفيذ سريع ودقيق')
            ->where('whyUsHighlights.2.title_ar', 'دعم التنمية المستدامة'));
});

test('hiding why us section removes it from homepage sections payload', function () {
    HomepageSection::query()->where('key', 'why_us')->update(['is_visible' => false]);

    HomepagePromoBlock::factory()->whyUsHighlight()->create([
        'title_en' => 'Visible only if section is on',
        'is_active' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', fn ($sections) => collect($sections)->where('key', 'why_us')->isEmpty())
            ->has('whyUsHighlights', 1));
});

test('why us nav link appears after about', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigationLinks', function ($links) {
                $items = collect($links)->values();
                $about = $items->firstWhere('key', 'section-about');
                $whyUs = $items->firstWhere('key', 'section-why_us');

                if (! $about || ! $whyUs) {
                    return false;
                }

                $aboutIndex = $items->search(fn ($link) => $link['key'] === 'section-about');
                $whyUsIndex = $items->search(fn ($link) => $link['key'] === 'section-why_us');

                return str_contains($whyUs['href'], '#why-us')
                    && $whyUs['label_ar'] === 'لماذا نحن'
                    && $whyUs['label_en'] === 'Why Us'
                    && $aboutIndex !== false
                    && $whyUsIndex !== false
                    && $aboutIndex < $whyUsIndex;
            }));
});

test('why us highlight is a homepage promo type', function () {
    expect(HomepagePromoType::values())->toContain('why_us_highlight')
        ->and(HomepagePromoType::WhyUsHighlight->labelEn())->toBe('Why Us Highlight');
});
