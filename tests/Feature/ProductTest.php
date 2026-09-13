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

test('updating a product without details keeps existing catalog fields', function () {
    $product = Product::factory()->create([
        'name_en' => 'Legacy Product',
        'slug' => 'legacy-product',
        'ordering' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->put(route('products.update', $product), [
            'name_ar' => $product->name_ar,
            'name_en' => 'Legacy Product',
            'slug' => 'legacy-product',
            'description_ar' => $product->description_ar,
            'description_en' => $product->description_en,
            'ordering' => 0,
            'is_active' => true,
        ])
        ->assertRedirect();

    expect($product->fresh()->details_ar)->toBeNull()
        ->and($product->fresh()->sizes)->toBeNull();
});

test('admin can save optional product details sizes and specifications', function () {
    $this->actingAs($this->admin)
        ->post(route('products.store'), validProductPayload([
            'details_ar' => '<p>تفاصيل عربية</p>',
            'details_en' => '<p>English details</p>',
            'sizes' => [
                ['value' => '5', 'unit' => 'mm'],
                ['value' => '9', 'unit' => 'mm'],
            ],
            'specifications_ar' => ['خفيف الوزن', 'مرونة عالية'],
            'specifications_en' => ['Lightweight', 'High flexibility'],
        ]))
        ->assertRedirect();

    $product = Product::where('slug', 'plastic-container')->first();

    expect($product->details_en)->toBe('<p>English details</p>')
        ->and($product->details_ar)->toBe('<p>تفاصيل عربية</p>')
        ->and($product->sizes)->toBe([
            ['value' => '5', 'unit' => 'mm'],
            ['value' => '9', 'unit' => 'mm'],
        ])
        ->and($product->specifications_ar)->toBe(['خفيف الوزن', 'مرونة عالية'])
        ->and($product->specifications_en)->toBe(['Lightweight', 'High flexibility']);
});

test('product details keep dashed dotted and double table borders and indent after reopen', function () {
    $details = <<<'HTML'
<figure class="table">
<table style="border-style:dashed;border-width:1px;border-color:#000000;">
<tbody>
<tr>
<td style="border-style:dotted;border-width:1px;border-color:#111111;">Dotted</td>
<td style="border-style:double;border-width:2px;border-color:#222222;">Double</td>
</tr>
</tbody>
</table>
</figure>
<p style="margin-left:40px;">Indented</p>
HTML;

    $this->actingAs($this->admin)
        ->post(route('products.store'), validProductPayload([
            'details_en' => $details,
            'details_ar' => $details,
        ]))
        ->assertRedirect();

    $product = Product::where('slug', 'plastic-container')->first();

    expect($product->details_en)
        ->toContain('border-style:dashed')
        ->toContain('border-style:dotted')
        ->toContain('border-style:double')
        ->toContain('margin-left:40px');

    $this->actingAs($this->admin)
        ->put(route('products.update', $product), [
            'name_ar' => $product->name_ar,
            'name_en' => $product->name_en,
            'slug' => $product->slug,
            'description_ar' => $product->description_ar,
            'description_en' => $product->description_en,
            'details_ar' => $product->details_ar,
            'details_en' => $product->details_en,
            'ordering' => $product->ordering,
            'is_active' => true,
        ])
        ->assertRedirect();

    expect($product->fresh()->details_en)
        ->toContain('border-style:dashed')
        ->toContain('border-style:dotted')
        ->toContain('border-style:double')
        ->toContain('margin-left:40px');
});

test('product rich text editor keeps table property tools and block indent', function () {
    $config = file_get_contents(resource_path('js/Composables/useRichTextEditorConfig.js'));
    $styles = file_get_contents(resource_path('css/app.css'));

    expect($config)->toContain('IndentBlock')
        ->and($config)->toContain("'indent'")
        ->and($config)->toContain("'outdent'")
        ->and($config)->toContain('indentBlock')
        ->and($config)->toContain("'tableColumn'")
        ->and($config)->toContain("'tableRow'")
        ->and($config)->toContain("'mergeTableCells'")
        ->and($config)->toContain("'tableProperties'")
        ->and($config)->toContain("'tableCellProperties'")
        ->and($config)->toContain("borderStyle: 'none'")
        ->and($styles)->toContain('.ck-content table.table:not(.layout-table)')
        ->and($styles)->toContain('border-style: none');
});


test('creating a product does not require details sizes or specifications', function () {
    $this->actingAs($this->admin)
        ->post(route('products.store'), validProductPayload())
        ->assertRedirect();

    $product = Product::where('slug', 'plastic-container')->first();

    expect($product)->not->toBeNull()
        ->and($product->details_ar)->toBeNull()
        ->and($product->sizes)->toBeNull()
        ->and($product->specifications_en)->toBeNull();
});

test('product form shows the image at the top then sizes before details and hides specifications', function () {
    $form = file_get_contents(resource_path('js/Components/Features/Products/ProductFormModal.vue'));

    $titlePos = strpos($form, 'product-form-modal-title');
    $imagesPos = strpos($form, "t('products.form.imagesSectionTitle')");
    $basicPos = strpos($form, "t('products.form.basicSectionTitle')");
    $sizesPos = strpos($form, "t('products.form.sizesLabel')");
    $detailsPos = strpos($form, "t('products.form.detailsSectionTitle')");

    expect($titlePos)->toBeGreaterThan(0)
        ->and($imagesPos)->toBeGreaterThan($titlePos)
        ->and($basicPos)->toBeGreaterThan($imagesPos)
        ->and($sizesPos)->toBeGreaterThan($basicPos)
        ->and($detailsPos)->toBeGreaterThan($sizesPos)
        ->and(substr_count($form, "t('products.form.imagesSectionTitle')"))->toBe(1)
        ->and($form)->toContain('object-contain')
        ->and($form)->not->toContain('object-cover')
        ->and($form)->toContain('replaceFile')
        ->and($form)->toContain('clearSelectedImage')
        ->and($form)->toContain('addSize')
        ->and($form)->toContain('removeSize')
        ->and($form)->toContain('moveSize')
        ->and($form)->not->toContain('specifications_ar')
        ->and($form)->not->toContain('specifications_en')
        ->and($form)->not->toContain('addSpecification')
        ->and($form)->not->toContain('specificationsArLabel')
        ->and($form)->not->toContain('specificationsEnLabel');
});

test('updating a product without specifications keeps stored specification values', function () {
    $product = Product::factory()->create([
        'name_en' => 'Stored Specs Product',
        'slug' => 'stored-specs-product',
        'ordering' => 0,
        'is_active' => true,
        'specifications_ar' => ['وزن خفيف'],
        'specifications_en' => ['Lightweight'],
    ]);

    $this->actingAs($this->admin)
        ->put(route('products.update', $product), [
            'name_ar' => $product->name_ar,
            'name_en' => 'Stored Specs Product',
            'slug' => 'stored-specs-product',
            'description_ar' => $product->description_ar,
            'description_en' => $product->description_en,
            'ordering' => 0,
            'is_active' => true,
        ])
        ->assertRedirect();

    expect($product->fresh()->specifications_ar)->toBe(['وزن خفيف'])
        ->and($product->fresh()->specifications_en)->toBe(['Lightweight']);
});

test('admin can delete a product', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('products.destroy', $product))
        ->assertRedirect();

    expect(Product::count())->toBe(0);
});
