<?php

namespace Database\Seeders;

use App\Models\CompanyInfo;
use App\Models\HeroSlide;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PlastexContentSeeder extends Seeder
{
    public function run(): void
    {
        $companyInfo = CompanyInfo::query()->first() ?? new CompanyInfo;

        $companyInfo->fill([
            'name_ar' => 'الصناعة الإبداعية',
            'name_en' => 'Creative Industry',
            'hero_title_ar' => "حلول بلاستيكية\nتصنع مستقبل أفضل",
            'hero_title_en' => "Plastic solutions\nthat build a better future",
            'hero_description_ar' => 'نختص في تصنيع المنتجات البلاستيكية وفق متطلبات العملاء، بمعايير جودة عالية وإنتاج مرن يلبي احتياجات القطاعات المختلفة.',
            'hero_description_en' => 'We specialize in manufacturing plastic products according to customer requirements, with high quality standards and flexible production for diverse sectors.',
            'about_ar' => 'الصناعة الإبداعية شركة بإدارة عمانية وكادر مميز من مهندسين عمانيين ذوي خبرة أكثر من عشر سنوات.',
            'about_en' => 'Creative Industry is an Omani-managed company with a distinguished team of Omani engineers with more than ten years of experience.',
            ...\App\Support\HomepageContentDefaults::companyInfoFields(),
        ]);

        $companyInfo->save();

        $this->replaceAttachment(
            model: $companyInfo,
            sourcePath: public_path('images/creative-industry/logo.jpeg'),
            storageDir: 'company-info',
            fileName: 'creative-industry-logo.jpeg',
        );

        $heroSlide = HeroSlide::query()->orderBy('ordering')->first();

        if (! $heroSlide) {
            $heroSlide = HeroSlide::create([
                'title_ar' => '',
                'title_en' => '',
                'description_ar' => '',
                'description_en' => '',
                'ordering' => 0,
                'is_active' => true,
            ]);
        } else {
            $heroSlide->update([
                'title_ar' => '',
                'title_en' => '',
                'description_ar' => '',
                'description_en' => '',
                'is_active' => true,
            ]);
        }

        $this->replaceAttachment(
            model: $heroSlide,
            sourcePath: public_path('images/plastex/hero.jpg'),
            storageDir: 'hero-slides',
            fileName: 'plastex-hero.jpg',
        );
    }

    private function replaceAttachment(object $model, string $sourcePath, string $storageDir, string $fileName): void
    {
        if (! File::exists($sourcePath)) {
            return;
        }

        $existing = $model->attachment;
        if ($existing?->path && Storage::disk('public')->exists($existing->path)) {
            Storage::disk('public')->delete($existing->path);
        }
        $existing?->delete();

        $contents = File::get($sourcePath);
        $storagePath = $storageDir.'/'.uniqid('creative_industry_', true).'-'.$fileName;
        Storage::disk('public')->put($storagePath, $contents);

        $model->attachment()->create([
            'name' => $fileName,
            'path' => $storagePath,
        ]);
    }
}
