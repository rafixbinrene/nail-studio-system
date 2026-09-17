<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('user_type') && $request->user_type !== 'all') {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('module') && $request->module !== 'all') {
            $query->where('module', $request->module);
        }

        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', '%' . $search . '%')
                    ->orWhere('module', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('user_type', 'like', '%' . $search . '%')
                    ->orWhere('ip_address', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $logs = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => AuditLog::count(),
            'customer' => AuditLog::where('user_type', 'customer')->count(),
            'staff' => AuditLog::where('user_type', 'staff')->count(),
            'admin' => AuditLog::where('user_type', 'admin')->count(),
            'today' => AuditLog::whereDate('created_at', now()->toDateString())->count(),
        ];

        $modules = AuditLog::whereNotNull('module')
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actions = AuditLog::whereNotNull('action')
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.auditlogs', [
            'logs' => $logs,
            'stats' => $stats,
            'modules' => $modules,
            'actions' => $actions,
            'filters' => [
                'search' => $request->search,
                'user_type' => $request->user_type ?? 'all',
                'module' => $request->module ?? 'all',
                'action' => $request->action ?? 'all',
                'date' => $request->date,
            ],
        ]);
    }
}