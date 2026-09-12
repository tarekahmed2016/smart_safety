<?php

use App\Models\CompanyGoal;
use App\Models\HomepageSection;
use App\Models\Page;
use App\Support\CompanyGoalDefaults;
use Database\Seeders\HomepageSectionSeeder;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(HomepageSectionSeeder::class);
    CompanyGoal::query()->delete();
});

test('company goals table exists with expected columns', function () {
    expect(Schema::hasColumns('company_goals', [
        'text_ar',
        'text_en',
        'ordering',
        'is_active',
    ]))->toBeTrue();
});

test('homepage sections include goals after about', function () {
    $about = HomepageSection::query()->where('key', 'about')->firstOrFail();
    $goals = HomepageSection::query()->where('key', 'goals')->firstOrFail();

    expect($goals->is_visible)->toBeTrue()
        ->and($goals->show_in_navigation)->toBeTrue()
        ->and($goals->title_ar)->toBe('أهدافنا')
        ->and($goals->title_en)->toBe('Our Goals')
        ->and($goals->ordering)->toBeGreaterThan($about->ordering);
});

test('homepage only includes active company goals ordered by ordering', function () {
    CompanyGoal::factory()->create([
        'text_en' => 'Second goal',
        'is_active' => true,
        'ordering' => 2,
    ]);

    CompanyGoal::factory()->create([
        'text_en' => 'Hidden goal',
        'is_active' => false,
        'ordering' => 1,
    ]);

    CompanyGoal::factory()->create([
        'text_en' => 'First goal',
        'is_active' => true,
        'ordering' => 0,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('companyGoals', 2)
            ->where('companyGoals.0.text_en', 'First goal')
            ->where('companyGoals.1.text_en', 'Second goal')
            ->where('homepageSections', fn ($sections) => collect($sections)->contains('key', 'goals')));
});

test('hiding goals section removes it from homepage sections payload', function () {
    HomepageSection::query()->where('key', 'goals')->update(['is_visible' => false]);

    CompanyGoal::factory()->create([
        'text_en' => 'Visible only if section is on',
        'is_active' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', fn ($sections) => collect($sections)->where('key', 'goals')->isEmpty())
            ->has('companyGoals', 1));
});

test('goals page remains available for compatibility', function () {
    Page::factory()->create([
        'slug' => 'goals',
        'title_ar' => 'أهدافنا',
        'title_en' => 'Our Goals',
        'is_active' => true,
        'show_in_main_menu' => false,
    ]);

    $this->get(route('public.page.show', ['slug' => 'goals']))
        ->assertOk();
});

test('goals nav link points to the homepage goals section', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigationLinks', fn ($links) => collect($links)
                ->contains(fn ($link) => $link['key'] === 'section-goals'
                    && $link['label_en'] === 'Our Goals'
                    && $link['label_ar'] === 'أهدافنا'
                    && str_contains($link['href'], '#goals'))));
});

test('company goal defaults keep the requested arabic copy', function () {
    $arabic = collect(CompanyGoalDefaults::items())->pluck('text_ar')->all();

    expect($arabic)->toBe([
        'نساهم في زرع الثقة في المنتج العماني ومنحه المجال للمنافسة في الأسواق العالمية',
        'المساهمة في التحول بوتيرة أسرع نحو الثورة الصناعية الرابعة في السلطنة',
        'المساهمة في وصول السلطنة إلى مصاف الدول المتقدمة وتحفيز التقدم في الصناعات التحويلية',
        'بناء وتطوير المصانع بأحدث التقنيات وبأعلى معايير الجودة العالمية',
        'تطوير المنتجات البلاستيكية وتصميمها باستخدام أفضل التقنيات الصناعية',
        'تقديم الاستشارات الصناعية التي تسهم في تطوير المجال الصناعي في السلطنة',
    ]);
});
