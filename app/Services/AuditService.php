<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Create an audit log entry.
     *
     * @param  string  $actionType
     * @param  string  $description
     * @param  string  $level
     * @return \App\Models\AuditLog
     */
    public static function log(string $actionType, string $description, string $level = 'info')
    {
        return AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => $actionType,
            'description' => $description,
            'log_level'   => $level,
        ]);
    }
}
