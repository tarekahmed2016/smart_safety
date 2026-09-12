<?php

namespace App\Services;

use App\Models\CompanyGoal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompanyGoalService
{
    /**
     * @var list<string>
     */
    private const ACTIVITY_FIELDS = [
        'text_ar',
        'text_en',
        'ordering',
        'is_active',
    ];

    public function __construct(public ActivityLogService $activityLogService) {}

    public function getPaginatedGoals(
        string $search = '',
        string $sortBy = 'ordering',
        string $sortDir = 'asc',
        int $perPage = 15
    ): LengthAwarePaginator {
        $allowedSort = ['id', 'text_ar', 'text_en', 'ordering', 'created_at'];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'ordering';
        }

        return CompanyGoal::query()
            ->when($search, fn ($q) => $q->where(function ($query) use ($search) {
                $query->where('text_ar', 'like', "%{$search}%")
                    ->orWhere('text_en', 'like', "%{$search}%");
            }))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function orderingQuery(): Builder
    {
        return CompanyGoal::query();
    }

    /**
     * @return Collection<int, array{text_ar: string, text_en: string, ordering: int}>
     */
    public function getActiveGoalsForPublic(): Collection
    {
        return CompanyGoal::query()
            ->where('is_active', true)
            ->orderBy('ordering')
            ->get()
            ->map(fn (CompanyGoal $goal) => [
                'text_ar' => $goal->text_ar,
                'text_en' => $goal->text_en,
                'ordering' => $goal->ordering,
            ])
            ->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): CompanyGoal
    {
        return DB::transaction(function () use ($data) {
            $orderingQuery = $this->orderingQuery();

            if (! array_key_exists('ordering', $data) || $data['ordering'] === null || $data['ordering'] === '') {
                $data['ordering'] = nextOrdering(model: $orderingQuery);
            } else {
                $data['ordering'] = (int) $data['ordering'];
                shiftOrdering(model: $orderingQuery, from: $data['ordering'], direction: 'up');
            }

            $goal = CompanyGoal::create($data);

            $this->activityLogService->recordCreated(
                subject: $goal,
                allowedFields: self::ACTIVITY_FIELDS,
                subjectLabel: $this->subjectLabel($goal),
            );

            return $goal;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CompanyGoal $companyGoal, array $data): CompanyGoal
    {
        return DB::transaction(function () use ($companyGoal, $data) {
            $originalValues = $companyGoal->only(self::ACTIVITY_FIELDS);
            $orderingQuery = $this->orderingQuery();
            $oldOrdering = $companyGoal->ordering;
            $newOrdering = (int) ($data['ordering'] ?? $oldOrdering);

            if ($newOrdering !== $oldOrdering) {
                if ($newOrdering < $oldOrdering) {
                    shiftOrdering(model: $orderingQuery, from: $newOrdering, direction: 'up', to: $oldOrdering - 1, excludeId: $companyGoal->id);
                } else {
                    shiftOrdering(model: $orderingQuery, from: $oldOrdering, direction: 'down', to: $newOrdering, excludeId: $companyGoal->id);
                }
            }

            $companyGoal->update($data);

            $this->activityLogService->recordChanges(
                subject: $companyGoal,
                originalValues: $originalValues,
                allowedFields: self::ACTIVITY_FIELDS,
                subjectLabel: $this->subjectLabel($companyGoal),
            );

            return $companyGoal;
        });
    }

    public function delete(CompanyGoal $companyGoal): void
    {
        DB::transaction(function () use ($companyGoal) {
            $this->activityLogService->recordDeleted(
                subject: $companyGoal,
                allowedFields: self::ACTIVITY_FIELDS,
                subjectLabel: $this->subjectLabel($companyGoal),
            );

            $ordering = $companyGoal->ordering;
            $companyGoal->delete();

            shiftOrdering(model: $this->orderingQuery(), from: $ordering, direction: 'down');
        });
    }

    private function subjectLabel(CompanyGoal $companyGoal): string
    {
        return $companyGoal->text_ar ?: $companyGoal->text_en ?: 'Company Goal';
    }
}
