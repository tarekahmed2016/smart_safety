<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyGoalRequest;
use App\Models\CompanyGoal;
use App\Services\CompanyGoalService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyGoalController extends Controller
{
    public function __construct(public CompanyGoalService $companyGoalService) {}

    public function index(Request $request)
    {
        $search = (string) $request->input('search', '');
        $sortBy = in_array($request->input('sort_column'), ['id', 'text_ar', 'text_en', 'ordering', 'created_at'], true)
            ? $request->input('sort_column')
            : 'ordering';
        $sortDir = $request->input('sort_direction', 'asc') === 'desc' ? 'desc' : 'asc';

        return Inertia::render('CompanyGoals/CompanyGoalsPage', [
            'companyGoals' => $this->companyGoalService->getPaginatedGoals(
                search: $search,
                sortBy: $sortBy,
                sortDir: $sortDir,
            ),
            'filters' => [
                'search' => $search,
                'sort_column' => $sortBy,
                'sort_direction' => $sortDir,
            ],
        ]);
    }

    public function getNextOrdering()
    {
        return response()->json([
            'ordering' => nextOrdering(model: $this->companyGoalService->orderingQuery()),
        ]);
    }

    public function store(CompanyGoalRequest $request)
    {
        $this->companyGoalService->store($request->validated());

        return redirect()->back()->with('success', 'تم الإضافة بنجاح');
    }

    public function update(CompanyGoalRequest $request, CompanyGoal $companyGoal)
    {
        $this->companyGoalService->update(
            companyGoal: $companyGoal,
            data: $request->validated(),
        );

        return redirect()->back()->with('success', 'تم التحديث بنجاح');
    }

    public function destroy(CompanyGoal $companyGoal)
    {
        $this->companyGoalService->delete(companyGoal: $companyGoal);

        return redirect()->back()->with('success', 'تم الحذف بنجاح');
    }
}
