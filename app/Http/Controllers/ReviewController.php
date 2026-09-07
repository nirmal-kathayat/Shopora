<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

/**
 * Admin view of customer product reviews - read and moderate (delete spam or
 * abuse). Reviews are written by customers on the storefront.
 */
class ReviewController extends Controller
{
    /**
     * The reviews screen, and the JSON behind its table.
     *
     * A TableHelper, so the envelope is { success, data, total }. Reviews go
     * live the moment they are written, so this list is where the shop reads
     * what has been said about it - newest first.
     */
    public function index(Request $request)
    {
        try {
            if (! $request->ajax()) {
                return view('review.index');
            }

            $query = ProductReview::query()
                ->leftJoin('inventory_items', 'inventory_items.id', '=', 'product_reviews.inventory_item_id')
                ->leftJoin('customers', 'customers.id', '=', 'product_reviews.customer_id')
                ->select([
                    'product_reviews.id',
                    'product_reviews.rating',
                    'product_reviews.title',
                    'product_reviews.body',
                    'product_reviews.created_at',
                    'inventory_items.title as product_title',
                    'customers.name as customer_name',
                ]);

            foreach (['product_title' => 'inventory_items.title', 'customer_name' => 'customers.name'] as $key => $column) {
                if ($value = trim((string) $request->input($key))) {
                    $query->where($column, 'like', '%' . $value . '%');
                }
            }

            // One box over the row: the product, who wrote it, or what it says.
            if ($search = trim((string) $request->input('search'))) {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('inventory_items.title', 'like', $like)
                        ->orWhere('customers.name', 'like', $like)
                        ->orWhere('product_reviews.title', 'like', $like)
                        ->orWhere('product_reviews.body', 'like', $like);
                });
            }

            $sortable = [
                'product_title' => 'inventory_items.title',
                'customer_name' => 'customers.name',
                'rating' => 'product_reviews.rating',
                'created_at' => 'product_reviews.created_at',
            ];
            $column = $sortable[$request->input('sort_field')] ?? null;

            $column
                ? $query->orderBy($column, strtolower((string) $request->input('sort_direction')) === 'asc' ? 'asc' : 'desc')
                : $query->orderByDesc('product_reviews.created_at');

            $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
            $page = max((int) $request->input('page', 1), 1);
            // Counted through a subquery. getCountForPagination() on this
            // joined Eloquent query hands back an object, not a number, and
            // the pager reads "of [object Object] entries".
            $total = DB::table(DB::raw('(' . $query->toSql() . ') as counted'))
                ->mergeBindings($query->getQuery())
                ->count();

            return response()->json([
                'success' => true,
                'data' => $query->skip(($page - 1) * $perPage)->take($perPage)->get()
                    ->map(fn ($r) => [
                        'id' => $r->id,
                        'rating' => (int) $r->rating,
                        'title' => $r->title,
                        'body' => $r->body,
                        'product_title' => $r->product_title,
                        'customer_name' => $r->customer_name,
                        'created_at' => $r->created_at?->format('d M Y, g:i a'),
                    ]),
                'total' => $total,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Review list failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Could not load',
                    'message' => 'The review list could not be loaded.',
                ]);
            }

            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function delete($id)
    {
        try {
            ProductReview::whereKey($id)->delete();

            return redirect()->back()->with(['message' => 'Review deleted successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
}
