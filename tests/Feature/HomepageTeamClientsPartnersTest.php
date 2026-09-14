<?php

use App\Models\ClientPartner;
use App\Models\HomepageSection;
use App\Models\TeamMember;
use Database\Seeders\HomepageSectionSeeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(HomepageSectionSeeder::class);
});

test('team members and clients partners tables contain show_on_homepage column', function () {
    expect(Schema::hasColumn('team_members', 'show_on_homepage'))->toBeTrue()
        ->and(Schema::hasColumn('clients_partners', 'show_on_homepage'))->toBeTrue()
        ->and(Schema::hasColumn('clients_partners', 'show_type_badge'))->toBeTrue();
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

test('homepage exposes show_type_badge on clients partners cards', function () {
    ClientPartner::factory()->client()->create([
        'name_en' => 'Badge Client',
        'show_type_badge' => true,
        'show_on_homepage' => true,
        'is_active' => true,
        'ordering' => 1,
    ]);

    ClientPartner::factory()->partner()->create([
        'name_en' => 'Plain Partner',
        'show_type_badge' => false,
        'show_on_homepage' => true,
        'is_active' => true,
        'ordering' => 2,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('clientsPartners.0.show_type_badge', true)
            ->where('clientsPartners.1.show_type_badge', false)
            ->where('clientsPartners.0.name_en', 'Badge Client'));
});

test('public client partner cards hide empty names and optional type badges', function () {
    $carousel = file_get_contents(resource_path('js/Components/Public/ClientsPartnersCarousel.vue'));
    $form = file_get_contents(resource_path('js/Components/Features/ClientsPartners/ClientPartnerFormModal.vue'));
    $styles = file_get_contents(resource_path('css/plastex.css'));
    $ar = file_get_contents(resource_path('js/Plugins/I18n/Locales/ar.json'));
    $en = file_get_contents(resource_path('js/Plugins/I18n/Locales/en.json'));

    $mediaBlock = Str::between($styles, '.px-client-media {', '}');
    $imageBlock = Str::between($styles, '.px-client-media img {', '}');

    expect($form)->toContain('form.show_type_badge')
        ->and($form)->toContain("t('clientsPartners.form.showTypeBadgeLabel')")
        ->and(strpos($form, 'form.type'))->toBeLessThan(strpos($form, 'form.show_type_badge'))
        ->and(strpos($form, 'form.show_type_badge'))->toBeLessThan(strpos($form, "t('bilingual.arabic')"))
        ->and($form)->not->toContain('form.name_ar" type="text" required')
        ->and($carousel)->toContain('v-if="showTextArea(item)"')
        ->and($carousel)->toContain('v-if="showTypeBadge(item)"')
        ->and($carousel)->toContain('v-if="itemName(item)"')
        ->and($carousel)->toContain('px-client-card--media-only')
        ->and($ar)->toContain('إظهار نوع العميل/الشريك')
        ->and($en)->toContain('Show client/partner type')
        ->and($mediaBlock)->toContain('height: 150px')
        ->and($mediaBlock)->toContain('overflow: hidden')
        ->and($mediaBlock)->toContain('background: transparent')
        ->and($mediaBlock)->toContain('border: 0')
        ->and($mediaBlock)->toContain('box-shadow: none')
        ->and($mediaBlock)->toContain('align-items: center')
        ->and($mediaBlock)->toContain('justify-content: center')
        ->and($imageBlock)->toContain('max-width: 100%')
        ->and($imageBlock)->toContain('max-height: 100%')
        ->and($imageBlock)->toContain('object-fit: contain')
        ->and($imageBlock)->toContain('width: auto')
        ->and($imageBlock)->toContain('height: auto')
        ->and($styles)->toContain('.px-client-card--media-only {')
        ->and($styles)->toContain('background: #fff')
        ->and($styles)->not->toContain('.px-client-card--media-only .px-client-media {');
});
