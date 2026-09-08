<?php

namespace App\Http\Controllers;

use App\Http\Requests\NavigationUpdateRequest;
use App\Services\NavigationService;
use Inertia\Inertia;
use Inertia\Response;

class NavigationController extends Controller
{
    public function __construct(public NavigationService $navigationService) {}

    public function index(): Response
    {
        return Inertia::render('Navigation/NavigationPage', [
            'navigationItems' => $this->navigationService->getAdminItems(),
        ]);
    }

    public function update(NavigationUpdateRequest $request)
    {
        $this->navigationService->syncNavigation($request->validated('items'));

        return redirect()
            ->route('navigation.index')
            ->with('success', 'تم التحديث بنجاح');
    }
}
