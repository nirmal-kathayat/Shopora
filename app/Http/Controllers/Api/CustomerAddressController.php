<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CustomerAddressRequest;
use App\Http\Resources\CustomerAddressResource;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The signed-in customer's delivery addresses. Every lookup goes through the
 * customer's own relation, so an id belonging to someone else is a 404 rather
 * than someone else's address.
 */
class CustomerAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->listResponse($request->user());
    }

    public function store(CustomerAddressRequest $request): JsonResponse
    {
        $customer = $request->user();

        DB::transaction(function () use ($customer, $request) {
            $address = $customer->addresses()->create($request->validated());

            // The first address a customer saves is their default, whether or
            // not they ticked the box.
            if ($address->is_default || $customer->addresses()->count() === 1) {
                $this->makeDefault($customer, $address);
            }
        });

        return $this->listResponse($customer->refresh(), 201);
    }

    public function update(CustomerAddressRequest $request, int $id): JsonResponse
    {
        $customer = $request->user();
        $address = $customer->addresses()->findOrFail($id);

        DB::transaction(function () use ($customer, $address, $request) {
            $address->update($request->validated());

            if ($address->is_default) {
                $this->makeDefault($customer, $address);
            }
        });

        return $this->listResponse($customer);
    }

    /** Promote one address to the default without touching its other fields. */
    public function setDefault(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();
        $address = $customer->addresses()->findOrFail($id);

        DB::transaction(fn () => $this->makeDefault($customer, $address));

        return $this->listResponse($customer);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();
        $address = $customer->addresses()->findOrFail($id);

        DB::transaction(function () use ($customer, $address) {
            $wasDefault = $address->is_default;
            $address->delete();

            // Never leave the customer without a default while they still have
            // addresses - the oldest remaining one takes over.
            if ($wasDefault) {
                $next = $customer->addresses()->oldest('id')->first();
                $next ? $this->makeDefault($customer, $next) : $customer->forceFill(['address' => null])->save();
            }
        });

        return $this->listResponse($customer);
    }

    /**
     * One default per customer, and customers.address follows it so the
     * profile card, the admin list and the POS all keep reading one field.
     */
    private function makeDefault(Customer $customer, CustomerAddress $address): void
    {
        $customer->addresses()->whereKeyNot($address->getKey())->update(['is_default' => false]);
        $address->forceFill(['is_default' => true])->save();

        $customer->forceFill(['address' => $address->single_line])->save();
    }

    private function listResponse(Customer $customer, int $status = 200): JsonResponse
    {
        $addresses = $customer->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'addresses' => CustomerAddressResource::collection($addresses),
        ], $status);
    }
}
