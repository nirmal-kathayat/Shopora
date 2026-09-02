<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeroSectionResource;
use App\Repository\HeroSectionRepository;
use Illuminate\Http\JsonResponse;

/**
 * Public homepage content for the storefront. No authentication: this is what
 * every visitor sees before they have an account.
 */
class HomeController extends Controller
{
    private HeroSectionRepository $heroRepo;

    public function __construct(HeroSectionRepository $heroRepo)
    {
        $this->heroRepo = $heroRepo;
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
}
