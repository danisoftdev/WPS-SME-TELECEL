<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BroadcastNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_notifications');
    }

    public function index(): View
    {
        $notifications = BroadcastNotification::with('creator')->orderByDesc('created_at')->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create(): View
    {
        $roles = \Spatie\Permission\Models\Role::where('guard_name', 'web')->pluck('name');
        return view('admin.notifications.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'audience' => 'required|in:all,wholesalers,retailers,custom',
            'role_ids' => 'nullable|array',
            'active_from' => 'nullable|date',
            'active_until' => 'nullable|date|after_or_equal:active_from',
            'is_active' => 'boolean',
        ]);
        BroadcastNotification::create([
            'title' => $request->title,
            'message' => $request->message,
            'audience' => $request->audience,
            'role_ids' => $request->role_ids,
            'active_from' => $request->active_from,
            'active_until' => $request->active_until,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
        ]);
        return redirect()->route('admin.notifications.index')->with('success', 'Notification created.');
    }

    public function edit(BroadcastNotification $notification): View
    {
        $roles = \Spatie\Permission\Models\Role::where('guard_name', 'web')->pluck('name');
        return view('admin.notifications.edit', compact('notification', 'roles'));
    }

    public function update(Request $request, BroadcastNotification $notification): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'audience' => 'required|in:all,wholesalers,retailers,custom',
            'role_ids' => 'nullable|array',
            'active_from' => 'nullable|date',
            'active_until' => 'nullable|date',
            'is_active' => 'boolean',
        ]);
        $notification->update([
            'title' => $request->title,
            'message' => $request->message,
            'audience' => $request->audience,
            'role_ids' => $request->role_ids,
            'active_from' => $request->active_from,
            'active_until' => $request->active_until,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('admin.notifications.index')->with('success', 'Notification updated.');
    }

    public function destroy(BroadcastNotification $notification): RedirectResponse
    {
        $notification->delete();
        return redirect()->route('admin.notifications.index')->with('success', 'Notification deleted.');
    }
}
