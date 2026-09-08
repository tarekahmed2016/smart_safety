<?php

use App\Enums\HomepageSectionType;
use App\Models\HomepageSection;
use App\Support\HomepageSectionNavigation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        HomepageSection::query()
            ->where('ordering', '>=', 8)
            ->increment('ordering', 2);

        $navDefaults = HomepageSectionNavigation::defaultsByKey();

        HomepageSection::query()->updateOrCreate(
            ['key' => 'team_members'],
            [
                'name' => 'Team Members',
                'type' => HomepageSectionType::TeamMembers,
                'ordering' => 8,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
                ...($navDefaults['team_members'] ?? [
                    'show_in_navigation' => false,
                    'nav_label_ar' => null,
                    'nav_label_en' => null,
                    'nav_order' => 55,
                    'anchor_id' => 'team',
                ]),
            ],
        );

        HomepageSection::query()->updateOrCreate(
            ['key' => 'clients_partners'],
            [
                'name' => 'Clients & Partners',
                'type' => HomepageSectionType::ClientsPartners,
                'ordering' => 9,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
                ...($navDefaults['clients_partners'] ?? [
                    'show_in_navigation' => false,
                    'nav_label_ar' => null,
                    'nav_label_en' => null,
                    'nav_order' => 60,
                    'anchor_id' => 'clients-partners',
                ]),
            ],
        );
    }

    public function down(): void
    {
        HomepageSection::query()->whereIn('key', ['team_members', 'clients_partners'])->delete();

        HomepageSection::query()
            ->where('ordering', '>=', 10)
            ->decrement('ordering', 2);
    }
};
