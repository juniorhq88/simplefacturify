<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\InboxNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications
     * Returns unread notifications for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = InboxNotification::query()
            ->where('user_id', $request->user()->id)
            ->unread()
            ->with(['message.sender:id,name', 'thread:id,subject'])
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return NotificationResource::collection($notifications);
    }

    /**
     * PATCH /api/notifications/{notification}/read
     * Mark a single notification as read.
     */
    public function markRead(Request $request, InboxNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * POST /api/notifications/read-all
     * Mark all unread notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        InboxNotification::query()
            ->where('user_id', $request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
