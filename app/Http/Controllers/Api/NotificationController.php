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
    /** Rows per request when the caller does not say. */
    private const PER_PAGE = 10;

    /** The most a caller can ask for in one go. */
    private const MAX_PER_PAGE = 50;

    /**
     * A page of notifications, newest first, with the unread count and whether
     * there is anything older. The bell's panel shows ten and loads more on
     * demand rather than pulling a year of history to draw five rows.
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        $perPage = min(max((int) $request->query('per_page', self::PER_PAGE), 1), self::MAX_PER_PAGE);
        $page = max((int) $request->query('page', 1), 1);

        // One row more than asked for: if it comes back, there is another page,
        // which is cheaper than counting every notification the customer owns.
        $rows = $customer->notifications()
            // created_at is second-precision, so two notifications written in
            // the same second tie - and an unstable sort under skip/take is
            // what makes "Load more" repeat a row or step over one. The id is
            // a random uuid, meaningless as an order but a total one, which is
            // all the tie-break has to be.
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->skip(($page - 1) * $perPage)
            ->take($perPage + 1)
            ->get();

        $hasMore = $rows->count() > $perPage;

        return response()->json([
            'notifications' => NotificationResource::collection($rows->take($perPage)),
            'unread' => $customer->unreadNotifications()->count(),
            'page' => $page,
            'per_page' => $perPage,
            'has_more' => $hasMore,
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
