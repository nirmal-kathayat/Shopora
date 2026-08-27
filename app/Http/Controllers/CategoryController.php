<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Repository\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $categoryRepo;

    public function __construct(CategoryRepository $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    public function storeCategory(CategoryRequest $request)
    {
        try {
            $data = $this->categoryRepo->store($request->validated());
            return response()->json(['message' => 'Category Added Successfully!', 'type' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something Went Wrong!', 'type' => 'error']);
        }
    }
}
