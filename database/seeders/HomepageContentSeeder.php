<?php

namespace Database\Seeders;

use App\Enums\HomepagePromoType;
use App\Models\CompanyInfo;
use App\Models\HomepagePromoBlock;
use App\Support\HomepageContentDefaults;
use App\Support\HomepageWhyUsContentDefaults;
use Illuminate\Database\Seeder;

class HomepageContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCompanyHomepageFields();
        $this->seedFeatureHighlights();
        $this->seedWhyUsHighlights();
        $this->seedIndustries();
        $this->seedCustomManufacturing();
    }

    private function seedCompanyHomepageFields(): void
    {
        $companyInfo = CompanyInfo::query()->first();

        if (! $companyInfo) {
            return;
        }

        $companyInfo->fill(HomepageContentDefaults::companyInfoFields());
        $companyInfo->save();
    }

    private function seedFeatureHighlights(): void
    {
        $features = [
            [
                'icon' => 'handshake',
                'title_ar' => 'شراكة طويلة الأمد',
                'title_en' => 'Long-term partnership',
                'description_ar' => '<p>مع عملائنا</p>',
                'description_en' => '<p>With our customers</p>',
            ],
            [
                'icon' => 'experience',
                'title_ar' => 'خبرة واسعة',
                'title_en' => 'Broad experience',
                'description_ar' => '<p>في مجال البلاستيك</p>',
                'description_en' => '<p>In plastics manufacturing</p>',
            ],
            [
                'icon' => 'flexible',
                'title_ar' => 'إنتاج مرن',
                'title_en' => 'Flexible production',
                'description_ar' => '<p>حسب احتياجاتك</p>',
                'description_en' => '<p>Tailored to your needs</p>',
            ],
            [
                'icon' => 'quality',
                'title_ar' => 'جودة عالية',
                'title_en' => 'High quality',
                'description_ar' => '<p>في كل منتج</p>',
                'description_en' => '<p>In every product</p>',
            ],
        ];

        foreach ($features as $index => $feature) {
            $block = HomepagePromoBlock::query()
                ->where('type', HomepagePromoType::FeatureHighlight)
                ->orderBy('ordering')
                ->skip($index)
                ->first();

            $payload = [
                'type' => HomepagePromoType::FeatureHighlight,
                'icon' => $feature['icon'],
                'title_ar' => $feature['title_ar'],
                'title_en' => $feature['title_en'],
                'description_ar' => $feature['description_ar'],
                'description_en' => $feature['description_en'],
                'ordering' => $index,
                'is_active' => true,
            ];

            if ($block) {
                $block->update($payload);
            } else {
                HomepagePromoBlock::create($payload);
            }
        }
    }

    private function seedWhyUsHighlights(): void
    {
        foreach (HomepageWhyUsContentDefaults::items() as $item) {
            $block = HomepagePromoBlock::query()
                ->where('type', HomepagePromoType::WhyUsHighlight)
                ->where(function ($query) use ($item) {
                    $query->where('ordering', $item['ordering'])
                        ->orWhereIn('title_en', $item['match_en'])
                        ->orWhereIn('title_ar', $item['match_ar']);
                })
                ->orderByRaw('CASE WHEN ordering = ? THEN 0 ELSE 1 END', [$item['ordering']])
                ->first();

            $payload = [
                'type' => HomepagePromoType::WhyUsHighlight,
                'icon' => $item['icon'],
                'title_ar' => $item['title_ar'],
                'title_en' => $item['title_en'],
                'description_ar' => $item['description_ar'],
                'description_en' => $item['description_en'],
                'ordering' => $item['ordering'],
                'is_active' => true,
            ];

            if ($block) {
                $block->update($payload);
            } else {
                HomepagePromoBlock::create($payload);
            }
        }
    }

    private function seedIndustries(): void
    {
        $industries = [
            ['icon' => 'food', 'title_ar' => 'الأغذية والمشروبات', 'title_en' => 'Food and beverages'],
            ['icon' => 'agri', 'title_ar' => 'الزراعة', 'title_en' => 'Agriculture'],
            ['icon' => 'industry', 'title_ar' => 'الصناعة', 'title_en' => 'Industry'],
            ['icon' => 'packing', 'title_ar' => 'التعبئة والتغليف', 'title_en' => 'Packaging'],
            ['icon' => 'home', 'title_ar' => 'المنزلية والاستهلاكية', 'title_en' => 'Household and consumer'],
            ['icon' => 'medical', 'title_ar' => 'القطاع الطبي', 'title_en' => 'Medical sector'],
        ];

        foreach ($industries as $index => $industry) {
            $block = HomepagePromoBlock::query()
                ->where('type', HomepagePromoType::Industry)
                ->orderBy('ordering')
                ->skip($index)
                ->first();

            $payload = [
                'type' => HomepagePromoType::Industry,
                'icon' => $industry['icon'],
                'title_ar' => $industry['title_ar'],
                'title_en' => $industry['title_en'],
                'ordering' => $index,
                'is_active' => true,
            ];

            if ($block) {
                $block->update($payload);
            } else {
                HomepagePromoBlock::create($payload);
            }
        }
    }

    private function seedCustomManufacturing(): void
    {
        $block = HomepagePromoBlock::query()
            ->where('type', HomepagePromoType::CustomManufacturing)
            ->orderBy('ordering')
            ->first();

        $payload = [
            'type' => HomepagePromoType::CustomManufacturing,
            'title_ar' => 'التصنيع حسب الطلب',
            'title_en' => 'Custom manufacturing',
            'description_ar' => '<p>نصنع المنتجات البلاستيكية وفق متطلباتك، من الفكرة حتى الإنتاج.</p>',
            'description_en' => '<p>We manufacture plastic products according to your requirements, from concept to production.</p>',
            'cta_text_ar' => 'اطلب منتجك الآن',
            'cta_text_en' => 'Order your product now',
            'cta_url' => '#contact',
            'ordering' => $block?->ordering ?? 0,
            'is_active' => true,
        ];

        if ($block) {
            $block->update($payload);
        } else {
            HomepagePromoBlock::create($payload);
        }
    }
}
