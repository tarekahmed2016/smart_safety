<?php

use App\Enums\ClientPartnerType;
use App\Models\ClientPartner;
use App\Models\HomepageSection;
use App\Models\TeamMember;
use Database\Seeders\HomepageSectionSeeder;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(HomepageSectionSeeder::class);
});

test('team members and clients partners tables contain show_on_homepage column', function () {
    expect(Schema::hasColumn('team_members', 'show_on_homepage'))->toBeTrue()
        ->and(Schema::hasColumn('clients_partners', 'show_on_homepage'))->toBeTrue();
});

test('homepage sections include team members and clients partners', function () {
    expect(HomepageSection::query()->where('key', 'team_members')->exists())->toBeTrue()
        ->and(HomepageSection::query()->where('key', 'clients_partners')->exists())->toBeTrue();
});

test('homepage only includes team members flagged for homepage display', function () {
    TeamMember::factory()->create([
        'name_en' => 'Visible Member',
        'is_active' => true,
        'show_on_homepage' => true,
        'ordering' => 1,
    ]);

    TeamMember::factory()->create([
        'name_en' => 'Hidden Member',
        'is_active' => true,
        'show_on_homepage' => false,
        'ordering' => 2,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('teamMembers', 1)
            ->where('teamMembers.0.name_en', 'Visible Member'));
});

test('homepage only includes clients and partners flagged for homepage display', function () {
    ClientPartner::factory()->client()->create([
        'name_en' => 'Visible Client',
        'is_active' => true,
        'show_on_homepage' => true,
        'ordering' => 1,
    ]);

    ClientPartner::factory()->partner()->create([
        'name_en' => 'Hidden Partner',
        'is_active' => true,
        'show_on_homepage' => false,
        'ordering' => 2,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('clientsPartners', 1)
            ->where('clientsPartners.0.name_en', 'Visible Client')
            ->has('clients', 1)
            ->has('partners', 0));
});

test('homepage orders team members and clients partners by ordering', function () {
    TeamMember::factory()->create([
        'name_en' => 'Second Member',
        'ordering' => 2,
        'show_on_homepage' => true,
    ]);

    TeamMember::factory()->create([
        'name_en' => 'First Member',
        'ordering' => 1,
        'show_on_homepage' => true,
    ]);

    ClientPartner::factory()->client()->create([
        'name_en' => 'Second Client',
        'ordering' => 2,
        'show_on_homepage' => true,
    ]);

    ClientPartner::factory()->partner()->create([
        'name_en' => 'First Partner',
        'ordering' => 1,
        'show_on_homepage' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('teamMembers.0.name_en', 'First Member')
            ->where('teamMembers.1.name_en', 'Second Member')
            ->where('clientsPartners.0.name_en', 'First Partner')
            ->where('clientsPartners.1.name_en', 'Second Client'));
});

test('hiding team members section removes it from homepage sections payload', function () {
    HomepageSection::query()->where('key', 'team_members')->update(['is_visible' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', fn ($sections) => collect($sections)->where('key', 'team_members')->isEmpty()));
});

test('hiding clients partners section removes it from homepage sections payload', function () {
    HomepageSection::query()->where('key', 'clients_partners')->update(['is_visible' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', fn ($sections) => collect($sections)->where('key', 'clients_partners')->isEmpty()));
});

test('homepage passes clientsPartners prop', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('clientsPartners'));
});
