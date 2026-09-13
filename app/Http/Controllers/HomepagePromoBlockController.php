<?php

namespace App\Http\Controllers;

use App\Enums\HomepagePromoType;
use App\Http\Requests\HomepagePromoBlockRequest;
use App\Http\Requests\HomepagePromoSectionSettingsRequest;
use App\Models\HomepagePromoBlock;
use App\Services\HomepagePromoBlockService;
use App\Support\HomepagePromoSectionMap;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomepagePromoBlockController extends Controller
{
    public function __construct(public HomepagePromoBlockService $homepagePromoBlockService) {}

    public function index()
    {
        return Inertia::render('HomepagePromos/HomepagePromosPage', [
            'sectionCards' => $this->homepagePromoBlockService->getAdminSectionCards(),
            'promoTypes' => collect(HomepagePromoType::cases())->map(fn (HomepagePromoType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
                'name' => $type->labelEn(),
                'supports_action' => $type->supportsAction(),
            ])->values(),
        ]);
    }

    public function updateSectionSettings(HomepagePromoSectionSettingsRequest $request, string $sectionKey)
    {
        if (! in_array($sectionKey, HomepagePromoSectionMap::sectionKeys(), true)) {
            abort(404);
        }

        $this->homepagePromoBlockService->updateSectionSettings(
            sectionKey: $sectionKey,
            companyData: $request->validated('company', []),
            sectionData: $request->validated('section', []),
        );

        return redirect()->back()->with('success', 'تم التحديث بنجاح');
    }

    public function getNextOrdering(Request $request)
    {
        $type = HomepagePromoType::tryFrom((string) $request->input('type', HomepagePromoType::FeatureHighlight->value))
            ?? HomepagePromoType::FeatureHighlight;

        return response()->json([
            'ordering' => nextOrdering(model: $this->homepagePromoBlockService->orderingQuery(type: $type)),
        ]);
    }

    public function store(HomepagePromoBlockRequest $request)
    {
        $this->homepagePromoBlockService->store(
            data: $request->safe()->except(['image', 'badge_image', 'remove_badge']),
            image: $request->file('image'),
            badgeImage: $request->file('badge_image'),
        );

        return redirect()->back()->with('success', 'تم الإضافة بنجاح');
    }

    public function update(HomepagePromoBlockRequest $request, HomepagePromoBlock $homepagePromo)
    {
        $this->homepagePromoBlockService->update(
            block: $homepagePromo,
            data: $request->safe()->except(['image', 'badge_image', 'remove_badge']),
            image: $request->file('image'),
            badgeImage: $request->file('badge_image'),
            removeBadge: (bool) $request->input('remove_badge', false),
        );

        return redirect()->back()->with('success', 'تم التحديث بنجاح');
    }

    public function destroy(HomepagePromoBlock $homepagePromo)
    {
        $this->homepagePromoBlockService->delete(block: $homepagePromo);

        return redirect()->back()->with('success', 'تم الحذف بنجاح');
    }
}
