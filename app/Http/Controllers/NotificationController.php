<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The signed-in admin's own bell.
 *
 * The shop-side twin of Api\NotificationController, and deliberately its own
 * class: this one answers to the `admin` session guard rather than a bearer
 * token, and the two must not be able to read each other's rows just because
 * they share a table.
 *
 * Polled, for the same reason as the storefront: a mart takes a few orders an
 * hour, not a few a second, and the unread count is a cheap enough question to
 * ask once a minute.
 */
class NotificationController extends Controller
{
    /** Rows per request when the caller does not say. */
    private const PER_PAGE = 10;

    /** The most a caller can ask for in one go. */
    private const MAX_PER_PAGE = 50;

    /** Whoever is signed into the panel right now. */
    private function admin()
    {
        return Auth::guard('admin')->user();
    }

    /**
     * A page of notifications, newest first, with the unread count and whether
     * there is anything older behind it.
     */
    public function index(Request $request): JsonResponse
    {
        $admin = $this->admin();
        $perPage = min(max((int) $request->query('per_page', self::PER_PAGE), 1), self::MAX_PER_PAGE);
        $page = max((int) $request->query('page', 1), 1);

        // One row more than asked for: if it comes back there is another page,
        // which is cheaper than counting the whole history to draw ten rows.
        $rows = $admin->notifications()
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

        return response()->json([
            'notifications' => NotificationResource::collection($rows->take($perPage)),
            'unread' => $admin->unreadNotifications()->count(),
            'page' => $page,
            'per_page' => $perPage,
            'has_more' => $rows->count() > $perPage,
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json(['unread' => $this->admin()->unreadNotifications()->count()]);
    }

    /** Marking one already-read is not an error - the panel may double-tap. */
    public function markRead(string $id): JsonResponse
    {
        $admin = $this->admin();
        $notification = $admin->notifications()->whereKey($id)->first();

        if (! $notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'notification' => new NotificationResource($notification->fresh()),
            'unread' => $admin->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        $this->admin()->unreadNotifications->markAsRead();

        return response()->json(['unread' => 0]);
    }

    public function destroy(string $id): JsonResponse
    {
        $admin = $this->admin();

        if (! $admin->notifications()->whereKey($id)->delete()) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        return response()->json(['unread' => $admin->unreadNotifications()->count()]);
    }
}
