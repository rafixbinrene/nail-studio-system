<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This controller handles Admin Service Management.
|
| Purpose:
| - Add services.
| - Edit services.
| - Upload and remove service images.
| - Activate/deactivate services.
| - Move deleted services to Trash.
| - Restore deleted services.
| - Permanently delete trashed services after 30 days.
|
| Defense explanation:
| This module keeps the service catalog organized while protecting deleted
| records through a temporary Trash system.
*/

class ServiceManagementController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Automatically removes services that stayed in Trash for more than 30 days.
        |--------------------------------------------------------------------------
        */
        $this->deleteExpiredTrashedServices();

        $services = Service::latest()->get();

        $deletedServices = Service::onlyTrashed()
            ->latest('deleted_at')
            ->get();

        return view('admin.services', compact('services', 'deletedServices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'duration' => ['required', 'integer', 'min:5', 'max:600'],
            'status' => ['required', Rule::in(['Available', 'Unavailable'])],
        ]);

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('services', 'public');
        }

        $service = Service::create($validated);

        $this->createAuditLog(
            action: 'create_service',
            description: 'Admin created service: ' . $service->service_name
        );

        return redirect()
            ->route('admin.services')
            ->with('success', 'Service added successfully.');
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'service_name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image_path' => ['nullable', 'boolean'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'duration' => ['required', 'integer', 'min:5', 'max:600'],
            'status' => ['required', Rule::in(['Available', 'Unavailable'])],
        ]);

        $data = collect($validated)
            ->except(['image_path', 'remove_image_path'])
            ->toArray();

        if ($request->boolean('remove_image_path')) {
            $this->deleteImage($service->image_path);
            $data['image_path'] = null;
        }

        if ($request->hasFile('image_path')) {
            $this->deleteImage($service->image_path);
            $data['image_path'] = $request->file('image_path')->store('services', 'public');
        }

        $service->update($data);

        $this->createAuditLog(
            action: 'update_service',
            description: 'Admin updated service: ' . $service->service_name
        );

        return redirect()
            ->route('admin.services')
            ->with('success', 'Service updated successfully.');
    }

    public function toggleStatus(Service $service)
    {
        $newStatus = $service->status === 'Available' ? 'Unavailable' : 'Available';

        $service->update([
            'status' => $newStatus,
        ]);

        $this->createAuditLog(
            action: 'change_service_status',
            description: 'Admin changed service status: ' . $service->service_name . ' to ' . $newStatus
        );

        return redirect()
            ->route('admin.services')
            ->with('success', 'Service status updated successfully.');
    }

    public function destroy(Service $service)
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | This is soft delete only.
        | The service moves to Trash and can still be restored.
        |--------------------------------------------------------------------------
        */
        $serviceName = $service->service_name;

        $service->delete();

        $this->createAuditLog(
            action: 'trash_service',
            description: 'Admin moved service to Trash: ' . $serviceName
        );

        return redirect()
            ->route('admin.services')
            ->with('success', 'Service moved to Trash. It will be permanently deleted after 30 days.');
    }

    public function restore(int $id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);

        $serviceName = $service->service_name;

        $service->restore();

        $this->createAuditLog(
            action: 'restore_service',
            description: 'Admin restored service from Trash: ' . $serviceName
        );

        return redirect()
            ->route('admin.services')
            ->with('success', 'Service restored successfully.');
    }

    public function forceDelete(int $id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);

        $serviceName = $service->service_name;

        $this->deleteImage($service->image_path);

        $service->forceDelete();

        $this->createAuditLog(
            action: 'permanent_delete_service',
            description: 'Admin permanently deleted service: ' . $serviceName
        );

        return redirect()
            ->route('admin.services')
            ->with('success', 'Service permanently deleted.');
    }

    private function deleteExpiredTrashedServices(): void
    {
        $expiredServices = Service::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->get();

        foreach ($expiredServices as $service) {
            $this->deleteImage($service->image_path);
            $service->forceDelete();
        }
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function createAuditLog(string $action, string $description): void
    {
        $data = [
            'user_type' => auth()->user()->role ?? 'admin',
            'action' => $action,
            'description' => $description,
        ];

        if (Schema::hasColumn('audit_logs', 'user_id')) {
            $data['user_id'] = auth()->id();
        }

        if (Schema::hasColumn('audit_logs', 'module')) {
            $data['module'] = 'Service Management';
        }

        if (Schema::hasColumn('audit_logs', 'ip_address')) {
            $data['ip_address'] = request()->ip();
        }

        if (Schema::hasColumn('audit_logs', 'user_agent')) {
            $data['user_agent'] = request()->userAgent();
        }

        AuditLog::create($data);
    }
}