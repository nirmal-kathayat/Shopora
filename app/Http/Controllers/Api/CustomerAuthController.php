<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Storefront (customer) authentication. Admins log in through the session
 * guard on the web routes; this is a separate, token-only door.
 */
class CustomerAuthController extends Controller
{
    /** Failed logins allowed per identifier + IP before a lockout. */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /** How long that lockout lasts, in seconds. */
    private const LOCKOUT_SECONDS = 300;

    /** Abilities every storefront token carries. */
    private const TOKEN_ABILITIES = ['customer'];

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        // If the shop already has this person on file from a counter sale, keep
        // that row so their purchase history survives the signup.
        $customer = $request->claimableCustomer() ?? new Customer();

        $customer->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'ph_number' => $data['ph_number'],
        ]);

        // Set outside fill() - password is not mass assignable. The model's
        // 'hashed' cast turns this into a bcrypt hash on the way in.
        $customer->password = $data['password'];

        if (array_key_exists('address', $data) && $data['address'] !== null) {
            $customer->address = $data['address'];
        }

        $customer->save();

        return $this->tokenResponse($customer, $request->input('device_name'), 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $identifier = $request->string('identifier')->toString();
        $throttleKey = $this->throttleKey($identifier, $request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'identifier' => 'Too many login attempts. Try again in '
                    . RateLimiter::availableIn($throttleKey) . ' seconds.',
            ])->status(429);
        }

        $customer = Customer::matchingIdentifier($identifier)->first();

        // A counter-created customer has no password at all, so treat it the
        // same as a wrong one rather than leaking that the row exists.
        if (! $customer || ! $customer->isRegistered() || ! Hash::check($request->input('password'), $customer->password)) {
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);

            throw ValidationException::withMessages([
                'identifier' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        return $this->tokenResponse($customer, $request->input('device_name'));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'customer' => new CustomerResource($request->user()),
        ]);
    }

    /**
     * Sign out of this device only - other logged-in devices keep working.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out of all devices.']);
    }

    private function tokenResponse(Customer $customer, ?string $deviceName, int $status = 200): JsonResponse
    {
        $token = $customer->createToken(
            $deviceName ?: 'storefront',
            self::TOKEN_ABILITIES
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'customer' => new CustomerResource($customer),
        ], $status);
    }

    private function throttleKey(string $identifier, Request $request): string
    {
        return 'customer-login:' . Str::lower($identifier) . '|' . $request->ip();
    }
}
