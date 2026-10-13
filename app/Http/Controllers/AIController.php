<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\AIContextService;

class AIController extends Controller
{
    /**
     * Send an authenticated user's message
     * to the internal Node AI server.
     */
    public function chat(
    Request $request,
    AIContextService $aiContextService
    ){
        // ========================================
        // 1. Validate message
        // ========================================

        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        // ========================================
        // 2. Get authenticated Laravel user
        // ========================================

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthenticated.',
            ], 401);
        }

        // ========================================
        // 3. Load only required relationships
        // ========================================

        $user->load([
            'roles',
            'patient',
            'doctor',
            'staff',
        ]);

        // ========================================
        // 4. Resolve roles from Laravel
        // ========================================

        $roles = $user->roles
            ->pluck('name')
            ->values()
            ->all();

        // ========================================
        // 5. Determine profile
        // ========================================

        $profileType = null;
        $profileId = null;

        if ($user->patient) {
            $profileType = 'patient';
            $profileId = $user->patient->id;
        } elseif ($user->doctor) {
            $profileType = 'doctor';
            $profileId = $user->doctor->id;
        } elseif ($user->staff) {
            $profileType = 'staff';
            $profileId = $user->staff->id;
        }

        // ========================================
        // 6. Build SAFE AI context
        // ========================================

        $liveContext = $aiContextService->build(
    $user,
    $validated['message']
);

$aiContext = [
    'userId' => $user->id,
    'roles' => $roles,
    'profileType' => $profileType,
    'profileId' => $profileId,
    'liveContext' => $liveContext,
];

        // ========================================
        // 7. Call internal Node AI server
        // ========================================

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-ai-internal-secret' => config('services.ai.secret'),
                ])
                ->post(
                    rtrim(config('services.ai.url'), '/') . '/api/chat',
                    [
                        'message' => $validated['message'],
                        'user' => $aiContext,
                    ]
                );

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'error' => 'AI service is unavailable.',
            ], 503);
        }

        // ========================================
        // 8. Handle Node failure
        // ========================================

        if (!$response->successful()) {

            return response()->json([
                'success' => false,
                'error' => 'AI service rejected the request.',
            ], $response->status());
        }

        // ========================================
        // 9. Return safe AI response
        // ========================================

        return response()->json(
            $response->json()
        );
    }
}