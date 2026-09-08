<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(string $action, ?string $tableName = null, ?int $recordId = null, ?string $details = null): ?AuditLog
    {
        try {
            return AuditLog::create([
                'user_id' => Auth::id(),
                'action' => strtoupper($action),
                'table_name' => $tableName,
                'record_id' => $recordId,
                'ip_address' => Request::ip(),
                'details' => $details,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if audit logging encounters an issue during unauthenticated or test runs
            return null;
        }
    }
}
