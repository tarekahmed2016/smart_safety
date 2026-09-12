<?php

namespace Database\Seeders;

use App\Enums\ClientPartnerType;
use App\Enums\HomepagePromoType;
use App\Models\ClientPartner;
use App\Models\CompanyGoal;
use App\Models\CompanyInfo;
use App\Models\HomepagePromoBlock;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Support\CompanyGoalDefaults;
use App\Support\HomepageWhyUsContentDefaults;
use Illuminate\Support\Str;

class CreativeIndustryContentSeeder extends Seeder
{
    private const SOURCE_BASE = 'https://creative-industry-kappa.vercel.app';

    /**
     * @var list<string>
     */
    private const PRESERVED_COMPANY_FIELDS = [
        'hero_title_ar',
        'hero_title_en',
        'hero_description_ar',
        'hero_description_en',
    ];

    public function run(): void
    {
        $this->seedCompanyInfo();
        $this->seedBusinessCta();
        $this->seedStats();
        $this->seedAboutHighlights();
        $this->seedServices();
        $this->seedProducts();
        $this->seedClients();
        $this->seedPages();
        $this->seedCompanyGoals();
    }

    private function seedCompanyInfo(): void
    {
        $companyInfo = CompanyInfo::query()->first() ?? CompanyInfo::create(
            app(\App\Services\CompanyInfoService::class)->emptyDefaults()
        );

        $preserved = $companyInfo->only(self::PRESERVED_COMPANY_FIELDS);

        $companyInfo->fill([
            ...$preserved,
            'name_ar' => 'الصناعة الإبداعية',
            'name_en' => 'Creative Industry',
            ...\App\Support\HomepageAboutContentDefaults::companyFields(),
            'vision_ar' => '<p>تكوين مجموعة شركات صناعية متكاملة ناجحة اقتصاديًا، متقدمة علميًا وإداريًا وصناعيًا، لخدمة المجتمع والمساهمة في التنمية المستدامة.</p>',
            'vision_en' => '<p>To build an integrated group of companies that are economically successful and advanced scientifically, administratively, and industrially, serving society and contributing to sustainable development.</p>',
            'mission_ar' => '<p>تقديم حلول صناعية احترافية تسهّل الأداء على المصانع وعلى العلامات التجارية، بأحدث التقنيات وأعلى معايير الجودة.</p>',
            'mission_en' => '<p>To deliver professional industrial solutions that improve performance for factories and brands using the latest technologies and the highest quality standards.</p>',
            'phone' => '+968 9513 6368',
            'email' => 'info@creativesindustry.com',
            'whatsapp' => 'https://wa.me/96895136368',
            'address_ar' => 'سلطنة عُمان / مسقط',
            'address_en' => 'Muscat, Sultanate of Oman',
            ...\App\Support\HomepageContentDefaults::companyInfoFields(),
        ]);

        $companyInfo->save();
    }

    private function seedBusinessCta(): void
    {
        $block = HomepagePromoBlock::query()
            ->where('type', HomepagePromoType::BusinessCta)
            ->orderBy('ordering')
            ->first();

        $payload = [
            'type' => HomepagePromoType::BusinessCta,
            'title_ar' => 'هل لديك مشروع في ذهنك؟',
            'title_en' => 'Have a project in mind?',
            'description_ar' => '<p>نحن هنا لتحويل أفكارك الصناعية إلى واقع ملموس بأعلى معايير الجودة.</p>',
            'description_en' => '<p>We are here to turn your industrial ideas into reality with the highest quality standards.</p>',
            'cta_text_ar' => 'تواصل معنا',
            'cta_text_en' => 'Contact us',
            'cta_url' => '#contact-form',
            'ordering' => $block?->ordering ?? 0,
            'is_active' => true,
        ];

        if ($block) {
            $block->update($payload);
        } else {
            HomepagePromoBlock::create($payload);
        }
    }

    private function seedStats(): void
    {
        foreach (\App\Support\HomepageAboutContentDefaults::stats() as $stat) {
            $this->upsertPromoBlock(HomepagePromoType::Stat, $stat);
        }
    }

    private function seedAboutHighlights(): void
    {
        foreach (\App\Support\HomepageAboutContentDefaults::highlights() as $highlight) {
            $this->upsertPromoBlock(HomepagePromoType::AboutHighlight, $highlight);
        }
    }

    /**
     * @param  array{title_ar: string, title_en: string, description_ar: string, description_en: string, icon?: string|null, ordering: int}  $item
     */
    private function upsertPromoBlock(HomepagePromoType $type, array $item): void
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

    private function seedServices(): void
    {
        $keptIds = [];

        foreach (\App\Support\HomepageServicesContentDefaults::items() as $service) {
            $record = \App\Models\Service::query()
                ->where(function ($query) use ($service) {
                    $query->whereIn('name_en', $service['match_en'])
                        ->orWhereIn('name_ar', $service['match_ar']);
                })
                ->first();

            $payload = [
                'name_ar' => $service['name_ar'],
                'name_en' => $service['name_en'],
                'description_ar' => $service['description_ar'],
                'description_en' => $service['description_en'],
                'ordering' => $service['ordering'],
                'is_active' => true,
                'show_on_homepage' => true,
            ];

            if ($record) {
                $record->update($payload);
            } else {
                $record = \App\Models\Service::create($payload);
            }

            $keptIds[] = $record->id;
        }

        if ($keptIds !== []) {
            \App\Models\Service::query()
                ->whereNotIn('id', $keptIds)
                ->update(['show_on_homepage' => false]);
        }

        $sectionCopy = \App\Support\HomepageServicesContentDefaults::section();
        $section = \App\Models\HomepageSection::query()->where('key', 'services')->first();

        if ($section) {
            $section->update([
                'title_ar' => $sectionCopy['title_ar'],
                'title_en' => $sectionCopy['title_en'],
                'settings' => array_merge($section->settings ?? [], [
                    'highlight_ar' => $sectionCopy['highlight_ar'],
                    'highlight_en' => $sectionCopy['highlight_en'],
                    'subtitle_ar' => $sectionCopy['subtitle_ar'],
                    'subtitle_en' => $sectionCopy['subtitle_en'],
                ]),
            ]);
        }
    }

    private function seedProducts(): void
    {
        $products = [
            [
                'slug' => 'pp-strap',
                'name_ar' => 'شريط PP',
                'name_en' => 'PP Strap',
                'description_ar' => 'شريط تغليف بلاستيكي عالي المتانة لربط وتثبيت الشحنات بكفاءة.',
                'description_en' => 'Durable plastic strapping for efficient load securing and bundling.',
                'image' => 'PP strap.da81e9d9.JPG',
            ],
            [
                'slug' => 'ring-holder',
                'name_ar' => 'حامل رولات الكرتون',
                'name_en' => 'Ring Holder',
                'description_ar' => 'حامل بلاستيكي لتثبيت رولات الكرتون أثناء النقل والتخزين.',
                'description_en' => 'Plastic holder for stabilizing carton rolls during transport and storage.',
                'image' => 'Ring holder.6b8c83fb.JPG',
            ],
            [
                'slug' => 'plastic-aluminum-coil-core-plug',
                'name_ar' => 'سدادة لب رول الألومنيوم',
                'name_en' => 'Plastic Aluminum Coil Core Plug',
                'description_ar' => 'سدادة بلاستيكية لحماية وتثبيت لب الرولات المعدنية أثناء التصنيع والشحن.',
                'description_en' => 'Plastic plug for protecting and securing metal coil cores during production and shipping.',
                'image' => 'Core plug.f1e4a594.JPG',
            ],
            [
                'slug' => 'pp-strap-clip',
                'name_ar' => 'مشبك ربط شريط PP',
                'name_en' => 'PP Strap Clip',
                'description_ar' => 'مشبك قوي لربط وإغلاق شرائط PP بسرعة وأمان.',
                'description_en' => 'Strong clip for quickly and safely sealing PP straps.',
                'image' => 'PP Strap Clip.5b3809e8.JPG',
            ],
            [
                'slug' => 'pet-strap',
                'name_ar' => 'شريط ربط PET',
                'name_en' => 'PET Strap',
                'description_ar' => 'شريط PET عالي القوة للتغليف الصناعي والشحنات الثقيلة.',
                'description_en' => 'High-strength PET strapping for industrial packaging and heavy shipments.',
                'image' => 'PET strap.3793cb13.JPG',
            ],
            [
                'slug' => 'stretch-film',
                'name_ar' => 'فيلم التغليف والتمدد',
                'name_en' => 'Stretch Film',
                'description_ar' => 'فيلم تمدد لحماية المنتجات وتثبيتها على البالتات أثناء النقل.',
                'description_en' => 'Stretch film for protecting products and securing pallet loads during transit.',
                'image' => 'Stretch film.8195151c.JPG',
            ],
            [
                'slug' => 'manual-pp-pet-strapping-tool-set',
                'name_ar' => 'طقم أدوات ربط يدوي لشريط PP و PET',
                'name_en' => 'Manual PP & PET Strapping Tool Set',
                'description_ar' => 'طقم أدوات يدوية لربط شرائط PP وPET بسهولة ودقة.',
                'description_en' => 'Manual tool set for applying PP and PET straps with ease and precision.',
                'image' => 'Strapping Tool Set.2309b507.JPG',
            ],
            [
                'slug' => 'manual-pet-pp-strap-sealing-machine',
                'name_ar' => 'ماكينة لحام يدوية لشريط PET/PP',
                'name_en' => 'Manual PET/PP Strap Sealing Machine',
                'description_ar' => 'ماكينة لحام يدوية لإغلاق شرائط PET وPP بجودة ثابتة.',
                'description_en' => 'Manual sealing machine for consistent PET and PP strap closures.',
                'image' => 'Strap Sealing Machine.9ded281d.JPG',
            ],
        ];

        foreach ($products as $index => $productData) {
            $product = Product::query()->where('slug', $productData['slug'])->first();

            $payload = [
                'name_ar' => $productData['name_ar'],
                'name_en' => $productData['name_en'],
                'slug' => $productData['slug'],
                'description_ar' => '<p>'.$productData['description_ar'].'</p>',
                'description_en' => '<p>'.$productData['description_en'].'</p>',
                'ordering' => $index,
                'is_active' => true,
            ];

            if ($product) {
                $product->update($payload);
            } else {
                $product = Product::create($payload);
            }

            if (! $product->attachment) {
                $this->attachRemoteImage(
                    model: $product,
                    storageDir: 'products',
                    remotePath: '/_next/static/media/'.$productData['image'],
                    fileName: Str::slug($productData['slug']).'.jpg',
                );
            }
        }
    }

    private function seedClients(): void
    {
        $clients = [
            ['file' => 'Success1.78c06b25.JPG', 'name_ar' => 'شريك نجاح 1', 'name_en' => 'Success Partner 1'],
            ['file' => 'Success2.505b6372.JPG', 'name_ar' => 'شريك نجاح 2', 'name_en' => 'Success Partner 2'],
            ['file' => 'Success3.f92bbf56.JPG', 'name_ar' => 'شريك نجاح 3', 'name_en' => 'Success Partner 3'],
            ['file' => 'Success4.1d02b2bb.JPG', 'name_ar' => 'شريك نجاح 4', 'name_en' => 'Success Partner 4'],
            ['file' => 'Success5.dd4d4052.JPG', 'name_ar' => 'شريك نجاح 5', 'name_en' => 'Success Partner 5'],
            ['file' => 'Success6.eeff7352.jpg', 'name_ar' => 'شريك نجاح 6', 'name_en' => 'Success Partner 6'],
            ['file' => 'Success7.c5225f80.jpeg', 'name_ar' => 'شريك نجاح 7', 'name_en' => 'Success Partner 7'],
        ];

        foreach ($clients as $index => $client) {
            $record = ClientPartner::query()
                ->where('type', ClientPartnerType::Client)
                ->where('name_en', $client['name_en'])
                ->first();

            $payload = [
                'type' => ClientPartnerType::Client,
                'name_ar' => $client['name_ar'],
                'name_en' => $client['name_en'],
                'website' => null,
                'ordering' => $index,
                'is_active' => true,
            ];

            if ($record) {
                $record->update($payload);
            } else {
                $record = ClientPartner::create($payload);
            }

            if (! $record->attachment) {
                $this->attachRemoteImage(
                    model: $record,
                    storageDir: 'clients-partners',
                    remotePath: '/_next/static/media/'.$client['file'],
                    fileName: 'client-'.($index + 1).'.jpg',
                );
            }
        }
    }

    private function seedPages(): void
    {
        $goalsAr = [
            'نساهم في زرع الثقة في المنتج العماني ومنحه المجال للمنافسة في الأسواق العالمية.',
            'المساهمة في التحول بوتيرة أسرع نحو الثورة الصناعية الرابعة في السلطنة.',
            'المساهمة في وصول السلطنة إلى مصاف الدول المتقدمة وتحفيز التقدم في الصناعات التحويلية.',
            'بناء وتطوير المصانع بأحدث التقنيات وبأعلى معايير الجودة العالمية.',
            'تطوير المنتجات البلاستيكية وتصميمها باستخدام أفضل التقنيات الصناعية.',
            'تقديم الاستشارات الصناعية التي تسهم في تطوير المجال الصناعي في السلطنة.',
        ];

        $goalsEn = [
            'Building trust in Omani products and helping them compete in global markets.',
            'Accelerating the transition toward the Fourth Industrial Revolution in the Sultanate.',
            'Supporting Oman’s progress toward advanced economies and transformative manufacturing industries.',
            'Building and developing factories with modern technologies and global quality standards.',
            'Developing and designing plastic products using advanced industrial technologies.',
            'Providing industrial consulting that supports Oman’s industrial sector.',
        ];

        $this->upsertPage(
            slug: 'goals',
            titleAr: 'أهدافنا',
            titleEn: 'Our Goals',
            contentAr: $this->listHtml($goalsAr),
            contentEn: $this->listHtml($goalsEn),
        );

        $whyUs = HomepageWhyUsContentDefaults::items();

        $whyUsAr = array_map(
            fn (array $item) => '<li><strong>'.$item['title_ar'].'</strong><br>'.$item['description_ar'].'</li>',
            $whyUs,
        );
        $whyUsEn = array_map(
            fn (array $item) => '<li><strong>'.$item['title_en'].'</strong><br>'.$item['description_en'].'</li>',
            $whyUs,
        );

        $this->upsertPage(
            slug: 'why-us',
            titleAr: 'لماذا نحن',
            titleEn: 'Why Us',
            contentAr: '<ul>'.implode('', $whyUsAr).'</ul>',
            contentEn: '<ul>'.implode('', $whyUsEn).'</ul>',
        );

        foreach ($whyUs as $item) {
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

        foreach ($goalsAr as $index => $goalAr) {
            $block = HomepagePromoBlock::query()
                ->where('type', HomepagePromoType::PromoStrip)
                ->where('ordering', 100 + $index)
                ->first();

            $payload = [
                'type' => HomepagePromoType::PromoStrip,
                'title_ar' => 'هدف '.($index + 1),
                'title_en' => 'Goal '.($index + 1),
                'description_ar' => '<p>'.$goalAr.'</p>',
                'description_en' => '<p>'.$goalsEn[$index].'</p>',
                'ordering' => 100 + $index,
                'is_active' => true,
            ];

            if ($block) {
                $block->update($payload);
            } else {
                HomepagePromoBlock::create($payload);
            }
        }
    }

    private function seedCompanyGoals(): void
    {
        foreach (CompanyGoalDefaults::itemsWithCmsEnglish() as $index => $item) {
            CompanyGoal::query()->updateOrCreate(
                ['ordering' => $index + 1],
                [
                    'text_ar' => $item['text_ar'],
                    'text_en' => $item['text_en'],
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @param  list<string>  $items
     */
    private function listHtml(array $items): string
    {
        return '<ul>'.implode('', array_map(fn (string $item) => '<li>'.$item.'</li>', $items)).'</ul>';
    }

    private function upsertPage(
        string $slug,
        string $titleAr,
        string $titleEn,
        string $contentAr,
        string $contentEn,
    ): void {
        $page = Page::query()->where('slug', $slug)->first();

        $payload = [
            'title_ar' => $titleAr,
            'title_en' => $titleEn,
            'menu_title_ar' => $titleAr,
            'menu_title_en' => $titleEn,
            'slug' => $slug,
            'content_ar' => $contentAr,
            'content_en' => $contentEn,
            'show_in_main_menu' => false,
            'menu_order' => 200,
            'is_active' => true,
        ];

        if ($page) {
            $page->update($payload);
        } else {
            Page::create($payload);
        }
    }

    private function attachRemoteImage(Model $model, string $storageDir, string $remotePath, string $fileName): void
    {
        $url = self::SOURCE_BASE.$remotePath;
        $response = Http::timeout(30)->get($url);

        if (! $response->successful()) {
            return;
        }

        $storagePath = $storageDir.'/'.uniqid('migrated_', true).'-'.$fileName;
        Storage::disk('public')->put($storagePath, $response->body());

        $model->attachment()->create([
            'name' => $fileName,
            'path' => $storagePath,
        ]);
    }
}
