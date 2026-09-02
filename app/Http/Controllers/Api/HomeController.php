<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DealSectionResource;
use App\Http\Resources\HeroSectionResource;
use App\Repository\DealSectionRepository;
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

    public function __construct(HeroSectionRepository $heroRepo, DealSectionRepository $dealRepo)
    {
        $this->heroRepo = $heroRepo;
        $this->dealRepo = $dealRepo;
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
}
