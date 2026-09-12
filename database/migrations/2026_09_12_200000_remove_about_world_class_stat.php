<?php

use App\Enums\HomepagePromoType;
use App\Models\HomepagePromoBlock;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        HomepagePromoBlock::query()
            ->where('type', HomepagePromoType::Stat)
            ->where(function ($query) {
                $query->where('title_ar', 'معايير عالمية')
                    ->orWhere('title_en', 'World-class standards');
            })
            ->delete();

        HomepagePromoBlock::query()
            ->where('type', HomepagePromoType::AboutHighlight)
            ->where('title_ar', 'صحة وسلامة')
            ->update(['icon' => 'shield']);
    }

    public function down(): void
    {
        HomepagePromoBlock::query()
            ->where('type', HomepagePromoType::AboutHighlight)
            ->where('title_ar', 'صحة وسلامة')
            ->update(['icon' => 'medical']);
    }
};
