<?php

namespace App\Http\Controllers;

use App\Services\PublicHomeService;
use Inertia\Inertia;
use Inertia\Response;

class PublicProductController extends Controller
{
    public function __construct(public PublicHomeService $publicHomeService) {}

    public function index(): Response
    {
        return Inertia::render('Public/ProductsIndex', [
            'companyInfo' => $this->publicHomeService->getPublicCompanyInfo(),
            'products' => $this->publicHomeService->getActiveProducts(),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = $this->publicHomeService->getPublicProductBySlug($slug);

        abort_if($product === null, 404);

        return Inertia::render('Public/ProductShow', [
            'companyInfo' => $this->publicHomeService->getPublicCompanyInfo(),
            'product' => $product,
        ]);
    }
}
