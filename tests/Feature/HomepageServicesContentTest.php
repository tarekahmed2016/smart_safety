<?php

use App\Models\CompanyInfo;
use App\Models\Service;
use App\Support\HomepageServicesContentDefaults;
use Database\Seeders\HomepageSectionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(HomepageSectionSeeder::class);
});

test('homepage services section uses original manufacturing copy', function () {
    CompanyInfo::create([
        'name_ar' => 'الصناعة الإبداعية',
        'name_en' => 'Creative Industry',
        'products_homepage_limit' => 8,
    ]);

    foreach (HomepageServicesContentDefaults::items() as $service) {
        Service::query()->create([
            'name_ar' => $service['name_ar'],
            'name_en' => $service['name_en'],
            'description_ar' => $service['description_ar'],
            'description_en' => $service['description_en'],
            'ordering' => $service['ordering'],
            'is_active' => true,
            'show_on_homepage' => true,
        ]);
    }

    Service::factory()->create([
        'name_ar' => 'تطبيقات الجوال',
        'name_en' => 'Mobile apps',
        'show_on_homepage' => false,
        'ordering' => 9,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', function ($sections) {
                $servicesSection = collect($sections)->first(
                    fn ($section) => ($section['key'] ?? null) === 'services' || ($section['type'] ?? null) === 'services'
                );

                return is_array($servicesSection)
                    && ($servicesSection['title_ar'] ?? null) === 'خدمات التصنيع';
            })
            ->has('services', 2)
            ->where('services.0.name_ar', 'التصنيع')
            ->where('services.0.description_ar', 'حلول تصنيع صناعية وتوفير خطوط الإنتاج.')
            ->where('services.1.name_ar', 'الصناعة البلاستيكية')
            ->where('services.1.description_ar', 'مثل الأغطية والعلب ومواد التغليف.'));
});
