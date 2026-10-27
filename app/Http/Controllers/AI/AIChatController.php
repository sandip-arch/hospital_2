<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AI\AiConversation;
use App\Services\AI\AgentService;
use App\Services\AI\ToolRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AIChatController extends Controller
{
    protected AgentService $agentService;
    protected ToolRegistry $toolRegistry;

    public function __construct(AgentService $agentService, ToolRegistry $toolRegistry)
    {
        $this->agentService = $agentService;
        $this->toolRegistry = $toolRegistry;
    }

    /**
     * POST /ai/chat - Process chat message and execute agent tool calls.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|integer|exists:ai_conversations,id',
        ]);

        $user = Auth::user();
        $conversation = null;

        if (!empty($validated['conversation_id'])) {
            $conversation = AiConversation::where('id', $validated['conversation_id'])
                ->where('user_id', $user->id)
                ->first();
        }

        $result = $this->agentService->handleUserMessage($validated['message'], $user, $conversation);

        return response()->json($result);
    }

    /**
     * GET /ai/history - List historical conversations strictly scoped to Auth::id().
     */
    public function history(): JsonResponse
    {
        $user = Auth::user();

        $conversations = AiConversation::where('user_id', $user->id)
            ->with('latestMessage')
            ->orderBy('updated_at', 'desc')
            ->take(20)
            ->get()
            ->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'title' => $conv->title,
                    'updated_at' => $conv->updated_at->diffForHumans(),
                    'last_message' => $conv->latestMessage?->content ? substr($conv->latestMessage->content, 0, 80) . '...' : '',
                ];
            });

        return response()->json([
            'status' => 'success',
            'conversations' => $conversations,
        ]);
    }

    /**
     * GET /ai/conversation/{id} - Retrieve message history for a specific conversation.
     */
    public function showConversation(int $id): JsonResponse
    {
        $user = Auth::user();

        $conversation = AiConversation::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['messages' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }])
            ->first();

        if (!$conversation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Conversation not found or access unauthorized.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'conversation_id' => $conversation->id,
            'title' => $conversation->title,
            'messages' => $conversation->messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'tool_calls' => $msg->tool_calls,
                    'tool_results' => $msg->tool_results,
                    'created_at' => $msg->created_at->format('h:i A'),
                ];
            }),
        ]);
    }

    /**
     * POST /ai/conversation/clear - Delete or reset a conversation.
     */
    public function clearConversation(Request $request): JsonResponse
    {
        $user = Auth::user();
        $id = $request->input('conversation_id');

        if ($id) {
            AiConversation::where('id', $id)->where('user_id', $user->id)->delete();
        } else {
            AiConversation::where('user_id', $user->id)->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Conversation history cleared.',
        ]);
    }

    /**
     * GET /ai/prompts - Retrieve role-aware quick-prompt chips for the widget.
     */
    public function quickPrompts(): JsonResponse
    {
        $user = Auth::user();

        if ($user->isPatient()) {
            $prompts = [
                ['icon' => 'fa-truck-medical', 'label' => 'Call Ambulance', 'prompt' => 'Call an emergency ambulance immediately to my address.'],
                ['icon' => 'fa-capsules', 'label' => 'Explain Prescriptions', 'prompt' => 'What medications are currently prescribed to me and how should I take them?'],
                ['icon' => 'fa-calendar-plus', 'label' => 'Book Appointment', 'prompt' => 'I would like to book a consultation appointment with a doctor.'],
                ['icon' => 'fa-file-invoice-dollar', 'label' => 'Check Bills', 'prompt' => 'Do I have any outstanding hospital bills or invoices?'],
                ['icon' => 'fa-vial', 'label' => 'My Lab Reports', 'prompt' => 'What are the results of my latest diagnostic lab tests?'],
            ];
        } elseif ($user->isDoctor()) {
            $prompts = [
                ['icon' => 'fa-calendar-day', 'label' => 'Today\'s Schedule', 'prompt' => 'Show me all patient consultations scheduled for today.'],
                ['icon' => 'fa-bed', 'label' => 'Bed Availability', 'prompt' => 'Check current inpatient bed availability and ICU capacity.'],
                ['icon' => 'fa-pen-to-square', 'label' => 'Write Prescription', 'prompt' => 'Help me write an electronic prescription for a patient.'],
                ['icon' => 'fa-vial-circle-check', 'label' => 'Order Lab Test', 'prompt' => 'What diagnostic pathology tests are available in our catalog?'],
            ];
        } elseif ($user->isDriver()) {
            $prompts = [
                ['icon' => 'fa-truck-medical', 'label' => 'Assigned Vehicle', 'prompt' => 'Show my assigned emergency ambulance vehicle and active dispatches.'],
                ['icon' => 'fa-toggle-on', 'label' => 'Toggle On-Duty', 'prompt' => 'Update my driver duty status to on_duty.'],
                ['icon' => 'fa-wrench', 'label' => 'Report Defect', 'prompt' => 'I want to submit an ambulance maintenance ticket.'],
            ];
        } else {
            // Receptionist / Staff / Admin
            $prompts = [
                ['icon' => 'fa-bed', 'label' => 'Bed Tracker', 'prompt' => 'Show bed occupancy matrix across ICU, Emergency, and Wards.'],
                ['icon' => 'fa-truck-fast', 'label' => 'Fleet Status', 'prompt' => 'Discover all active ambulances in the hospital fleet.'],
                ['icon' => 'fa-user-doctor', 'label' => 'Lookup Doctors', 'prompt' => 'List available physicians and their consultation fees.'],
                ['icon' => 'fa-file-invoice', 'label' => 'Billing Status', 'prompt' => 'Check total outstanding invoices in the billing ledger.'],
            ];
        }

        return response()->json([
            'status' => 'success',
            'role' => $user->primaryRole(),
            'role_display' => $user->primaryRoleDisplay(),
            'user_name' => $user->name,
            'prompts' => $prompts,
        ]);
    }
}
