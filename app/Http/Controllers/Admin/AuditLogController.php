<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $action = $request->input('action');
        $actorType = $request->input('actor_type');

        $logs = AuditLog::query()
            ->when($action, fn ($q) => $q->where('action', 'like', "%{$action}%"))
            ->when(in_array($actorType, ['admin', 'customer', 'system'], true), fn ($q) => $q->where('actor_type', $actorType))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $actions = AuditLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit.index', compact('logs', 'action', 'actorType', 'actions'));
    }
}
