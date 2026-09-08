<?php

use App\Models\HomepageSection;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
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

test('homepage sections table contains navigation columns', function () {
    expect(Schema::hasColumns('homepage_sections', [
        'show_in_navigation',
        'nav_label_ar',
        'nav_label_en',
        'nav_order',
        'anchor_id',
    ]))->toBeTrue();
});

test('pages table contains open in new tab column', function () {
    expect(Schema::hasColumn('pages', 'open_in_new_tab'))->toBeTrue();
});

test('admin can open navigation settings page', function () {
    $this->actingAs($this->admin)
        ->get(route('navigation.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Navigation/NavigationPage', false)
            ->has('navigationItems'));
});

test('admin can open homepage sections settings page', function () {
    $this->actingAs($this->admin)
        ->get(route('homepage-sections.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('HomepageSections/HomepageSectionsPage', false)
            ->has('homepageSections'));
});

test('admin can navigate between navigation and homepage sections pages', function () {
    $this->actingAs($this->admin)
        ->get(route('navigation.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Navigation/NavigationPage', false));

    $this->get(route('homepage-sections.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('HomepageSections/HomepageSectionsPage', false));

    $this->get(route('navigation.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Navigation/NavigationPage', false));
});

test('dashboard sidebar places navigation before homepage sections', function () {
    $layout = file_get_contents(resource_path('js/Layouts/DashboardLayout.vue'));

    $companyPos = strpos($layout, "route('company-info.index')");
    $navigationPos = strpos($layout, "route('navigation.index')");
    $sectionsPos = strpos($layout, "route('homepage-sections.index')");
    $promosPos = strpos($layout, "route('homepage-promos.index')");

    expect($companyPos)->not->toBeFalse()
        ->and($navigationPos)->not->toBeFalse()
        ->and($sectionsPos)->not->toBeFalse()
        ->and($promosPos)->not->toBeFalse()
        ->and($companyPos)->toBeLessThan($navigationPos)
        ->and($navigationPos)->toBeLessThan($sectionsPos)
        ->and($sectionsPos)->toBeLessThan($promosPos);
});

test('navigation and homepage sections pages use dashboard layout', function () {
    $navigationPage = file_get_contents(resource_path('js/Pages/Navigation/NavigationPage.vue'));
    $homepageSectionsPage = file_get_contents(resource_path('js/Pages/HomepageSections/HomepageSectionsPage.vue'));

    expect($navigationPage)->toContain("defineOptions({ layout: DashboardLayout })")
        ->and($homepageSectionsPage)->toContain("defineOptions({ layout: DashboardLayout })");
});

test('services nav link appears when homepage section is visible and enabled in navigation', function () {
    HomepageSection::query()->where('key', 'services')->update([
        'is_visible' => true,
        'show_in_navigation' => true,
        'nav_label_en' => 'Our Services',
        'nav_order' => 25,
        'anchor_id' => 'services',
    ]);

    Service::factory()->create([
        'is_active' => true,
        'show_on_homepage' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('navigationLinks')
            ->where('navigationLinks', fn ($links) => collect($links)
                ->contains(fn ($link) => $link['key'] === 'section-services'
                    && $link['label_en'] === 'Our Services'
                    && str_contains($link['href'], '#services'))));
});

test('services nav link is hidden when homepage section is not visible', function () {
    HomepageSection::query()->where('key', 'services')->update([
        'is_visible' => false,
        'show_in_navigation' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigationLinks', fn ($links) => collect($links)
                ->where('key', 'section-services')
                ->isEmpty()));
});

test('services nav link is hidden when show in navigation is disabled', function () {
    HomepageSection::query()->where('key', 'services')->update([
        'is_visible' => true,
        'show_in_navigation' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigationLinks', fn ($links) => collect($links)
                ->where('key', 'section-services')
                ->isEmpty()));
});

test('admin can update navigation order and labels', function () {
    $servicesSection = HomepageSection::query()->where('key', 'services')->firstOrFail();
    $aboutSection = HomepageSection::query()->where('key', 'about')->firstOrFail();

    $page = Page::factory()->create([
        'title_ar' => 'صفحة',
        'title_en' => 'Extra Page',
        'menu_title_ar' => 'صفحة إضافية',
        'menu_title_en' => 'Extra Page',
        'slug' => 'extra-page',
        'show_in_main_menu' => true,
        'menu_order' => 80,
        'open_in_new_tab' => true,
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->put(route('navigation.update'), [
            'items' => [
                [
                    'id' => $servicesSection->id,
                    'source' => 'section',
                    'show_in_navigation' => true,
                    'nav_label_ar' => 'خدماتنا',
                    'nav_label_en' => 'Services Menu',
                    'nav_order' => 15,
                    'anchor_id' => 'services',
                ],
                [
                    'id' => $aboutSection->id,
                    'source' => 'section',
                    'show_in_navigation' => true,
                    'nav_label_ar' => 'من نحن',
                    'nav_label_en' => 'About',
                    'nav_order' => 20,
                    'anchor_id' => 'about',
                ],
                [
                    'id' => $page->id,
                    'source' => 'page',
                    'show_in_navigation' => true,
                    'nav_label_ar' => 'صفحة خاصة',
                    'nav_label_en' => 'Special Page',
                    'nav_order' => 90,
                    'open_in_new_tab' => true,
                ],
            ],
        ])
        ->assertRedirect(route('navigation.index'));

    expect($servicesSection->fresh())
        ->show_in_navigation->toBeTrue()
        ->nav_label_en->toBe('Services Menu')
        ->nav_order->toBe(15)
        ->and($page->fresh())
        ->menu_title_en->toBe('Special Page')
        ->menu_order->toBe(90)
        ->open_in_new_tab->toBeTrue();
});

test('navigation links are sorted by nav order on homepage', function () {
    HomepageSection::query()->where('key', 'services')->update([
        'is_visible' => true,
        'show_in_navigation' => true,
        'nav_order' => 5,
    ]);

    HomepageSection::query()->where('key', 'about')->update([
        'is_visible' => true,
        'show_in_navigation' => true,
        'nav_order' => 50,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigationLinks.0.key', 'section-services')
            ->where('navigationLinks.1.key', 'section-hero'));
});
