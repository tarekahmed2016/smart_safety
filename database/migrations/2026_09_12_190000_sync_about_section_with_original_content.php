<?php

use App\Enums\HomepagePromoType;
use App\Models\CompanyInfo;
use App\Models\HomepagePromoBlock;
use App\Support\HomepageAboutContentDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('company_info', 'about_highlight_ar')) {
            Schema::table('company_info', function (Blueprint $table) {
                $table->string('about_highlight_ar')->nullable()->after('about_section_title_en');
                $table->string('about_highlight_en')->nullable()->after('about_highlight_ar');
            });
        }

        CompanyInfo::query()->update(HomepageAboutContentDefaults::companyFields());

        foreach (HomepageAboutContentDefaults::stats() as $stat) {
            $this->upsertPromo(HomepagePromoType::Stat, $stat);
        }

        foreach (HomepageAboutContentDefaults::highlights() as $highlight) {
            $this->upsertPromo(HomepagePromoType::AboutHighlight, $highlight);
        }
    }

    public function down(): void
    {
        HomepagePromoBlock::query()
            ->where('type', HomepagePromoType::AboutHighlight)
            ->delete();

        if (Schema::hasColumn('company_info', 'about_highlight_ar')) {
            Schema::table('company_info', function (Blueprint $table) {
                $table->dropColumn(['about_highlight_ar', 'about_highlight_en']);
            });
        }
    }

    /**
     * @param  array{title_ar: string, title_en: string, description_ar: string, description_en: string, icon?: string|null, ordering: int}  $item
     */
    private function upsertPromo(HomepagePromoType $type, array $item): void
    {
        $block = HomepagePromoBlock::query()
            ->where('type', $type)
            ->where(function ($query) use ($item) {
                $query->where('ordering', $item['ordering'])
                    ->orWhere('title_en', $item['title_en']);
            })
            ->orderByRaw('CASE WHEN ordering = ? THEN 0 ELSE 1 END', [$item['ordering']])
            ->first();

        $payload = [
            'type' => $type,
            'title_ar' => $item['title_ar'],
            'title_en' => $item['title_en'],
            'description_ar' => $item['description_ar'],
            'description_en' => $item['description_en'],
            'icon' => $item['icon'] ?? null,
            'ordering' => $item['ordering'],
            'is_active' => true,
        ];

        if ($block) {
            $block->update($payload);
        } else {
            HomepagePromoBlock::create($payload);
        }
    }
};
