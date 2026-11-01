<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Message;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Admission;
use App\Models\Doctor;
use App\Services\AuditService;

class CommunicationController extends Controller
{
    /**
     * Get allowed chat contact User IDs for a patient (only their appointed doctors and receptionists).
     */
    public static function getAllowedPatientContactUserIds(User $user)
    {
        $patient = $user->patient;
        $doctorUserIds = collect();

        if ($patient) {
            $appointmentDocIds = Appointment::where('patient_id', $patient->id)->pluck('doctor_id');
            $admissionDocIds = Admission::where('patient_id', $patient->id)->pluck('doctor_id');
            $allDocIds = $appointmentDocIds->merge($admissionDocIds)->filter()->unique();

            $doctorUserIds = Doctor::whereIn('id', $allDocIds)->whereNotNull('user_id')->pluck('user_id');
        }

        // Hospital Front Desk / Receptionists
        $receptionistUserIds = User::where('status', 'active')
            ->where(function ($q) {
                $q->whereHas('staff', function ($sq) {
                    $sq->where(function ($tsq) {
                        $tsq->where('job_title', 'like', '%receptionist%')
                            ->orWhere('job_title', 'like', '%front desk%');
                    });
                })->orWhereHas('roles', function ($rq) {
                    $rq->where('name', 'receptionist');
                });
            })
            ->pluck('id');

        return $doctorUserIds->merge($receptionistUserIds)->unique()->values();
    }

    /**
     * Get allowed chat contact User IDs for an ambulance driver (admin, superadmin, hospital admin).
     */
    public static function getAllowedDriverContactUserIds(User $user)
    {
        return User::where('status', 'active')
            ->where(function ($q) {
                $q->whereHas('roles', function ($rq) {
                    $rq->whereIn('name', ['superadmin', 'admin']);
                });
            })
            ->pluck('id');
    }

    public function notifications()
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('communication.notifications', compact('notifications'));
    }

    public function livePoll(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'latest_id' => 0, 'notifications' => []]);
        }

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'type_icon' => $n->type_icon,
                    'target_url' => route('communication.notifications.open', $n->id),
                    'time_ago' => $n->created_at->diffForHumans(),
                ];
            });

        $latestId = Notification::where('user_id', $user->id)->max('id') ?? 0;

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'latest_id' => $latestId,
            'notifications' => $notifications,
        ]);
    }

    public function ajaxMarkAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'success', 'message' => 'All marked as read.']);
    }

    public function ajaxMarkRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->find($id);
        if ($notification) {
            $notification->update(['is_read' => true]);
        }

        return response()->json(['status' => 'success']);
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

    public function openNotification($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->update(['is_read' => true]);

        return redirect($notification->target_url);
    }

    public function messages(Request $request)
    {
        $currentUser = Auth::user();
        $currentUserId = $currentUser->id;
        $selectedUserId = $request->query('user_id');

        if ($currentUser->isPatient()) {
            $allowedContactIds = self::getAllowedPatientContactUserIds($currentUser);

            $users = User::with(['roles', 'doctor.department', 'staff'])
                ->whereIn('id', $allowedContactIds)
                ->where('id', '!=', $currentUserId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            // Guard: if patient attempts to navigate to an unauthorized contact, block and redirect
            if ($selectedUserId && !$allowedContactIds->contains((int) $selectedUserId)) {
                return redirect()->route('communication.messages')
                    ->with('error', 'Patients can only chat with their appointed doctors or hospital receptionists.');
            }
        } elseif ($currentUser->isDriver()) {
            $allowedContactIds = self::getAllowedDriverContactUserIds($currentUser);

            $users = User::with(['roles', 'doctor.department', 'staff'])
                ->whereIn('id', $allowedContactIds)
                ->where('id', '!=', $currentUserId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            // Guard: if driver attempts to navigate to an unauthorized contact, block and redirect
            if ($selectedUserId && !$allowedContactIds->contains((int) $selectedUserId)) {
                return redirect()->route('communication.messages')
                    ->with('error', 'Ambulance drivers can only chat with administrative personnel (Admin, Superadmin, Hospital Admin).');
            }
        } else {
            // For medical and administrative staff: full internal roster
            $users = User::with(['roles', 'doctor.department', 'staff', 'patient', 'ambulanceDriver'])
                ->where('id', '!=', $currentUserId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        $selectedUser = null;
        $messages = collect();

        if ($selectedUserId) {
            $selectedUser = User::with(['roles', 'doctor.department', 'staff', 'patient', 'ambulanceDriver'])->findOrFail($selectedUserId);

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

        $currentUser = Auth::user();

        // Security check: patients can only send messages to their appointed doctors or receptionists
        if ($currentUser->isPatient()) {
            $allowedContactIds = self::getAllowedPatientContactUserIds($currentUser);
            if (!$allowedContactIds->contains((int) $validated['receiver_id'])) {
                return back()->with('error', 'Unauthorized. Patients can only message their appointed doctors or hospital receptionists.');
            }
        } elseif ($currentUser->isDriver()) {
            $allowedContactIds = self::getAllowedDriverContactUserIds($currentUser);
            if (!$allowedContactIds->contains((int) $validated['receiver_id'])) {
                return back()->with('error', 'Unauthorized. Ambulance drivers can only message administrative personnel (Admin, Superadmin, Hospital Admin).');
            }
        }

        $message = Message::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => $validated['receiver_id'],
            'message_body' => $validated['message_body'],
            'sent_at' => now(),
            'is_read' => false,
        ]);

        // Send a notification to the recipient
        Notification::create([
            'user_id' => $validated['receiver_id'],
            'title' => 'New Internal Message',
            'message' => "Message from " . $currentUser->name . ": " . substr($validated['message_body'], 0, 50) . '...',
            'type' => 'system',
            'is_read' => false,
        ]);

        return redirect()->route('communication.messages', ['user_id' => $validated['receiver_id']])->with('success', 'Message sent.');
    }

    public function chatPoll(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['status' => 'unauthenticated'], 401);
        }

        $contactId = (int) $request->query('user_id');
        $lastMessageId = (int) $request->query('last_id', 0);

        if (!$contactId) {
            return response()->json(['status' => 'error', 'message' => 'No contact specified']);
        }

        // Mark incoming unread messages from this contact as read
        Message::where('sender_id', $contactId)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Get status of messages sent by currentUser to this contact that are read
        $readMessageIds = Message::where('sender_id', $currentUser->id)
            ->where('receiver_id', $contactId)
            ->where('is_read', true)
            ->pluck('id');

        // Check for new messages since lastMessageId
        $newMessages = Message::where(function ($q) use ($currentUser, $contactId) {
                $q->where('sender_id', $currentUser->id)->where('receiver_id', $contactId);
            })->orWhere(function ($q) use ($currentUser, $contactId) {
                $q->where('sender_id', $contactId)->where('receiver_id', $currentUser->id);
            })
            ->where('id', '>', $lastMessageId)
            ->orderBy('sent_at', 'asc')
            ->get()
            ->map(function ($msg) use ($currentUser) {
                return [
                    'id' => $msg->id,
                    'is_me' => $msg->sender_id === $currentUser->id,
                    'message_body' => $msg->message_body,
                    'time' => $msg->sent_at ? $msg->sent_at->format('h:i A') : '',
                    'is_read' => (bool) $msg->is_read,
                ];
            });

        return response()->json([
            'status' => 'success',
            'read_ids' => $readMessageIds,
            'new_messages' => $newMessages,
        ]);
    }
}
