<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use Yajra\DataTables\DataTables;

/**
 * Admin view of customer product reviews - read and moderate (delete spam or
 * abuse). Reviews are written by customers on the storefront.
 */
class ReviewController extends Controller
{
    public function index()
    {
        try {
            if (request()->ajax()) {
                $reviews = ProductReview::query()
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
                    ])
                    ->orderByDesc('product_reviews.created_at');

                return DataTables::of($reviews)
                    ->addIndexColumn()
                    ->editColumn('created_at', fn ($r) => $r->created_at?->format('d M Y, g:i a'))
                    ->rawColumns([])
                    ->make(true);
            }

            return view('review.index');
        } catch (\Exception $e) {
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
