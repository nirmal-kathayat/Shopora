<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The signed-in customer's own notifications.
 *
 * Polled rather than pushed: an order changes hands a few times a day, not a
 * few times a second, so a WebSocket would be a long-running process to keep
 * alive for almost no news. The unread count is its own endpoint so the header
 * bell can ask cheaply without pulling the list.
 */
class NotificationController extends Controller
{
    /** How many a customer can see at once - older ones are history, not news. */
    private const PAGE = 30;

    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();

        return response()->json([
            'notifications' => NotificationResource::collection(
                $customer->notifications()->latest()->limit(self::PAGE)->get()
            ),
            'unread' => $customer->unreadNotifications()->count(),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json(['unread' => $request->user()->unreadNotifications()->count()]);
    }

    /** Marking one already-read is not an error - the client may double-tap. */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->first();

        if (! $notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'notification' => new NotificationResource($notification->fresh()),
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['unread' => 0]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $deleted = $request->user()->notifications()->whereKey($id)->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        return response()->json(['unread' => $request->user()->unreadNotifications()->count()]);
    }
}
