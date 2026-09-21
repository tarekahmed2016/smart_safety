<?php

use App\Models\CompanyInfo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('company info get does not create a database row', function () {
    expect(CompanyInfo::count())->toBe(0);

    $this->actingAs($this->admin)
        ->get(route('company-info.index'))
        ->assertOk();

    expect(CompanyInfo::count())->toBe(0);
});

test('guest cannot open company info', function () {
    $this->get(route('company-info.index'))
        ->assertRedirect(route('login'));
});

test('non admin cannot open company info', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('company-info.index'))
        ->assertRedirect(route('login'));
});

test('admin can view company info with empty defaults', function () {
    $this->actingAs($this->admin)
        ->get(route('company-info.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('CompanyInfo/CompanyInfoPage', false)
            ->where('companyInfo.name_ar', 'سمارت سيفتي')
            ->where('companyInfo.name_en', 'Smart Safety')
            ->where('companyInfo.email', ''));
});

test('admin can create company info on first update with bilingual names', function () {
    expect(CompanyInfo::count())->toBe(0);

    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة أكme',
            'name_en' => 'Acme Company',
            'phone' => '0123456789',
            'email' => 'hello@acme.test',
        ])
        ->assertRedirect();

    expect(CompanyInfo::count())->toBe(1)
        ->and(CompanyInfo::first()->name_ar)->toBe('شركة أكme')
        ->and(CompanyInfo::first()->name_en)->toBe('Acme Company');
});

test('admin can update both arabic and english company names', function () {
    CompanyInfo::create([
        'name_ar' => 'اسم قديم',
        'name_en' => 'Old Name',
        'phone' => '0123456789',
        'email' => 'hello@acme.test',
    ]);

    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'اسم محدث',
            'name_en' => 'Updated Name',
            'phone' => '0123456789',
            'email' => 'hello@acme.test',
        ])
        ->assertRedirect();

    expect(CompanyInfo::first()->name_ar)->toBe('اسم محدث')
        ->and(CompanyInfo::first()->name_en)->toBe('Updated Name');
});

test('company info rejects invalid email', function () {
    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'phone' => '0123456789',
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors('email');
});

test('company info accepts jpg logo', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'phone' => '0123456789',
            'email' => 'hello@acme.test',
            'logo' => UploadedFile::fake()->image('logo.jpg'),
        ])
        ->assertRedirect();

    expect(CompanyInfo::first()->attachment)->not->toBeNull();
});

test('company info accepts png logo', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])
        ->assertRedirect();
});

test('company info accepts webp logo', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'logo' => UploadedFile::fake()->create('logo.webp', 100, 'image/webp'),
        ])
        ->assertRedirect();
});

test('company info rejects svg logo', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'logo' => UploadedFile::fake()->create('logo.svg', 100, 'image/svg+xml'),
        ])
        ->assertSessionHasErrors('logo');
});

test('company info rejects non image logo', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'logo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('logo');
});

test('company info rejects oversized logo', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'logo' => UploadedFile::fake()->image('logo.jpg')->size(5000),
        ])
        ->assertSessionHasErrors('logo');
});

test('admin can save company name text color', function () {
    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'company_name_text_color' => '#E63946',
        ])
        ->assertRedirect();

    expect(CompanyInfo::first()->company_name_text_color)->toBe('#E63946');
});

test('company info rejects invalid company name text color', function () {
    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'company_name_text_color' => 'red',
        ])
        ->assertSessionHasErrors('company_name_text_color');
});

test('admin can save company name brand typography settings', function () {
    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'الصناعة الإبداعية',
            'name_en' => 'Creative Industry',
            'company_name_font_family_ar' => 'Cairo, sans-serif',
            'company_name_font_family_en' => 'Poppins, sans-serif',
            'company_name_font_size_ar' => 1.1,
            'company_name_font_size_en' => 0.8,
            'company_name_font_weight' => 700,
            'company_name_text_color' => '#123456',
        ])
        ->assertRedirect();

    $companyInfo = CompanyInfo::first();

    expect($companyInfo->company_name_font_family_ar)->toBe('Cairo, sans-serif')
        ->and($companyInfo->company_name_font_family_en)->toBe('Poppins, sans-serif')
        ->and((float) $companyInfo->company_name_font_size_ar)->toBe(1.1)
        ->and((float) $companyInfo->company_name_font_size_en)->toBe(0.8)
        ->and($companyInfo->company_name_font_weight)->toBe(700)
        ->and($companyInfo->company_name_text_color)->toBe('#123456');
});

test('company info rejects invalid company name font weight', function () {
    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'company_name_font_weight' => 350,
        ])
        ->assertSessionHasErrors('company_name_font_weight');
});

test('company info rejects invalid company name font size', function () {
    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'company_name_font_size_ar' => 5,
        ])
        ->assertSessionHasErrors('company_name_font_size_ar');
});

test('company info rejects invalid company name font family', function () {
    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'شركة',
            'name_en' => 'Company',
            'company_name_font_family_ar' => 'Cairo; drop table users;',
            'company_name_font_family_en' => 'Poppins<script>',
        ])
        ->assertSessionHasErrors([
            'company_name_font_family_ar',
            'company_name_font_family_en',
        ]);
});
