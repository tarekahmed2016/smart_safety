<?php

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('public catalog lists only active products', function () {
    Storage::fake('public');

    $visible = Product::factory()->create([
        'name_ar' => 'عبوة ظاهرة',
        'name_en' => 'Visible Pack',
        'slug' => 'visible-pack',
        'ordering' => 1,
        'is_active' => true,
    ]);
    Product::factory()->inactive()->create([
        'name_en' => 'Hidden Pack',
        'slug' => 'hidden-pack',
        'ordering' => 0,
    ]);

    $path = UploadedFile::fake()->image('pack.jpg')->store('products', 'public');
    $visible->attachment()->create(['name' => 'pack.jpg', 'path' => $path]);

    $this->get(route('public.products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/ProductsIndex', false)
            ->has('products', 1)
            ->where('products.0.name_en', 'Visible Pack')
            ->where('products.0.slug', 'visible-pack')
            ->where('products.0.image', asset('storage/'.$path))
            ->missing('products.0.id')
            ->missing('products.0.ordering')
            ->missing('products.0.is_active'));
});

test('public product details page shows an active product', function () {
    $product = Product::factory()->create([
        'name_ar' => 'غطاء',
        'name_en' => 'Cap',
        'slug' => 'cap',
        'description_ar' => 'وصف عربي',
        'description_en' => 'English description',
        'is_active' => true,
    ]);

    $this->get(route('public.products.show', $product->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/ProductShow', false)
            ->where('product.slug', 'cap')
            ->where('product.name_en', 'Cap')
            ->where('product.description_en', 'English description')
            ->missing('product.id')
            ->missing('product.is_active'));
});

test('inactive products are not publicly reachable', function () {
    Product::factory()->inactive()->create([
        'slug' => 'secret-product',
    ]);

    $this->get(route('public.products.show', 'secret-product'))
        ->assertNotFound();
});

test('public homepage products include bilingual details sizes and specifications', function () {
    $product = Product::factory()->create([
        'name_ar' => 'شريط PP',
        'name_en' => 'PP Strap',
        'slug' => 'pp-strap',
        'details_ar' => '<p>تفاصيل الشريط</p>',
        'details_en' => '<p>Strap details</p>',
        'sizes' => [
            ['value' => '5', 'unit' => 'mm'],
            ['value' => '12', 'unit' => 'mm'],
        ],
        'specifications_ar' => ['خفيف الوزن'],
        'specifications_en' => ['Lightweight'],
        'is_active' => true,
        'show_on_homepage' => true,
        'ordering' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/HomePage', false)
            ->has('products', 1)
            ->where('products.0.slug', 'pp-strap')
            ->where('products.0.name_ar', 'شريط PP')
            ->where('products.0.name_en', 'PP Strap')
            ->where('products.0.details_ar', '<p>تفاصيل الشريط</p>')
            ->where('products.0.details_en', '<p>Strap details</p>')
            ->where('products.0.sizes.0.value', '5')
            ->where('products.0.sizes.0.unit', 'mm')
            ->where('products.0.sizes.1.value', '12')
            ->where('products.0.specifications_ar.0', 'خفيف الوزن')
            ->where('products.0.specifications_en.0', 'Lightweight'));

    expect($product->name_en)->toBe('PP Strap');
});

test('public product payloads stay safe when details sizes and specifications are missing', function () {
    Product::factory()->create([
        'name_en' => 'Legacy Pack',
        'slug' => 'legacy-pack',
        'details_ar' => null,
        'details_en' => null,
        'sizes' => null,
        'specifications_ar' => null,
        'specifications_en' => null,
        'is_active' => true,
        'show_on_homepage' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.0.slug', 'legacy-pack')
            ->where('products.0.details_ar', null)
            ->where('products.0.details_en', null)
            ->where('products.0.sizes', [])
            ->where('products.0.specifications_ar', [])
            ->where('products.0.specifications_en', []));
});

test('products carousel opens a details modal from the product card', function () {
    $carousel = file_get_contents(resource_path('js/Components/Public/ProductsCarousel.vue'));
    $modal = file_get_contents(resource_path('js/Components/Public/ProductDetailsModal.vue'));
    $styles = file_get_contents(resource_path('css/plastex.css'));

    expect($carousel)->toContain('openProduct')
        ->and($carousel)->toContain('ProductDetailsModal')
        ->and($carousel)->toContain('@click.stop="openProduct(product)"')
        ->and($carousel)->toContain('@keydown="onCardKeydown(product, $event)"')
        ->and($carousel)->toContain('role="button"')
        ->and($carousel)->not->toContain("route('public.products.show'")
        ->and($carousel)->not->toContain("t('public.home.products.details')")
        ->and($carousel)->toContain('inquireAboutProduct')
        ->and($carousel)->toContain('@click.stop="inquireAboutProduct(product)"')
        ->and($styles)->toContain('.px-product-carousel .px-product-card')
        ->and($styles)->toContain('align-items: stretch')
        ->and($styles)->toContain('align-self: stretch')
        ->and($styles)->toContain('-webkit-line-clamp: 2')
        ->and($styles)->toContain('-webkit-line-clamp: 3')
        ->and($styles)->toContain('pointer-events: auto')
        ->and($styles)->toContain('cursor: pointer')
        ->and($modal)->toContain('<Teleport to="body">')
        ->and($modal)->toContain('v-show="isOpen"')
        ->and($modal)->toContain('@click.self="close"')
        ->and($modal)->toContain('useDialogAccessibility')
        ->and($modal)->toContain('px-product-modal-close')
        ->and($modal)->toContain('productName')
        ->and($modal)->toContain('product.image')
        ->and($modal)->toContain('hasDetails')
        ->and($modal)->toContain('px-product-modal-layout')
        ->and($modal)->toContain('px-product-modal-copy')
        ->and($modal)->toContain('px-product-modal-media')
        ->and($modal)->toContain('px-product-size-pill')
        ->and($modal)->not->toContain('specifications')
        ->and($modal)->not->toContain('px-product-modal-specs')
        ->and($modal)->toContain("locale.value === 'ar'")
        ->and($modal)->not->toContain('action_text')
        ->and($modal)->not->toContain('action_url')
        ->and($modal)->not->toContain('cta_url');

    $copyPos = strpos($modal, 'px-product-modal-copy');
    $titlePos = strpos($modal, 'px-product-modal-title');
    $sizesPos = strpos($modal, 'px-product-modal-sizes');
    $detailsPos = strpos($modal, 'px-product-modal-details');
    $mediaPos = strpos($modal, 'class="px-product-modal-media"');

    expect($copyPos)->toBeLessThan($titlePos)
        ->and($titlePos)->toBeLessThan($sizesPos)
        ->and($sizesPos)->toBeLessThan($detailsPos)
        ->and($detailsPos)->toBeLessThan($mediaPos);
});

test('product details modal keeps image on the right and lists formatted on desktop', function () {
    $modal = file_get_contents(resource_path('js/Components/Public/ProductDetailsModal.vue'));
    $styles = file_get_contents(resource_path('css/plastex.css'));

    expect($modal)->toContain('px-product-modal-layout')
        ->and($modal)->toContain('px-product-modal-copy')
        ->and($modal)->toContain('px-product-modal-media')
        ->and($modal)->toContain('px-product-modal-details')
        ->and($modal)->toContain('v-if="sizes.length"')
        ->and($modal)->not->toContain('specificationsTitle')
        ->and($styles)->toContain('grid-template-areas: "copy media"')
        ->and($styles)->toContain('grid-area: copy')
        ->and($styles)->toContain('grid-area: media')
        ->and($styles)->toContain('direction: ltr')
        ->and($styles)->toContain('object-fit: contain')
        ->and($styles)->toContain('list-style-type: disc')
        ->and($styles)->toContain('list-style-type: decimal')
        ->and($styles)->toContain('backdrop-filter: blur')
        ->and($styles)->toContain('grid-template-columns: 1fr')
        ->and($styles)->toContain('"media"')
        ->and($styles)->toContain('"copy"');
});

test('products carousel opens the details modal directly from the product image click', function () {
    $carousel = file_get_contents(resource_path('js/Components/Public/ProductsCarousel.vue'));
    $styles = file_get_contents(resource_path('css/plastex.css'));
    $composable = file_get_contents(resource_path('js/Composables/useHorizontalCarousel.js'));

    expect($carousel)->toContain('<img')
        ->and($carousel)->toContain('@click.stop="openProduct(product)"')
        ->and(substr_count($carousel, '@click.stop="openProduct(product)"'))->toBeGreaterThanOrEqual(2)
        ->and($carousel)->toContain('selectedProduct.value = product')
        ->and($styles)->toContain('.px-product-carousel .px-product-media img')
        ->and($styles)->toContain('pointer-events: auto')
        ->and($composable)->toContain('dragThreshold')
        ->and($composable)->toContain('if (dragDistance <= dragThreshold)');
});

test('product inquire button scrolls to the contact form with the product name and does not open details', function () {
    $carousel = file_get_contents(resource_path('js/Components/Public/ProductsCarousel.vue'));
    $home = file_get_contents(resource_path('js/Pages/Public/HomePage.vue'));
    $ar = file_get_contents(resource_path('js/Plugins/I18n/Locales/ar.json'));
    $en = file_get_contents(resource_path('js/Plugins/I18n/Locales/en.json'));

    expect($carousel)->not->toContain("t('public.home.products.details')")
        ->and($carousel)->not->toContain("route('public.products.show'")
        ->and($carousel)->toContain("t('public.home.products.inquire')")
        ->and($carousel)->toContain('@click.stop="inquireAboutProduct(product)"')
        ->and($home)->toContain('@inquire="inquireAboutProduct"')
        ->and($home)->toContain("contactForm.message = t('public.home.products.inquireMessage'")
        ->and($home)->toContain('scrollToPublicContact')
        ->and($ar)->toContain('"inquire": "استفسر عن المنتج"')
        ->and($ar)->toContain('أرغب في الاستفسار عن المنتج: {name}')
        ->and($en)->toContain('"inquire": "Inquire about this product"')
        ->and($en)->toContain('I would like to inquire about this product: {name}');

    $inquireClick = strpos($carousel, '@click.stop="inquireAboutProduct(product)"');
    $openClick = strpos($carousel, '@click.stop="openProduct(product)"');

    expect($inquireClick)->toBeGreaterThan(0)
        ->and($openClick)->toBeGreaterThan(0)
        ->and($inquireClick)->not->toBe($openClick);
});
