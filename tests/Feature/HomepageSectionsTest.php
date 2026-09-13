<?php

use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Support\HomepageSectionDefaults;
use Database\Seeders\HomepageSectionSeeder;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->seed(HomepageSectionSeeder::class);
});

test('homepage sections table exists with expected columns', function () {
    expect(Schema::hasColumns('homepage_sections', [
        'key',
        'name',
        'type',
        'title_ar',
        'title_en',
        'ordering',
        'is_visible',
        'settings',
    ]))->toBeTrue();
});

test('services products and projects tables contain show_on_homepage column', function () {
    expect(Schema::hasColumn('services', 'show_on_homepage'))->toBeTrue()
        ->and(Schema::hasColumn('products', 'show_on_homepage'))->toBeTrue()
        ->and(Schema::hasColumn('projects', 'show_on_homepage'))->toBeTrue();
});

test('admin can update homepage section visibility and ordering', function () {
    $productsSection = HomepageSection::query()->where('key', 'products')->firstOrFail();
    $servicesSection = HomepageSection::query()->where('key', 'services')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('homepage-sections.update'), [
            'sections' => HomepageSection::query()
                ->orderBy('ordering')
                ->get()
                ->map(fn (HomepageSection $section) => [
                    'id' => $section->id,
                    'is_visible' => $section->key === 'products' ? false : $section->is_visible,
                    'ordering' => match ($section->key) {
                        'services' => 2,
                        'products' => 9,
                        default => $section->ordering,
                    },
                    'title_ar' => $section->title_ar,
                    'title_en' => $section->title_en,
                ])
                ->all(),
        ])
        ->assertRedirect(route('homepage-sections.index'));

    expect($productsSection->fresh()->is_visible)->toBeFalse()
        ->and($servicesSection->fresh()->ordering)->toBe(2)
        ->and(HomepageSection::query()->where('key', 'products')->value('ordering'))->toBe(9);
});

test('homepage hides products section when it is not visible', function () {
    HomepageSection::query()->where('key', 'products')->update(['is_visible' => false]);

    Product::factory()->count(2)->create([
        'is_active' => true,
        'show_on_homepage' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', fn ($sections) => collect($sections)->where('key', 'products')->isEmpty())
            ->has('products', 2));
});

test('homepage hides services section when it is not visible', function () {
    HomepageSection::query()->where('key', 'services')->update([
        'is_visible' => true,
        'ordering' => 2,
    ]);

    Service::factory()->count(2)->create([
        'is_active' => true,
        'show_on_homepage' => true,
    ]);

    HomepageSection::query()->where('key', 'services')->update(['is_visible' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', fn ($sections) => collect($sections)->where('key', 'services')->isEmpty())
            ->has('services', 2));
});

test('homepage only includes products flagged for homepage display', function () {
    Product::factory()->create([
        'name_ar' => 'منتج ظاهر',
        'name_en' => 'Visible Product',
        'is_active' => true,
        'show_on_homepage' => true,
        'ordering' => 1,
    ]);

    Product::factory()->create([
        'name_ar' => 'منتج مخفي',
        'name_en' => 'Hidden Product',
        'is_active' => true,
        'show_on_homepage' => false,
        'ordering' => 2,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products', 1)
            ->where('products.0.name_en', 'Visible Product'));
});

test('homepage orders services and products by ordering field', function () {
    Service::factory()->create([
        'name_en' => 'Second Service',
        'is_active' => true,
        'show_on_homepage' => true,
        'ordering' => 2,
    ]);

    Service::factory()->create([
        'name_en' => 'First Service',
        'is_active' => true,
        'show_on_homepage' => true,
        'ordering' => 1,
    ]);

    Product::factory()->create([
        'name_en' => 'Second Product',
        'slug' => 'second-product',
        'is_active' => true,
        'show_on_homepage' => true,
        'ordering' => 2,
    ]);

    Product::factory()->create([
        'name_en' => 'First Product',
        'slug' => 'first-product',
        'is_active' => true,
        'show_on_homepage' => true,
        'ordering' => 1,
    ]);

    HomepageSection::query()->where('key', 'services')->update(['is_visible' => true]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('services.0.name_en', 'First Service')
            ->where('products.0.name_en', 'First Product'));
});

test('homepage sections are returned in configured order', function () {
    HomepageSection::query()->where('key', 'gallery')->update(['ordering' => 2, 'is_visible' => true]);
    HomepageSection::query()->where('key', 'features')->update(['ordering' => 8, 'is_visible' => true]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections.0.key', 'hero')
            ->where('homepageSections.1.key', 'gallery')
            ->where('homepageSections.2.key', 'about'));
});

test('homepage visible sections follow the main navigation order', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', function ($sections) {
                $keys = collect($sections)->pluck('key')->values();

                $expected = [
                    'hero',
                    'features',
                    'about',
                    'why_us',
                    'services',
                    'products',
                    'vision_mission',
                    'goals',
                    'team_members',
                    'clients_partners',
                    'gallery',
                    'custom_manufacturing',
                    'industries',
                    'contact_cta',
                    'contact',
                ];

                return $keys->all() === $expected;
            }));
});

test('hero and features do not use standalone section titles', function () {
    expect(HomepageSectionDefaults::usesStandaloneSectionTitle('hero'))->toBeFalse()
        ->and(HomepageSectionDefaults::usesStandaloneSectionTitle('features'))->toBeFalse()
        ->and(HomepageSectionDefaults::usesStandaloneSectionTitle('about'))->toBeTrue()
        ->and(HomepageSectionDefaults::usesStandaloneSectionTitle('products'))->toBeTrue();
});

test('homepage sections page hides section title fields on hero and features cards', function () {
    $source = file_get_contents(resource_path('js/Pages/HomepageSections/HomepageSectionsPage.vue'));

    expect($source)->toContain("section_key === 'hero'")
        ->and($source)->toContain("section_key === 'features'")
        ->and($source)->toContain('showsSectionTitleFields')
        ->and($source)->toContain('titleArLabel')
        ->and($source)->toContain('titleEnLabel')
        ->and($source)->toContain('visibleLabel')
        ->and($source)->toContain('navLabelAr')
        ->and($source)->toContain('navOrderLabel');
});

test('saving homepage sections keeps existing hero and features titles in the database', function () {
    $hero = HomepageSection::query()->where('key', 'hero')->firstOrFail();
    $features = HomepageSection::query()->where('key', 'features')->firstOrFail();

    $hero->update(['title_ar' => 'عنوان هيرو قديم', 'title_en' => 'Legacy hero title']);
    $features->update(['title_ar' => 'عنوان مميزات قديم', 'title_en' => 'Legacy features title']);

    $this->actingAs($this->admin)
        ->put(route('homepage-sections.update'), [
            'sections' => HomepageSection::query()
                ->orderBy('ordering')
                ->get()
                ->map(fn (HomepageSection $section) => [
                    'id' => $section->id,
                    'is_visible' => $section->is_visible,
                    'ordering' => $section->ordering,
                    'title_ar' => $section->fresh()->title_ar,
                    'title_en' => $section->fresh()->title_en,
                    'show_in_navigation' => $section->show_in_navigation,
                    'nav_label_ar' => $section->nav_label_ar,
                    'nav_label_en' => $section->nav_label_en,
                    'nav_order' => $section->nav_order,
                    'anchor_id' => $section->anchor_id,
                ])
                ->all(),
        ])
        ->assertRedirect(route('homepage-sections.index'));

    expect($hero->fresh()->title_ar)->toBe('عنوان هيرو قديم')
        ->and($hero->fresh()->title_en)->toBe('Legacy hero title')
        ->and($features->fresh()->title_ar)->toBe('عنوان مميزات قديم')
        ->and($features->fresh()->title_en)->toBe('Legacy features title');
});
