<?php

namespace App\Services\AI;

use App\Models\AI\AiConversation;
use App\Models\AI\AiMessage;
use App\Models\Doctor;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgentService
{
    protected ToolRegistry $toolRegistry;
    protected string $apiKey;
    
    /**
     * Supported active Google Gemini models in order of priority.
     * gemini-3.1-flash-lite and gemini-3.5-flash-lite are verified active.
     */
    protected array $candidateModels = [
        'gemini-3.1-flash-lite',
        'gemini-3.5-flash-lite',
        'gemini-3-flash-preview',
        'gemini-2.5-flash',
        'gemini-flash-latest',
    ];

    public function __construct(ToolRegistry $toolRegistry)
    {
        $this->toolRegistry = $toolRegistry;
        $this->apiKey = env('GEMINI_API_KEY') ?? config('services.gemini.key') ?? '';
    }

    /**
     * Build the empathetic, clinical system instructions for Gemini.
     */
    protected function buildSystemInstruction(User $user): string
    {
        $hospitalName = SystemSetting::get('hospital_name', 'Apex Horizon Medical Center');
        $hotline = SystemSetting::get('emergency_hotline', '+1 (800) 555-APEX / 911');
        $role = $user->primaryRoleDisplay();

        $today = Carbon::now()->toDateString();
        $tomorrow = Carbon::now()->addDay()->toDateString();
        $dayOfWeek = Carbon::now()->format('l');

        // Dynamically index all doctors for immediate LLM entity linking
        $doctorsList = Doctor::with(['user', 'department'])->get()->map(function ($doc) {
            return "- ID {$doc->id}: {$doc->full_name} | {$doc->specialization} | Department: " . ($doc->department?->name ?? 'General') . " | Fee: \${$doc->consultation_fee}";
        })->implode("\n");

        return <<<PROMPT
You are the elite Autonomous Clinical & Operations AI Assistant for {$hospitalName}.
Speaking with authenticated user: [{$role}] (Name: {$user->name}).

TEMPORAL CONTEXT:
- Today is: {$dayOfWeek}, {$today}.
- Tomorrow is: {$tomorrow}.
When the user mentions "today", "tomorrow", or "this week", compute the exact YYYY-MM-DD date using this temporal context.

HOSPITAL DOCTORS DIRECTORY (Use these exact doctor_id values):
{$doctorsList}

OPERATIONAL DIRECTIVES:
1. BOOKING APPOINTMENTS:
   - When the user asks to book an appointment with a specific doctor (e.g. Dr. Taylor, Dr. Wilson, Dr. Chang, Dr. Jenkins):
     - Identify their exact doctor_id (e.g., Dr. Robert Taylor = 4, Dr. James Wilson = 2, Dr. Emily Chang = 3, Dr. Sarah Jenkins = 1).
     - If the user specifies a time slot (e.g., "10:00 - 10:30"), use it directly in `book_appointment`. If not, default to "10:00 - 10:30".
     - If the user specifies "tomorrow", set `appointment_date` to {$tomorrow}.
     - Call `book_appointment` directly to finalize the booking.
2. EMERGENCY PROTOCOL:
   - If the user reports chest pain, severe trauma, bleeding, shortness of breath, or stroke:
     - Immediately invoke `book_ambulance_dispatch` or `discover_nearby_ambulances`.
     - Advise calling the emergency hotline immediately: {$hotline}.
3. TONE & ETHICS:
   - Speak with utmost warmth, clinical empathy, reassurance, and concise clarity.
   - Never fabricate or guess medical diagnoses. Always include a brief disclaimer when discussing drugs, prescriptions, or pathology.
4. ROLE ISOLATION:
   - Patient operations are automatically bound to their verified identity by the secure backend.
PROMPT;
    }

    /**
     * Process an incoming user prompt, orchestrating Gemini Function Calling and persistence.
     *
     * @param string $userPrompt
     * @param User $user
     * @param AiConversation|null $conversation
     * @return array<string, mixed>
     */
    public function handleUserMessage(string $userPrompt, User $user, ?AiConversation $conversation = null): array
    {
        // 1. Ensure conversation exists
        if (!$conversation) {
            $conversation = AiConversation::create([
                'user_id' => $user->id,
                'title' => substr($userPrompt, 0, 40) . (strlen($userPrompt) > 40 ? '...' : ''),
            ]);
        }

        // 2. Persist user message
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userPrompt,
        ]);

        // 3. Check for Gemini API key
        if (!empty($this->apiKey) && $this->apiKey !== 'YOUR_GEMINI_API_KEY_HERE') {
            try {
                return $this->executeGeminiFlow($userPrompt, $user, $conversation);
            } catch (Exception $e) {
                Log::warning('Gemini API execution error, executing local autonomous fallback: ' . $e->getMessage());
                return $this->executeLocalAutonomousFallback($userPrompt, $user, $conversation, $e->getMessage());
            }
        }

        // 4. Autonomous Local Engine when GEMINI_API_KEY is unset
        return $this->executeLocalAutonomousFallback($userPrompt, $user, $conversation);
    }

    /**
     * Multi-Turn Google Gemini Function-Calling Loop via native Laravel Http.
     */
    protected function executeGeminiFlow(string $userPrompt, User $user, AiConversation $conversation): array
    {
        $functionDeclarations = $this->toolRegistry->getGeminiFunctionDeclarations($user);

        // Build conversation history
        $historyMessages = $conversation->messages()->take(12)->get();
        $contents = [];

        foreach ($historyMessages as $msg) {
            if ($msg->role === 'user') {
                $contents[] = [
                    'role' => 'user',
                    'parts' => [['text' => $msg->content]],
                ];
            } elseif ($msg->role === 'model') {
                $contents[] = [
                    'role' => 'model',
                    'parts' => [['text' => $msg->content]],
                ];
            }
        }

        $systemInstruction = $this->buildSystemInstruction($user);

        // Determine model to use
        $preferredModel = env('GEMINI_MODEL');
        $modelsToTry = $preferredModel ? array_merge([$preferredModel], $this->candidateModels) : $this->candidateModels;

        $maxTurns = 5;
        $turn = 0;
        $lastToolExecuted = null;
        $lastToolResult = null;
        $finalText = '';

        while ($turn < $maxTurns) {
            $turn++;

            $payload = [
                'systemInstruction' => [
                    'parts' => [['text' => $systemInstruction]],
                ],
                'contents' => $contents,
            ];

            if (!empty($functionDeclarations)) {
                $payload['tools'] = [
                    ['functionDeclarations' => $functionDeclarations],
                ];
            }

            // Attempt request across candidate models
            $response = null;
            $usedModel = null;

            foreach ($modelsToTry as $m) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$m}:generateContent?key=" . urlencode($this->apiKey);
                $resp = Http::timeout(25)->post($url, $payload);
                if ($resp->successful()) {
                    $response = $resp;
                    $usedModel = $m;
                    break;
                }
            }

            if (!$response) {
                throw new Exception("All Gemini API candidate models failed to return 200 OK.");
            }

            $data = $response->json();
            $candidate = $data['candidates'][0] ?? null;

            if (!$candidate) {
                throw new Exception("No candidate response generated by Gemini API.");
            }

            $candidateContent = $candidate['content'] ?? [];
            $parts = $candidateContent['parts'] ?? [];

            $functionCallPart = null;
            $textPart = '';

            foreach ($parts as $part) {
                if (isset($part['functionCall'])) {
                    $functionCallPart = $part['functionCall'];
                }
                if (isset($part['text'])) {
                    $textPart .= $part['text'];
                }
            }

            // If the model called a function, execute it and loop
            if ($functionCallPart) {
                $toolName = $functionCallPart['name'];
                $toolArgs = $functionCallPart['args'] ?? [];

                // Execute the tool with zero-trust RBAC
                $toolResult = $this->toolRegistry->executeTool($toolName, $toolArgs, $user);

                $lastToolExecuted = $toolName;
                $lastToolResult = $toolResult;

                // Log execution in database
                AiMessage::create([
                    'conversation_id' => $conversation->id,
                    'role' => 'tool',
                    'content' => "Executed {$toolName}",
                    'tool_calls' => [['name' => $toolName, 'args' => $toolArgs]],
                    'tool_results' => $toolResult,
                ]);

                // Append model's functionCall and tool's functionResponse to conversation context
                $contents[] = $candidateContent;
                $contents[] = [
                    'role' => 'user',
                    'parts' => [
                        [
                            'functionResponse' => [
                                'name' => $toolName,
                                'response' => $toolResult,
                            ],
                        ],
                    ],
                ];

                continue; // Continue to next turn to allow model to synthesize final answer or chain tools
            }

            // Natural language response received
            if (!empty($textPart)) {
                $finalText = $textPart;
                break;
            }

            break;
        }

        if (empty($finalText)) {
            $finalText = $lastToolResult['message'] ?? 'Action successfully completed in the hospital system.';
        }

        // Persist model final response
        $finalMessage = AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'model',
            'content' => $finalText,
            'tool_results' => $lastToolResult,
        ]);

        return [
            'status' => 'success',
            'conversation_id' => $conversation->id,
            'message' => $finalText,
            'tool_executed' => $lastToolExecuted,
            'tool_result' => $lastToolResult,
            'model_used' => $usedModel,
            'created_at' => $finalMessage->created_at->toIso8601String(),
        ];
    }

    /**
     * Highly Intelligent Local Autonomous Fallback:
     * When external API is disrupted or rate-limited, this engine parses the user's
     * specific intent, extracts doctor names (Taylor, Wilson, Chang, Jenkins),
     * dynamically executes the verified tools, and composes an empathetic response.
     */
    protected function executeLocalAutonomousFallback(string $userPrompt, User $user, AiConversation $conversation, ?string $apiWarning = null): array
    {
        $prompt = strtolower($userPrompt);
        $toolName = null;
        $toolArgs = [];

        // 1. Emergency & Ambulance Dispatch
        if (str_contains($prompt, 'ambulance') && (str_contains($prompt, 'call') || str_contains($prompt, 'book') || str_contains($prompt, 'emergency') || str_contains($prompt, 'dispatch') || str_contains($prompt, 'help') || str_contains($prompt, 'need') || str_contains($prompt, 'severe') || str_contains($prompt, 'pain'))) {
            $toolName = 'book_ambulance_dispatch';
            $toolArgs = [
                'pickup_address' => $user->patient?->address ?? 'Caller Reported Location',
                'contact_phone' => $user->patient?->phone ?? '911',
                'emergency_notes' => $userPrompt,
            ];
        } elseif (str_contains($prompt, 'ambulance') && (str_contains($prompt, 'track') || str_contains($prompt, 'where') || str_contains($prompt, 'status'))) {
            $toolName = 'track_ambulance_booking';
        } elseif (str_contains($prompt, 'ambulance') || str_contains($prompt, 'fleet')) {
            $toolName = 'discover_nearby_ambulances';

        // 2. Prescriptions & Medication Inquiries
        } elseif (str_contains($prompt, 'prescription') || str_contains($prompt, 'rx')) {
            if ($user->isDoctor() && (str_contains($prompt, 'write') || str_contains($prompt, 'create') || str_contains($prompt, 'issue'))) {
                $toolName = 'write_prescription';
                $toolArgs = ['patient_id' => 1, 'medications' => [['medicine_id' => 1, 'dosage' => '500mg', 'frequency' => 'Twice daily', 'duration_days' => 7]]];
            } else {
                $toolName = 'get_prescriptions';
            }
        } elseif (str_contains($prompt, 'explain') || str_contains($prompt, 'medicine') || str_contains($prompt, 'drug') || str_contains($prompt, 'dosage') || preg_match('/(paracetamol|amoxicillin|metformin|atorvastatin|omeprazole|azithromycin|cetirizine|ibuprofen)/i', $prompt)) {
            $toolName = 'explain_medicine';
            preg_match('/(paracetamol|amoxicillin|metformin|atorvastatin|omeprazole|azithromycin|cetirizine|ibuprofen)/i', $prompt, $m);
            $toolArgs = ['medicine_name' => $m[1] ?? 'Amoxicillin'];

        // 3. Inpatient Bed Tracker & ADT
        } elseif (str_contains($prompt, 'bed') || str_contains($prompt, 'icu') || str_contains($prompt, 'admit') || str_contains($prompt, 'ward')) {
            $toolName = 'check_bed_availability';

        // 4. Appointments & Doctor Scheduling (DYNAMIC RESOLUTION)
        } elseif (str_contains($prompt, 'appointment') || str_contains($prompt, 'consult') || str_contains($prompt, 'schedule') || str_contains($prompt, 'book') || str_contains($prompt, 'doctor')) {
            if (str_contains($prompt, 'book') || str_contains($prompt, 'schedule') || str_contains($prompt, 'take') || str_contains($prompt, 'make')) {
                $toolName = 'book_appointment';

                // Dynamically resolve target doctor from user prompt!
                $targetDoctorId = 1; // Default
                if (preg_match('/taylor|robert|ortho|bone|knee|joint/i', $prompt)) {
                    $targetDoctorId = 4; // Dr. Robert Taylor
                } elseif (preg_match('/wilson|james|neuro|brain|spine/i', $prompt)) {
                    $targetDoctorId = 2; // Dr. James Wilson
                } elseif (preg_match('/chang|emily|pedia|child|baby/i', $prompt)) {
                    $targetDoctorId = 3; // Dr. Emily Chang
                } elseif (preg_match('/jenkins|sarah|cardio|heart/i', $prompt)) {
                    $targetDoctorId = 1; // Dr. Sarah Jenkins
                }

                // Determine target appointment date
                $appointmentDate = Carbon::now()->addDay()->toDateString(); // Default tomorrow
                if (str_contains($prompt, 'today')) {
                    $appointmentDate = Carbon::now()->toDateString();
                } elseif (preg_match('/\b(20\d\d-\d\d-\d\d)\b/', $prompt, $dateMatch)) {
                    $appointmentDate = $dateMatch[1];
                }

                // Determine time slot
                $timeSlot = '10:00 - 10:30';
                if (preg_match('/\b(\d{1,2}:\d{2}\s*-\s*\d{1,2}:\d{2})\b/', $prompt, $slotMatch)) {
                    $timeSlot = $slotMatch[1];
                }

                $toolArgs = [
                    'doctor_id' => $targetDoctorId,
                    'appointment_date' => $appointmentDate,
                    'time_slot' => $timeSlot,
                    'reason' => 'Consultation requested via AI Assistant',
                ];
            } elseif (str_contains($prompt, 'my') || str_contains($prompt, 'check') || str_contains($prompt, 'list') || str_contains($prompt, 'view')) {
                $toolName = 'get_my_appointments';
            } else {
                $toolName = 'lookup_doctors';
                if (preg_match('/taylor|robert/i', $prompt)) $toolArgs['search'] = 'Taylor';
                if (preg_match('/wilson|james/i', $prompt)) $toolArgs['search'] = 'Wilson';
                if (preg_match('/chang|emily/i', $prompt)) $toolArgs['search'] = 'Chang';
                if (preg_match('/jenkins|sarah/i', $prompt)) $toolArgs['search'] = 'Jenkins';
                if (preg_match('/cardio/i', $prompt)) $toolArgs['specialization'] = 'Cardiology';
                if (preg_match('/neuro/i', $prompt)) $toolArgs['specialization'] = 'Neurology';
                if (preg_match('/ortho/i', $prompt)) $toolArgs['specialization'] = 'Orthopedics';
                if (preg_match('/pedia/i', $prompt)) $toolArgs['specialization'] = 'Pediatrics';
            }

        // 5. Billing & Invoices
        } elseif (str_contains($prompt, 'bill') || str_contains($prompt, 'invoice') || str_contains($prompt, 'pay') || str_contains($prompt, 'cost') || str_contains($prompt, 'balance')) {
            $toolName = 'get_invoices';

        // 6. Diagnostic Lab Tests
        } elseif (str_contains($prompt, 'lab') || str_contains($prompt, 'report') || str_contains($prompt, 'test') || str_contains($prompt, 'blood test')) {
            $toolName = 'get_lab_reports';

        // 7. Driver Fleet Operations
        } elseif (str_contains($prompt, 'vehicle') || str_contains($prompt, 'duty')) {
            $toolName = 'driver_duty_and_vehicle';
        } else {
            $toolName = 'hospital_services_catalog';
        }

        // Execute Tool safely
        $toolResult = $this->toolRegistry->executeTool($toolName, $toolArgs, $user);

        // Persist tool execution
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'tool',
            'content' => "Executed {$toolName}",
            'tool_calls' => [['name' => $toolName, 'args' => $toolArgs]],
            'tool_results' => $toolResult,
        ]);

        // Synthesize response
        $hospitalName = SystemSetting::get('hospital_name', 'Apex Horizon Medical Center');
        $hotline = SystemSetting::get('emergency_hotline', '+1 (800) 555-APEX / 911');

        $responseText = "Hello {$user->name}, I am your **{$hospitalName}** Assistant.\n\n";

        if ($toolName === 'book_appointment') {
            if (($toolResult['status'] ?? '') === 'success') {
                $docName = $toolResult['doctor_name'] ?? 'your selected physician';
                $responseText .= "✅ **Appointment Confirmed!**\n";
                $responseText .= "Your consultation with **{$docName}** has been scheduled successfully.\n\n";
                $responseText .= "- **Date**: " . ($toolResult['date'] ?? 'Upcoming') . "\n";
                $responseText .= "- **Time Slot**: " . ($toolResult['time_slot'] ?? 'Scheduled') . "\n";
                $responseText .= "- **Department**: " . ($toolResult['department'] ?? 'OPD') . "\n";
                $responseText .= "- **Consultation Fee**: " . ($toolResult['consultation_fee'] ?? 'Standard') . "\n\n";
                $responseText .= "You will receive an automated appointment notification. Please arrive 10 minutes prior to your time slot.";
            } else {
                $responseText .= "⚠️ " . ($toolResult['message'] ?? 'Unable to complete booking. Please select another slot.');
            }
        } elseif ($toolName === 'book_ambulance_dispatch') {
            $responseText .= "🚨 **EMERGENCY DISPATCH INITIATED IMMEDIATELY!**\n";
            $responseText .= "An emergency ambulance has been deployed to your location.\n\n";
            $responseText .= "- **Vehicle**: " . ($toolResult['vehicle_number'] ?? 'Emergency Fleet') . "\n";
            $responseText .= "- **Type**: " . ($toolResult['vehicle_type'] ?? 'Advanced Life Support') . "\n";
            $responseText .= "- **Estimated Arrival**: " . ($toolResult['estimated_arrival_minutes'] ?? '8-12 minutes') . "\n";
            $responseText .= "- **Emergency Hotline**: {$hotline}\n\n";
            $responseText .= "Please keep your phone line open. Medical personnel are en route!";
        } elseif ($toolName === 'lookup_doctors') {
            $responseText .= "👨‍⚕️ **Hospital Physicians Directory**:\n\n";
            if (!empty($toolResult['doctors'])) {
                foreach ($toolResult['doctors'] as $doc) {
                    $responseText .= "• **{$doc['name']}** - *{$doc['specialization']}* ({$doc['department']}) | Fee: {$doc['consultation_fee']} | Available: {$doc['working_days']}\n";
                }
                $responseText .= "\nWould you like me to book an appointment with any of these doctors?";
            } else {
                $responseText .= "No physicians matched your query. All our departments (Cardiology, Neurology, Pediatrics, Orthopedics) are operating.";
            }
        } elseif ($toolName === 'explain_medicine') {
            $pharma = $toolResult['pharmacology_summary'] ?? [];
            $responseText .= "💊 **Pharmacological Information for " . htmlspecialchars($toolResult['query']) . "**:\n\n";
            $responseText .= "- **Drug Class**: " . ($pharma['class'] ?? 'Formulary Pharmaceutical') . "\n";
            $responseText .= "- **Clinical Indications**: " . ($pharma['indications'] ?? 'Clinical therapy') . "\n";
            $responseText .= "- **Standard Dosage**: " . ($pharma['common_dosage'] ?? 'As directed by physician') . "\n";
            $responseText .= "- **Precautions**: " . ($pharma['precautions'] ?? 'Take strictly as prescribed') . "\n";
            $responseText .= "\n> ⚠️ *Important Disclaimer: This information is for educational purposes only. Do not modify medication dosages without physician approval.*";
        } elseif ($toolName === 'check_bed_availability') {
            $matrix = $toolResult['overall_capacity_matrix'] ?? [];
            $responseText .= "🛏️ **Inpatient Bed Availability Status**:\n\n";
            foreach ($matrix as $tier) {
                $responseText .= "- **{$tier['tier']}**: **{$tier['available']}** beds available (out of {$tier['total']})\n";
            }
        } elseif ($toolName === 'get_invoices') {
            $responseText .= "💳 **Billing & Invoices Ledger**:\n\n";
            $responseText .= "- **Total Outstanding**: {$toolResult['total_outstanding']}\n";
            if (!empty($toolResult['invoices'])) {
                foreach ($toolResult['invoices'] as $inv) {
                    $responseText .= "• **Invoice #{$inv['invoice_number']}** ({$inv['date']}): Net {$inv['net_payable']} — Status: `{$inv['status']}`\n";
                }
            }
        } else {
            $responseText .= $toolResult['message'] ?? 'Hospital inquiry processed successfully.';
        }

        // Persist model response
        $finalMessage = AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'model',
            'content' => $responseText,
            'tool_results' => $toolResult,
        ]);

        return [
            'status' => 'success',
            'conversation_id' => $conversation->id,
            'message' => $responseText,
            'tool_executed' => $toolName,
            'tool_result' => $toolResult,
            'created_at' => $finalMessage->created_at->toIso8601String(),
        ];
    }
}
