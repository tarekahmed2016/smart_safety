<?php

use App\Enums\ActivityLogs\Event;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

function validProductPayload(array $overrides = []): array
{
    return array_merge([
        'name_ar' => 'عبوة بلاستيكية',
        'name_en' => 'Plastic Container',
        'slug' => 'plastic-container',
        'description_ar' => 'وصف المنتج',
        'description_en' => 'Product description',
        'ordering' => 0,
        'is_active' => true,
        'show_on_homepage' => true,
        'image' => UploadedFile::fake()->image('product.jpg'),
    ], $overrides);
}

test('guest cannot open products index', function () {
    $this->get(route('products.index'))
        ->assertRedirect(route('login'));
});

test('non admin cannot open products index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertRedirect(route('login'));
});

test('admin can view products index', function () {
    Product::factory()->create([
        'name_ar' => 'غطاء',
        'name_en' => 'Cap',
        'slug' => 'cap',
    ]);

    $this->actingAs($this->admin)
        ->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Products/ProductsPage', false)
            ->has('products.data', 1)
            ->where('products.data.0.name_ar', 'غطاء')
            ->where('products.data.0.name_en', 'Cap'));
});

test('admin can create a product with arabic and english content', function () {
    $this->actingAs($this->admin)
        ->post(route('products.store'), validProductPayload())
        ->assertRedirect();

    $product = Product::where('slug', 'plastic-container')->first();

    expect($product)->not->toBeNull()
        ->and($product->name_ar)->toBe('عبوة بلاستيكية')
        ->and($product->name_en)->toBe('Plastic Container')
        ->and($product->is_active)->toBeTrue()
        ->and($product->attachment)->not->toBeNull();

    Storage::disk('public')->assertExists($product->attachment->path);
    expect(ActivityLog::where('event', Event::Created)->where('subject_type', Product::class)->exists())->toBeTrue();
});

test('creating a product generates a slug when omitted', function () {
    $payload = validProductPayload(['name_en' => 'Custom Bottle']);
    unset($payload['slug']);

    $this->actingAs($this->admin)
        ->post(route('products.store'), $payload)
        ->assertRedirect();

    expect(Product::where('slug', 'custom-bottle')->exists())->toBeTrue();
});

test('creating a product requires name_ar and name_en', function () {
    $this->actingAs($this->admin)
        ->post(route('products.store'), validProductPayload(['name_ar' => '']))
        ->assertSessionHasErrors('name_ar');

    $this->actingAs($this->admin)
        ->post(route('products.store'), validProductPayload(['name_en' => '']))
        ->assertSessionHasErrors('name_en');
});

test('creating a product requires an image', function () {
    $payload = validProductPayload();
    unset($payload['image']);

    $this->actingAs($this->admin)
        ->post(route('products.store'), $payload)
        ->assertSessionHasErrors('image');
});

test('admin can update a product', function () {
    $product = Product::factory()->create([
        'name_ar' => 'منتج قديم',
        'name_en' => 'Old Product',
        'slug' => 'old-product',
        'ordering' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->put(route('products.update', $product), [
            'name_ar' => 'منتج محدث',
            'name_en' => 'Updated Product',
            'slug' => 'updated-product',
            'description_ar' => $product->description_ar,
            'description_en' => $product->description_en,
            'ordering' => 0,
            'is_active' => false,
        ])
        ->assertRedirect();

    expect($product->fresh()->name_en)->toBe('Updated Product')
        ->and($product->fresh()->slug)->toBe('updated-product')
        ->and($product->fresh()->is_active)->toBeFalse();
});

test('admin can delete a product', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('products.destroy', $product))
        ->assertRedirect();

    expect(Product::count())->toBe(0);
});
