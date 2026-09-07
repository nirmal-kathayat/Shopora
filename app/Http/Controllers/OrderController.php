<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Repository\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class OrderController extends Controller
{
    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    /**
     * The orders screen, and the JSON behind its table.
     *
     * A TableHelper, so the envelope is { success, data, total }. The date
     * range is sent as start_date / end_date - those names are fixed in the
     * component - and works on when the order was placed.
     */
    public function index(Request $request)
    {
        try {
            if (! $request->ajax()) {
                return view('order.index', ['statuses' => array_keys(Sales::FLOW)]);
            }

            $query = $this->orderRepo->getOrders($request->input('status'), [
                'payment_method' => $request->input('payment_method'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'search' => $request->input('search'),
                'sort_field' => $request->input('sort_field'),
                'sort_direction' => $request->input('sort_direction'),
            ]);

            $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
            $page = max((int) $request->input('page', 1), 1);

            // Counted through a subquery: this list carries selectSub columns,
            // and getCountForPagination() does not survive them.
            $total = DB::table(DB::raw('(' . $query->toSql() . ') as counted'))
                ->mergeBindings($query->getQuery())
                ->count();

            $rows = $query->skip(($page - 1) * $perPage)->take($perPage)->get()
                ->map(fn ($order) => [
                    'id' => $order->id,
                    'code' => $order->code,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone ?: $order->delivery_phone,
                    'item_count' => (int) $order->item_count,
                    // What the customer actually owes: the lines plus delivery.
                    'total' => (float) $order->items_total + (float) $order->delivery_fee,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                    'created_at' => $order->created_at?->format('d M Y, g:i a'),
                ]);

            return response()->json([
                'success' => true,
                'data' => $rows,
                'total' => $total,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Order list failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Could not load',
                    'message' => 'The order list could not be loaded.',
                ]);
            }

            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    /** One order, for the modal: what was bought and where it goes. */
    public function show($id)
    {
        try {
            $order = $this->orderRepo->find($id);

            $items = $order->products->map(fn ($line) => [
                'name' => $line->inventoryItem?->title ?? 'Product',
                // The modal shows the packshot, so an order reads like the
                // storefront basket it came from rather than a list of words.
                'image' => inventoryItemImageUrl($line->inventoryItem?->image),
                'qty' => (int) $line->qty,
                'price_per_unit' => (float) $line->price_per_unit,
                'line_total' => (float) $line->price_per_unit * (int) $line->qty,
            ]);

            return response()->json([
                'code' => $order->code,
                'status' => $order->status,
                'placed_at' => $order->created_at?->format('d M Y, g:i a'),
                'customer' => [
                    'name' => $order->customer?->name,
                    'phone' => $order->customer?->ph_number,
                    'email' => $order->customer?->email,
                ],
                'delivery' => [
                    'recipient' => $order->delivery_recipient,
                    'phone' => $order->delivery_phone,
                    'address' => $order->delivery_address,
                    'landmark' => $order->delivery_landmark,
                ],
                'items' => $items,
                'subtotal' => $items->sum('line_total'),
                'delivery_fee' => (float) $order->delivery_fee,
                'total' => $items->sum('line_total') + (float) $order->delivery_fee,
                // Only the steps this order can actually move to.
                'next_statuses' => Sales::FLOW[$order->status] ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong!'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $order = $this->orderRepo->find($id);

            $allowed = Sales::FLOW[$order->status] ?? [];
            $data = $request->validate([
                'status' => ['required', 'string', 'in:' . implode(',', $allowed)],
            ]);

            $this->orderRepo->updateStatus((int) $id, $data['status']);

            return response()->json(['message' => 'Order marked as ' . $data['status'] . '.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'That is not a step this order can take.'], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong!'], 500);
        }
    }
}
