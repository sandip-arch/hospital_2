<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Message;
use App\Models\User;
use App\Services\AuditService;

class CommunicationController extends Controller
{
    public function notifications()
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('communication.notifications', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function messages(Request $request)
    {
        $currentUserId = Auth::id();
        $selectedUserId = $request->query('user_id');

        $users = User::where('id', '!=', $currentUserId)->where('status', 'active')->orderBy('name')->get();

        $selectedUser = null;
        $messages = collect();

        if ($selectedUserId) {
            $selectedUser = User::findOrFail($selectedUserId);

            // Mark received messages from this user as read
            Message::where('sender_id', $selectedUserId)
                ->where('receiver_id', $currentUserId)
                ->update(['is_read' => true]);

            $messages = Message::where(function ($q) use ($currentUserId, $selectedUserId) {
                $q->where('sender_id', $currentUserId)->where('receiver_id', $selectedUserId);
            })->orWhere(function ($q) use ($currentUserId, $selectedUserId) {
                $q->where('sender_id', $selectedUserId)->where('receiver_id', $currentUserId);
            })->orderBy('sent_at', 'asc')->get();
        }

        return view('communication.messages', compact('users', 'selectedUser', 'messages'));
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message_body' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'message_body' => $validated['message_body'],
            'sent_at' => now(),
            'is_read' => false,
        ]);

        // Send a notification to the recipient
        Notification::create([
            'user_id' => $validated['receiver_id'],
            'title' => 'New Internal Message',
            'message' => "Message from " . Auth::user()->name . ": " . substr($validated['message_body'], 0, 50) . '...',
            'type' => 'system',
            'is_read' => false,
        ]);

        return redirect()->route('communication.messages', ['user_id' => $validated['receiver_id']])->with('success', 'Message sent.');
    }
}
