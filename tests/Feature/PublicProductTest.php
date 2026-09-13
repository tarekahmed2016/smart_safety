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

test('products carousel opens a details modal from the product image', function () {
    $carousel = file_get_contents(resource_path('js/Components/Public/ProductsCarousel.vue'));
    $modal = file_get_contents(resource_path('js/Components/Public/ProductDetailsModal.vue'));

    expect($carousel)->toContain('openProductDetails')
        ->and($carousel)->toContain('ProductDetailsModal')
        ->and($carousel)->toContain('@click="openProductDetails(product)"')
        ->and($carousel)->toContain('px-product-media-trigger')
        ->and($modal)->toContain('@click.self="close"')
        ->and($modal)->toContain('useDialogAccessibility')
        ->and($modal)->toContain('px-product-modal-close')
        ->and($modal)->toContain('productName')
        ->and($modal)->toContain('product.image')
        ->and($modal)->toContain('hasDetails')
        ->and($modal)->toContain('px-product-size-pill')
        ->and($modal)->toContain('specifications')
        ->and($modal)->toContain("locale.value === 'ar'")
        ->and($modal)->not->toContain('action_text')
        ->and($modal)->not->toContain('action_url')
        ->and($modal)->not->toContain('cta_url');
});
