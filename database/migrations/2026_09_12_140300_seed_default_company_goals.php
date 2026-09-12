<?php

use App\Models\CompanyGoal;
use App\Support\CompanyGoalDefaults;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (CompanyGoal::query()->exists()) {
            return;
        }

        foreach (CompanyGoalDefaults::itemsWithCmsEnglish() as $index => $item) {
            CompanyGoal::query()->create([
                'text_ar' => $item['text_ar'],
                'text_en' => $item['text_en'],
                'ordering' => $index + 1,
                'is_active' => true,
            ]);
        }
    }

    public function down(): void
    {
        $defaultArabic = collect(CompanyGoalDefaults::items())->pluck('text_ar')->all();

        CompanyGoal::query()
            ->whereIn('text_ar', $defaultArabic)
            ->delete();
    }
};
