<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Admin Service Management Controller
|--------------------------------------------------------------------------
| Purpose:
| - Add new beauty services.
| - Edit existing service details.
| - Activate and deactivate services.
| - Move deleted services to Trash.
| - Restore deleted services.
| - Permanently delete trashed services after 30 days.
| - Record administrative actions in Audit Logs.
|
| IMPORTANT UPDATE:
| Service images are no longer supported in Service Management.
| All services are now managed using text-based details only.
|
| Defense Explanation:
| This module centralizes service information such as service name,
| description, price, duration, and availability status.
|
| Admin updates are saved in the central database so that the same
| information can be displayed across Admin, Staff, Customer,
| and public website interfaces.
|--------------------------------------------------------------------------
*/

class ServiceManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Display Service Management
    |--------------------------------------------------------------------------
    | Retrieves active service records and services in Trash.
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Automatic Trash Cleanup
        |--------------------------------------------------------------------------
        | Permanently removes services that have remained in Trash
        | for 30 days or longer.
        |--------------------------------------------------------------------------
        */

        $this->deleteExpiredTrashedServices();

        $services = Service::latest()->get();

        $deletedServices = Service::onlyTrashed()
            ->latest('deleted_at')
            ->get();

        return view(
            'admin.services',
            compact('services', 'deletedServices')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Create New Service
    |--------------------------------------------------------------------------
    | Only service details are accepted.
    | No service image is uploaded or stored.
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_name' => [
                'required',
                'string',
                'max:120',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'duration' => [
                'required',
                'integer',
                'min:5',
                'max:600',
            ],

            'status' => [
                'required',
                Rule::in([
                    'Available',
                    'Unavailable',
                ]),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Save Service to Database
        |--------------------------------------------------------------------------
        | New service records contain only the necessary information.
        |
        | If the image_path column exists, explicitly set it to null.
        | This maintains compatibility with the existing database.
        |--------------------------------------------------------------------------
        */

        $service = new Service();

        $service->service_name = $validated['service_name'];
        $service->description = $validated['description'] ?? null;
        $service->price = $validated['price'];
        $service->duration = $validated['duration'];
        $service->status = $validated['status'];

        if (Schema::hasColumn('services', 'image_path')) {
            $service->image_path = null;
        }

        $service->save();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Audit Log - Create Service
        |--------------------------------------------------------------------------
        */

        $this->createAuditLog(
            action: 'create_service',
            description:
                'Admin created service: ' . $service->service_name
        );

        return redirect()
            ->route('admin.services')
            ->with(
                'success',
                'Service added successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Update Service
    |--------------------------------------------------------------------------
    | Updates service name, description, price, duration,
    | and availability status.
    |
    | Existing image paths are not modified. Images will be hidden
    | when we update the service views across all accounts.
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'service_name' => [
                'required',
                'string',
                'max:120',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'duration' => [
                'required',
                'integer',
                'min:5',
                'max:600',
            ],

            'status' => [
                'required',
                Rule::in([
                    'Available',
                    'Unavailable',
                ]),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Save Updated Service Details
        |--------------------------------------------------------------------------
        */

        $service->update($validated);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Audit Log - Update Service
        |--------------------------------------------------------------------------
        */

        $this->createAuditLog(
            action: 'update_service',
            description:
                'Admin updated service: ' . $service->service_name
        );

        return redirect()
            ->route('admin.services')
            ->with(
                'success',
                'Service updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Toggle Service Availability
    |--------------------------------------------------------------------------
    | Available services can be changed to Unavailable and vice versa.
    |
    | All modules that retrieve current service data from MySQL
    | will receive the updated availability status.
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(Service $service)
    {
        $newStatus = $service->status === 'Available'
            ? 'Unavailable'
            : 'Available';

        $service->update([
            'status' => $newStatus,
        ]);

        $this->createAuditLog(
            action: 'change_service_status',
            description:
                'Admin changed service status: '
                . $service->service_name
                . ' to '
                . $newStatus
        );

        return redirect()
            ->route('admin.services')
            ->with(
                'success',
                'Service status updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Move Service to Trash
    |--------------------------------------------------------------------------
    | Uses soft deletion instead of immediately removing the record.
    |--------------------------------------------------------------------------
    */

    public function destroy(Service $service)
    {
        $serviceName = $service->service_name;

        $service->delete();

        $this->createAuditLog(
            action: 'trash_service',
            description:
                'Admin moved service to Trash: ' . $serviceName
        );

        return redirect()
            ->route('admin.services')
            ->with(
                'success',
                'Service moved to Trash. '
                . 'It will be permanently deleted after 30 days.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Restore Service
    |--------------------------------------------------------------------------
    | Restores a previously soft-deleted service from Trash.
    |--------------------------------------------------------------------------
    */

    public function restore(int $id)
    {
        $service = Service::onlyTrashed()
            ->findOrFail($id);

        $serviceName = $service->service_name;

        $service->restore();

        $this->createAuditLog(
            action: 'restore_service',
            description:
                'Admin restored service from Trash: '
                . $serviceName
        );

        return redirect()
            ->route('admin.services')
            ->with(
                'success',
                'Service restored successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Permanently Delete Service
    |--------------------------------------------------------------------------
    | Permanently deletes a service already in Trash.
    |
    | Service image files are not deleted because image management
    | is no longer part of this module.
    |--------------------------------------------------------------------------
    */

    public function forceDelete(int $id)
    {
        $service = Service::onlyTrashed()
            ->findOrFail($id);

        $serviceName = $service->service_name;

        $service->forceDelete();

        $this->createAuditLog(
            action: 'permanent_delete_service',
            description:
                'Admin permanently deleted service: '
                . $serviceName
        );

        return redirect()
            ->route('admin.services')
            ->with(
                'success',
                'Service permanently deleted.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Automatic Deletion After 30 Days
    |--------------------------------------------------------------------------
    | Retrieves services that have stayed in Trash for at least 30 days
    | and permanently removes them.
    |
    | No image files are accessed or deleted.
    |--------------------------------------------------------------------------
    */

    private function deleteExpiredTrashedServices(): void
    {
        $expiredServices = Service::onlyTrashed()
            ->where(
                'deleted_at',
                '<=',
                now()->subDays(30)
            )
            ->get();

        foreach ($expiredServices as $service) {
            $service->forceDelete();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Record Audit Logs
    |--------------------------------------------------------------------------
    | Stores the administrative activity details.
    |
    | Optional fields are checked before insertion to remain compatible
    | with the current audit_logs database schema.
    |--------------------------------------------------------------------------
    */

    private function createAuditLog(
        string $action,
        string $description
    ): void {
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
