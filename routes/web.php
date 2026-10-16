
<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\Admin\BookingManagementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ServiceManagementController;
use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ContentManagementController;
use App\Http\Controllers\Admin\CustomerManagementController;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\StaffActivationController;

use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\BookingDetailsController;
use App\Http\Controllers\Customer\CustomerCancelBookingController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerHistoryController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\FeedbackController;

use App\Http\Controllers\Staff\StaffAppointmentController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Landing Page
|--------------------------------------------------------------------------
*/

Route::get('/', [
    HomeController::class,
    'index'
])->name('home');

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Guest Authentication Pages
|--------------------------------------------------------------------------
| Authentication forms are available only to guests
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Login
    |--------------------------------------------------------------------------
    */

    Route::get('/login', [
        AuthenticatedSessionController::class,
        'create'
    ])->name('login');

    Route::post('/login', [
        AuthenticatedSessionController::class,
        'store'
    ])->name('login.submit');

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Customer Registration
    |--------------------------------------------------------------------------
    */

    Route::get('/register', [
        RegisteredUserController::class,
        'create'
    ])->name('register');

    Route::post('/register', [
        RegisteredUserController::class,
        'store'
    ])->name('register.store');

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: OTP Verification
    |--------------------------------------------------------------------------
    */

    Route::get('/otp-verification', [
        OtpController::class,
        'show'
    ])->name('otp.verification');

    Route::post('/otp-verification', [
        OtpController::class,
        'verify'
    ])->name('otp.verify');

    Route::post('/otp-resend', [
        OtpController::class,
        'resend'
    ])->name('otp.resend');

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Forgot and Reset Password
    |--------------------------------------------------------------------------
    */

    Route::get('/forgot-password', [
        OtpController::class,
        'forgotForm'
    ])->name('password.request');

    Route::post('/forgot-password', [
        OtpController::class,
        'sendForgotOtp'
    ])->name('password.otp.send');

    Route::get('/reset-password', [
        OtpController::class,
        'resetForm'
    ])->name('password.reset.form');

    Route::post('/reset-password', [
        OtpController::class,
        'resetPassword'
    ])->name('password.reset');
});

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Logout
|--------------------------------------------------------------------------
| Authenticated users can securely terminate their session
|--------------------------------------------------------------------------
*/

Route::post('/logout', [
    AuthenticatedSessionController::class,
    'destroy'
])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Customer Pages
|--------------------------------------------------------------------------
| All routes below require authentication and customer role
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:customer'
])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            CustomerDashboardController::class,
            'index'
        ])->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Booking
        |--------------------------------------------------------------------------
        */

        Route::get('/booking', [
            CustomerBookingController::class,
            'create'
        ])->name('booking');

        Route::post('/booking', [
            CustomerBookingController::class,
            'store'
        ])->name('booking.store');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Real-Time Booking Availability
        |--------------------------------------------------------------------------
        | Checks appointment date, time, staff, services,
        | overlapping appointments, and staff day-offs
        |
        | The BookingController will also be updated to
        | enforce studio-wide closure dates
        |--------------------------------------------------------------------------
        */

        Route::get('/booking/availability', [
            CustomerBookingController::class,
            'availability'
        ])->name('booking.availability');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer History
        |--------------------------------------------------------------------------
        */

        Route::get('/history', [
            CustomerHistoryController::class,
            'index'
        ])->name('history');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [
            CustomerProfileController::class,
            'show'
        ])->name('profile');

        Route::patch('/profile', [
            CustomerProfileController::class,
            'update'
        ])->name('profile.update');

        Route::patch('/profile/password', [
            CustomerProfileController::class,
            'updatePassword'
        ])->name('password.update');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Feedback
        |--------------------------------------------------------------------------
        */

        Route::get('/feedback/{id}', [
            FeedbackController::class,
            'show'
        ])->name('feedback');

        Route::post('/feedback/{id}', [
            FeedbackController::class,
            'store'
        ])->name('feedback.store');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Booking Details
        |--------------------------------------------------------------------------
        */

        Route::get('/booking-details/{id}', [
            BookingDetailsController::class,
            'show'
        ])->name('booking.details');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Booking Cancellation
        |--------------------------------------------------------------------------
        */

        Route::get('/cancel-booking/{id}', [
            CustomerCancelBookingController::class,
            'show'
        ])->name('booking.cancel.form');

        Route::patch('/cancel-booking/{id}', [
            CustomerCancelBookingController::class,
            'cancel'
        ])->name('booking.cancel');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Emergency Rescheduling
        |--------------------------------------------------------------------------
        | PATCH /customer/booking-details/{id}/reschedule
        |
        | - Customer must own the appointment
        | - Appointment must require emergency rescheduling
        | - Preserves original appointment details
        | - Checks staff eligibility and schedule conflicts
        | - Enforces the seven-day booking window
        | - Prevents Sunday appointments
        |
        | Studio-wide closure validation must also be
        | integrated into BookingDetailsController
        |--------------------------------------------------------------------------
        */

        Route::patch('/booking-details/{id}/reschedule', [
            BookingDetailsController::class,
            'reschedule'
        ])->name('booking.reschedule');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Notifications
        |--------------------------------------------------------------------------
        */

        Route::get('/notifications', [
            NotificationController::class,
            'index'
        ])->name('notifications');

        Route::patch('/notifications/read-all', [
            NotificationController::class,
            'readAll'
        ])->name('notifications.read-all');

        Route::patch('/notifications/{notification}/read', [
            NotificationController::class,
            'read'
        ])->name('notifications.read');
    });

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Staff Pages
|--------------------------------------------------------------------------
| All routes below require authentication and staff role
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:staff'
])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Staff Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            StaffAppointmentController::class,
            'dashboard'
        ])->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Staff Appointment Management
        |--------------------------------------------------------------------------
        */

        Route::get('/appointments', [
            StaffAppointmentController::class,
            'index'
        ])->name('appointments');

        Route::patch('/appointments/{appointment}/status', [
            StaffAppointmentController::class,
            'updateStatus'
        ])->name('appointments.status');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Staff Availability
        |--------------------------------------------------------------------------
        */

        Route::view(
            '/availability',
            'staff.availability'
        )->name('availability');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Staff Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [
            StaffAppointmentController::class,
            'profile'
        ])->name('profile');

        Route::patch('/profile', [
            StaffAppointmentController::class,
            'updateProfile'
        ])->name('profile.update');

        Route::patch('/profile/password', [
            StaffAppointmentController::class,
            'updatePassword'
        ])->name('password.update');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Staff Notifications
        |--------------------------------------------------------------------------
        */

        Route::get('/notifications', [
            NotificationController::class,
            'index'
        ])->name('notifications');

        Route::patch('/notifications/read-all', [
            NotificationController::class,
            'readAll'
        ])->name('notifications.read-all');

        Route::patch('/notifications/{notification}/read', [
            NotificationController::class,
            'read'
        ])->name('notifications.read');
    });

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Staff Account Activation
|--------------------------------------------------------------------------
| Allows staff to activate their accounts using a valid token
|--------------------------------------------------------------------------
*/

Route::get('/staff/activate/{token}', [
    StaffActivationController::class,
    'show'
])
    ->middleware('guest')
    ->name('staff.activate.form');

Route::post('/staff/activate/{token}', [
    StaffActivationController::class,
    'store'
])
    ->middleware('guest')
    ->name('staff.activate.store');

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Admin Pages
|--------------------------------------------------------------------------
| All routes below require authentication and administrator role
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin'
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Admin Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            AdminDashboardController::class,
            'index'
        ])->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking Management
        |--------------------------------------------------------------------------
        */

        Route::get('/bookings', [
            BookingManagementController::class,
            'index'
        ])->name('bookings');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Cancel Appointment
        |--------------------------------------------------------------------------
        | Requires administrator password in the controller
        |--------------------------------------------------------------------------
        */

        Route::patch('/bookings/{booking}/cancel', [
            BookingManagementController::class,
            'cancel'
        ])->name('bookings.cancel');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Individual Emergency Rescheduling
        |--------------------------------------------------------------------------
        | Admin marks an eligible booking as rescheduling_required
        |--------------------------------------------------------------------------
        */

        Route::patch('/bookings/{booking}/reschedule', [
            BookingManagementController::class,
            'requestRescheduling'
        ])->name('bookings.reschedule');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Studio-Wide Emergency Closure
        |--------------------------------------------------------------------------
        |
        | NEW INTEGRATED FEATURE
        |
        | This feature is part of Admin Booking Management
        | No separate sidebar module or StudioClosureController
        | is required
        |
        | POST /admin/bookings/closures
        |
        | Controller:
        | BookingManagementController::declareStudioClosure()
        |
        | Security:
        | - Authenticated administrator only
        | - CSRF verification
        | - Admin password required
        | - Date range validation
        | - Overlapping closure validation
        |
        | Effects:
        | - Creates a studio closure record
        | - Marks eligible future appointments for rescheduling
        | - Preserves original appointment details
        | - Records Audit Logs
        | - Attempts customer notifications
        |--------------------------------------------------------------------------
        */

        Route::post('/bookings/closures', [
            BookingManagementController::class,
            'declareStudioClosure'
        ])->name('bookings.closures.store');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Lift Studio-Wide Emergency Closure
        |--------------------------------------------------------------------------
        |
        | NEW INTEGRATED FEATURE
        |
        | PATCH /admin/bookings/closures/{closure}/lift
        |
        | Controller:
        | BookingManagementController::liftStudioClosure()
        |
        | Security:
        | - Authenticated administrator only
        | - CSRF verification
        | - Admin password required
        | - Reopening reason required
        |
        | Effects:
        | - Marks active closure as lifted
        | - Preserves closure history
        | - Records administrator and reopening details
        | - Does not automatically restore disrupted bookings
        |--------------------------------------------------------------------------
        */

        Route::patch('/bookings/closures/{closure}/lift', [
            BookingManagementController::class,
            'liftStudioClosure'
        ])->name('bookings.closures.lift');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Service Management
        |--------------------------------------------------------------------------
        */

        Route::get('/services', [
            ServiceManagementController::class,
            'index'
        ])->name('services');

        Route::post('/services', [
            ServiceManagementController::class,
            'store'
        ])->name('services.store');

        Route::patch('/services/{service}', [
            ServiceManagementController::class,
            'update'
        ])->name('services.update');

        Route::patch('/services/{service}/status', [
            ServiceManagementController::class,
            'toggleStatus'
        ])->name('services.status');

        Route::delete('/services/{service}', [
            ServiceManagementController::class,
            'destroy'
        ])->name('services.destroy');

        Route::patch('/services/{id}/restore', [
            ServiceManagementController::class,
            'restore'
        ])->name('services.restore');

        Route::delete('/services/{id}/force-delete', [
            ServiceManagementController::class,
            'forceDelete'
        ])->name('services.force-delete');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Management
        |--------------------------------------------------------------------------
        */

        Route::get('/customers', [
            CustomerManagementController::class,
            'index'
        ])->name('customers');

        Route::patch('/customers/{customer}/block', [
            CustomerManagementController::class,
            'block'
        ])->name('customers.block');

        Route::patch('/customers/{customer}/unblock', [
            CustomerManagementController::class,
            'unblock'
        ])->name('customers.unblock');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Staff Management
        |--------------------------------------------------------------------------
        */

        Route::get('/staff', [
            StaffManagementController::class,
            'index'
        ])->name('staff');

        Route::post('/staff', [
            StaffManagementController::class,
            'store'
        ])->name('staff.store');

        Route::patch('/staff/{staff}', [
            StaffManagementController::class,
            'update'
        ])->name('staff.update');

        Route::patch('/staff/{staff}/status', [
            StaffManagementController::class,
            'toggleStatus'
        ])->name('staff.status');

        Route::delete('/staff/{staff}', [
            StaffManagementController::class,
            'destroy'
        ])->name('staff.destroy');

        Route::patch('/staff/{id}/restore', [
            StaffManagementController::class,
            'restore'
        ])->name('staff.restore');

        Route::delete('/staff/{id}/force-delete', [
            StaffManagementController::class,
            'forceDelete'
        ])->name('staff.force-delete');

        Route::delete('/staff-day-offs/{dayOff}', [
            StaffManagementController::class,
            'destroyDayOff'
        ])->name('staff.day-offs.destroy');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Content Management
        |--------------------------------------------------------------------------
        */

        Route::get('/content', [
            ContentManagementController::class,
            'index'
        ])->name('content');

        Route::patch('/content', [
            ContentManagementController::class,
            'update'
        ])->name('content.update');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Audit Logs
        |--------------------------------------------------------------------------
        */

        Route::get('/audit-logs', [
            AuditLogController::class,
            'index'
        ])->name('auditlogs');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Settings
        |--------------------------------------------------------------------------
        */

        Route::view(
            '/settings',
            'admin.settings'
        )->name('settings');

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Admin Notifications
        |--------------------------------------------------------------------------
        */

        Route::get('/notifications', [
            NotificationController::class,
            'index'
        ])->name('notifications');

        Route::patch('/notifications/read-all', [
            NotificationController::class,
            'readAll'
        ])->name('notifications.read-all');

        Route::patch('/notifications/{notification}/read', [
            NotificationController::class,
            'read'
        ])->name('notifications.read');
    });
