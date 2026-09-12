<?php

use App\Models\CompanyInfo;
use App\Models\HomepageSection;
use Database\Seeders\HomepageSectionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(HomepageSectionSeeder::class);
});

test('homepage sections place vision and mission immediately before goals', function () {
    $about = HomepageSection::query()->where('key', 'about')->firstOrFail();
    $visionMission = HomepageSection::query()->where('key', 'vision_mission')->firstOrFail();
    $goals = HomepageSection::query()->where('key', 'goals')->firstOrFail();

    expect($visionMission->is_visible)->toBeTrue()
        ->and($visionMission->show_in_navigation)->toBeTrue()
        ->and($visionMission->title_ar)->toBe('رؤيتنا ورسالتنا')
        ->and($visionMission->title_en)->toBe('Vision & Mission')
        ->and($visionMission->anchor_id)->toBe('vision-mission')
        ->and($visionMission->ordering)->toBeGreaterThan($about->ordering)
        ->and($goals->ordering)->toBeGreaterThan($visionMission->ordering);
});

test('homepage includes the vision and mission section before goals', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', function ($sections) {
                $keys = collect($sections)->pluck('key')->values();
                $visionIndex = $keys->search('vision_mission');
                $goalsIndex = $keys->search('goals');

                return $visionIndex !== false
                    && $goalsIndex !== false
                    && $visionIndex === $goalsIndex - 1;
            }));
});

test('hiding vision and mission section removes it from homepage sections payload', function () {
    HomepageSection::query()->where('key', 'vision_mission')->update(['is_visible' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', fn ($sections) => collect($sections)->where('key', 'vision_mission')->isEmpty())
            ->has('companyInfo.vision_ar')
            ->has('companyInfo.mission_ar'));
});

test('vision and mission nav link appears before goals when both are enabled', function () {
    HomepageSection::query()->where('key', 'goals')->update([
        'is_visible' => true,
        'show_in_navigation' => true,
        'nav_label_en' => 'Our Goals',
        'nav_order' => 61,
        'anchor_id' => 'goals',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigationLinks', function ($links) {
                $items = collect($links)->values();
                $vision = $items->firstWhere('key', 'section-vision_mission');
                $goals = $items->firstWhere('key', 'section-goals');

                if (! $vision || ! $goals) {
                    return false;
                }

                $visionIndex = $items->search(fn ($link) => $link['key'] === 'section-vision_mission');
                $goalsIndex = $items->search(fn ($link) => $link['key'] === 'section-goals');

                return str_contains($vision['href'], '#vision-mission')
                    && $vision['label_ar'] === 'رؤيتنا'
                    && $visionIndex !== false
                    && $goalsIndex !== false
                    && $visionIndex < $goalsIndex;
            }));
});

test('vision and mission nav link can be hidden from navigation settings', function () {
    HomepageSection::query()->where('key', 'vision_mission')->update([
        'show_in_navigation' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigationLinks', fn ($links) => collect($links)
                ->contains(fn ($link) => ($link['key'] ?? '') === 'section-vision_mission') === false));
});

test('homepage still receives company vision and mission fields for the section', function () {
    CompanyInfo::query()->create([
        'vision_ar' => '<p>تكوين مجموعة شركات صناعية متكاملة ناجحة اقتصاديًا.</p>',
        'mission_ar' => '<p>تقديم حلول صناعية احترافية.</p>',
        'vision_en' => '<p>Existing English vision.</p>',
        'mission_en' => '<p>Existing English mission.</p>',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('companyInfo.vision_ar', '<p>تكوين مجموعة شركات صناعية متكاملة ناجحة اقتصاديًا.</p>')
            ->where('companyInfo.mission_en', '<p>Existing English mission.</p>'));
});
