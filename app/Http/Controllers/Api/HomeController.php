<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DealSectionResource;
use App\Http\Resources\HeroSectionResource;
use App\Repository\CategoryRepository;
use App\Repository\DealSectionRepository;
use App\Models\StoreSetting;
use App\Repository\HeroSectionRepository;
use Illuminate\Http\JsonResponse;

/**
 * Public homepage content for the storefront. No authentication: this is what
 * every visitor sees before they have an account.
 */
class HomeController extends Controller
{
    private HeroSectionRepository $heroRepo;
    private DealSectionRepository $dealRepo;
    private CategoryRepository $categoryRepo;

    public function __construct(
        HeroSectionRepository $heroRepo,
        DealSectionRepository $dealRepo,
        CategoryRepository $categoryRepo
    ) {
        $this->heroRepo = $heroRepo;
        $this->dealRepo = $dealRepo;
        $this->categoryRepo = $categoryRepo;
    }

    public function hero(): JsonResponse
    {
        $hero = $this->heroRepo->activeHeroSection();

        // Null rather than 404: the storefront falls back to its built-in copy,
        // so having no active hero is not an error.
        return response()->json([
            'hero' => $hero ? new HeroSectionResource($hero) : null,
        ]);
    }

    public function deals(): JsonResponse
    {
        $section = $this->dealRepo->activeDealSection();

        return response()->json([
            'deals' => $section ? new DealSectionResource($section) : null,
        ]);
    }

    public function categories(): JsonResponse
    {
        return response()->json([
            'categories' => CategoryResource::collection($this->categoryRepo->getActiveCategories()),
        ]);
    }

    public function productTrust(): JsonResponse
    {
        return response()->json([
            'trust_badges' => StoreSetting::get(StoreSetting::PRODUCT_TRUST, []),
        ]);
    }
}
