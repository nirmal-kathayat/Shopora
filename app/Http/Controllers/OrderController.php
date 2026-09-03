<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Repository\OrderRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class OrderController extends Controller
{
    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $orders = $this->orderRepo->getOrders($request->input('status'));

                return DataTables::of($orders)
                    ->addIndexColumn()
                    ->addColumn('code', fn ($order) => $order->code)
                    ->addColumn('total', fn ($order) => (float) $order->items_total + (float) $order->delivery_fee)
                    ->editColumn('created_at', fn ($order) => $order->created_at?->format('d M Y, g:i a'))
                    ->rawColumns([])
                    ->make(true);
            }

            return view('order.index', ['statuses' => array_keys(Sales::FLOW)]);
        } catch (\Exception $e) {
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
