<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public function log(string $action, string $module, Model $model, array $old = [], array $new = []): void
    {
        /** @var Request $request */
        $request = request();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'module' => $module,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }
}
