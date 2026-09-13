<?php

namespace App\Http\Controllers;

use App\Http\Requests\HomepageSectionsUpdateRequest;
use App\Services\HomepageSectionService;
use App\Support\HomepageSectionDefaults;
use App\Support\HomepageSectionNavigation;
use Inertia\Inertia;
use Inertia\Response;

class HomepageSectionController extends Controller
{
    public function __construct(public HomepageSectionService $homepageSectionService) {}

    public function index(): Response
    {
        return Inertia::render('HomepageSections/HomepageSectionsPage', [
            'homepageSections' => $this->homepageSectionService->getAllSections()
                ->map(fn ($section) => [
                    ...$section->toArray(),
                    'is_navigable' => HomepageSectionNavigation::isNavigable($section->key),
                    'shows_headline' => HomepageSectionDefaults::usesHeadline($section->key),
                    'shows_highlight' => HomepageSectionDefaults::usesHighlight($section->key),
                    'shows_subtitle' => HomepageSectionDefaults::usesSubtitle($section->key),
                    'shows_max_items' => HomepageSectionDefaults::usesMaxItems($section->key),
                ]),
            'navigableSectionKeys' => HomepageSectionNavigation::navigableKeys(),
        ]);
    }

    public function update(HomepageSectionsUpdateRequest $request)
    {
        $this->homepageSectionService->syncSections($request->validated('sections'));

        return redirect()
            ->route('homepage-sections.index')
            ->with('success', 'تم التحديث بنجاح');
    }
}
