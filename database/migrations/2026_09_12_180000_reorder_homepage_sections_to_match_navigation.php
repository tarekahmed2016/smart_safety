<?php

use App\Models\HomepageSection;
use App\Support\HomepageSectionDefaults;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (HomepageSectionDefaults::sections() as $section) {
            HomepageSection::query()
                ->where('key', $section['key'])
                ->update(['ordering' => $section['ordering']]);
        }
    }

    public function down(): void
    {
        $previous = [
            'hero' => 1,
            'features' => 2,
            'products' => 3,
            'services' => 4,
            'custom_manufacturing' => 5,
            'industries' => 6,
            'about' => 7,
            'vision_mission' => 8,
            'goals' => 9,
            'team_members' => 10,
            'clients_partners' => 11,
            'gallery' => 12,
            'contact_cta' => 13,
            'contact' => 14,
        ];

        foreach ($previous as $key => $ordering) {
            HomepageSection::query()->where('key', $key)->update(['ordering' => $ordering]);
        }
    }
};
