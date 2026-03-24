<?php

namespace App\Http\Controllers;

use App\Models\BroadcastNotification;
use App\Models\BroadcastNotificationRead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List unread/active notifications for current user (for popup).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = BroadcastNotification::visibleAt();
        if (!$user->hasRole('Supplier')) {
            $audiences = ['all'];
            if ($user->hasRole('Wholesaler')) {
                $audiences[] = 'wholesalers';
            }
            if ($user->hasRole('Retailer')) {
                $audiences[] = 'retailers';
            }
            $query->whereIn('audience', $audiences);
        }
        $notifications = $query->latest()->limit(10)->get();
        $readIds = BroadcastNotificationRead::where('user_id', $user->id)->pluck('broadcast_notification_id')->toArray();
        $unread = $notifications->filter(fn ($n) => !in_array($n->id, $readIds, true))->values();
        return response()->json([
            'data' => $unread->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'created_at' => $n->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markRead(Request $request, BroadcastNotification $notification): JsonResponse
    {
        BroadcastNotificationRead::firstOrCreate(
            ['broadcast_notification_id' => $notification->id, 'user_id' => $request->user()->id],
            ['read_at' => now()]
        );
        return response()->json(['message' => 'Marked as read.']);
    }
}
