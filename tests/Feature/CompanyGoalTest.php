<?php

use App\Enums\ActivityLogs\Event;
use App\Models\ActivityLog;
use App\Models\CompanyGoal;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    CompanyGoal::query()->delete();
});

function validCompanyGoalPayload(array $overrides = []): array
{
    return array_merge([
        'text_ar' => 'نساهم في زرع الثقة في المنتج العماني ومنحه المجال للمنافسة في الأسواق العالمية',
        'text_en' => 'Building trust in Omani products and helping them compete in global markets.',
        'ordering' => 1,
        'is_active' => true,
    ], $overrides);
}

test('guest cannot open company goals index', function () {
    $this->get(route('company-goals.index'))
        ->assertRedirect(route('login'));
});

test('non admin cannot open company goals index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('company-goals.index'))
        ->assertRedirect(route('login'));
});

test('admin can view company goals index', function () {
    CompanyGoal::factory()->create([
        'text_ar' => 'هدف تجريبي',
        'text_en' => 'Sample goal',
    ]);

    $this->actingAs($this->admin)
        ->get(route('company-goals.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('CompanyGoals/CompanyGoalsPage', false)
            ->has('companyGoals.data', 1)
            ->where('companyGoals.data.0.text_en', 'Sample goal'));
});

test('admin can create a bilingual company goal', function () {
    $this->actingAs($this->admin)
        ->post(route('company-goals.store'), validCompanyGoalPayload())
        ->assertRedirect();

    $goal = CompanyGoal::query()->first();

    expect($goal)->not->toBeNull()
        ->and($goal->text_ar)->toBe('نساهم في زرع الثقة في المنتج العماني ومنحه المجال للمنافسة في الأسواق العالمية')
        ->and($goal->text_en)->toBe('Building trust in Omani products and helping them compete in global markets.')
        ->and($goal->ordering)->toBe(1)
        ->and($goal->is_active)->toBeTrue();

    expect(ActivityLog::query()->where('event', Event::Created)->exists())->toBeTrue();
});

test('creating a company goal requires arabic and english text', function () {
    $this->actingAs($this->admin)
        ->post(route('company-goals.store'), validCompanyGoalPayload(['text_ar' => '']))
        ->assertSessionHasErrors('text_ar');

    $this->actingAs($this->admin)
        ->post(route('company-goals.store'), validCompanyGoalPayload(['text_en' => '']))
        ->assertSessionHasErrors('text_en');

    expect(CompanyGoal::count())->toBe(0);
});

test('creating a company goal rejects text longer than 1000 characters', function () {
    $this->actingAs($this->admin)
        ->post(route('company-goals.store'), validCompanyGoalPayload([
            'text_en' => str_repeat('a', 1001),
        ]))
        ->assertSessionHasErrors('text_en');
});

test('admin can update a company goal', function () {
    $goal = CompanyGoal::factory()->create([
        'text_en' => 'Old goal',
        'ordering' => 1,
    ]);

    $this->actingAs($this->admin)
        ->put(route('company-goals.update', $goal), validCompanyGoalPayload([
            'text_en' => 'Updated goal',
            'ordering' => 3,
            'is_active' => false,
        ]))
        ->assertRedirect();

    expect($goal->fresh()->text_en)->toBe('Updated goal')
        ->and($goal->fresh()->ordering)->toBe(3)
        ->and($goal->fresh()->is_active)->toBeFalse();
});

test('admin can delete a company goal', function () {
    $goal = CompanyGoal::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('company-goals.destroy', $goal))
        ->assertRedirect();

    expect(CompanyGoal::query()->find($goal->id))->toBeNull();
});
