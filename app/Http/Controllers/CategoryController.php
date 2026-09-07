<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Repository\CategoryRepository;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller
{
    private CategoryRepository $categoryRepo;

    public function __construct(CategoryRepository $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    /**
     * The screen, and the JSON behind its table.
     *
     * A TableHelper, so the envelope is { success, data, total }. There is no
     * filter bar above this one - a shop has a handful of aisles, and the
     * header row and the search box are enough to find one.
     */
    public function index()
    {
        try {
            if (! request()->ajax()) {
                return view('category.index');
            }

            $perPage = min(max((int) request()->input('per_page', 10), 1), 100);
            $page = max((int) request()->input('page', 1), 1);

            $rows = $this->categoryRepo->getCategoriesForListing([
                'title' => request()->input('title'),
                'slug' => request()->input('slug'),
                'search' => request()->input('search'),
                'sort_field' => request()->input('sort_field'),
                'sort_direction' => request()->input('sort_direction'),
            ])->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => collect($rows->items())->map(fn ($category) => [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => $category->slug,
                    'status' => (int) $category->status,
                    'sort_order' => (int) $category->sort_order,
                    'inventory_items_count' => (int) $category->inventory_items_count,
                    'image_url' => $category->image ? inventoryItemImageUrl($category->image) : null,
                    'updated_at' => $category->updated_at?->format('d M Y, g:i a'),
                ]),
                'total' => $rows->total(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Category list failed: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Could not load',
                    'message' => 'The category list could not be loaded.',
                ]);
            }

            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function create()
    {
        try {
            return view('category.form', ['icons' => Category::ICONS]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    /**
     * Also serves the inventory item modal, which posts a bare title over AJAX
     * and expects a JSON row back. The full category form posts a normal
     * request and expects a redirect.
     */
    public function storeCategory(CategoryRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image'] = $this->categoryRepo->storeImageFile($request->file('image'));
            }

            $category = $this->categoryRepo->store($data);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'Category added successfully!',
                    'data' => ['id' => $category->id, 'title' => $category->title],
                ]);
            }

            return redirect()->route('admin.category')
                ->with(['message' => 'Category added successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['type' => 'error', 'message' => 'Something went wrong!'], 500);
            }

            return redirect()->back()->withInput()
                ->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function edit($id)
    {
        try {
            $category = $this->categoryRepo->find($id);

            return view('category.form', ['icons' => Category::ICONS, 'category' => $category]);
        } catch (\Exception $e) {
            return redirect()->route('admin.category')
                ->with(['message' => 'That category no longer exists.', 'type' => 'error']);
        }
    }

    public function update(CategoryRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $existing = $this->categoryRepo->find($id);

            if ($request->hasFile('image')) {
                $this->categoryRepo->deleteImageFile($existing->image);
                $data['image'] = $this->categoryRepo->storeImageFile($request->file('image'));
            } elseif ($request->boolean('remove_image')) {
                $this->categoryRepo->deleteImageFile($existing->image);
                $data['image'] = null;
            } else {
                unset($data['image']);
            }

            $this->categoryRepo->update($data, (int) $id);

            return redirect()->route('admin.category')
                ->with(['message' => 'Category updated successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function delete($id)
    {
        try {
            // Items would be deleted with the category by the FK cascade, so
            // refuse rather than quietly take inventory down with it.
            if ($this->categoryRepo->itemCount((int) $id) > 0) {
                return redirect()->back()->with([
                    'message' => 'This category still has inventory items. Move or remove them first.',
                    'type' => 'error',
                ]);
            }

            $this->categoryRepo->delete((int) $id);

            return redirect()->back()
                ->with(['message' => 'Category deleted successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
}
