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
