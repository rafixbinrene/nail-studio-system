<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\BookingManagementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\BookingDetailsController;
use App\Http\Controllers\Customer\CustomerCancelBookingController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerHistoryController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\FeedbackController;
use App\Http\Controllers\Staff\StaffAppointmentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ServiceManagementController;
use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ContentManagementController;
use App\Http\Controllers\Admin\CustomerManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing Page
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Guest Authentication Pages
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.submit');

    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->name('register.store');

    Route::get('/otp-verification', [OtpController::class, 'show'])
        ->name('otp.verification');

    Route::post('/otp-verification', [OtpController::class, 'verify'])
        ->name('otp.verify');

    Route::post('/otp-resend', [OtpController::class, 'resend'])
        ->name('otp.resend');

    Route::get('/forgot-password', [OtpController::class, 'forgotForm'])
        ->name('password.request');

    Route::post('/forgot-password', [OtpController::class, 'sendForgotOtp'])
        ->name('password.otp.send');

    Route::get('/reset-password', [OtpController::class, 'resetForm'])
        ->name('password.reset.form');

    Route::post('/reset-password', [OtpController::class, 'resetPassword'])
        ->name('password.reset');
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Customer Pages
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/booking', [CustomerBookingController::class, 'create'])
            ->name('booking');

        Route::post('/booking', [CustomerBookingController::class, 'store'])
            ->name('booking.store');

        Route::get('/history', [CustomerHistoryController::class, 'index'])
            ->name('history');

        Route::get('/profile', [CustomerProfileController::class, 'show'])
            ->name('profile');

        Route::patch('/profile', [CustomerProfileController::class, 'update'])
            ->name('profile.update');

        Route::patch('/profile/password', [CustomerProfileController::class, 'updatePassword'])
            ->name('password.update');

        Route::get('/feedback/{id}', [FeedbackController::class, 'show'])
            ->name('feedback');

        Route::post('/feedback/{id}', [FeedbackController::class, 'store'])
            ->name('feedback.store');

        Route::get('/booking-details/{id}', [BookingDetailsController::class, 'show'])
            ->name('booking.details');

        Route::get('/cancel-booking/{id}', [CustomerCancelBookingController::class, 'show'])
            ->name('booking.cancel.form');

        Route::patch('/cancel-booking/{id}', [CustomerCancelBookingController::class, 'cancel'])
            ->name('booking.cancel');

        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications');

        Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])
            ->name('notifications.read-all');

        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])
            ->name('notifications.read');
    });

/*
|--------------------------------------------------------------------------
| Staff Pages
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', [StaffAppointmentController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/appointments', [StaffAppointmentController::class, 'index'])
            ->name('appointments');

        Route::patch('/appointments/{appointment}/status', [StaffAppointmentController::class, 'updateStatus'])
            ->name('appointments.status');

        Route::view('/availability', 'staff.availability')
            ->name('availability');

        Route::get('/profile', [StaffAppointmentController::class, 'profile'])
            ->name('profile');

        Route::patch('/profile', [StaffAppointmentController::class, 'updateProfile'])
            ->name('profile.update');

        Route::patch('/profile/password', [StaffAppointmentController::class, 'updatePassword'])
            ->name('password.update');

        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications');

        Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])
            ->name('notifications.read-all');

        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])
            ->name('notifications.read');
    });

/*
|--------------------------------------------------------------------------
| Admin Pages
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/bookings', [BookingManagementController::class, 'index'])
            ->name('bookings');

        Route::patch('/bookings/{booking}/cancel', [BookingManagementController::class, 'cancel'])
            ->name('bookings.cancel');

        Route::get('/services', [ServiceManagementController::class, 'index'])
            ->name('services');

        Route::post('/services', [ServiceManagementController::class, 'store'])
            ->name('services.store');

        Route::patch('/services/{service}', [ServiceManagementController::class, 'update'])
            ->name('services.update');

        Route::patch('/services/{service}/status', [ServiceManagementController::class, 'toggleStatus'])
            ->name('services.status');

        Route::delete('/services/{service}', [ServiceManagementController::class, 'destroy'])
            ->name('services.destroy');

        Route::patch('/services/{id}/restore', [ServiceManagementController::class, 'restore'])
            ->name('services.restore');

        Route::delete('/services/{id}/force-delete', [ServiceManagementController::class, 'forceDelete'])
            ->name('services.force-delete');

        Route::get('/customers', [CustomerManagementController::class, 'index'])
            ->name('customers');

        Route::patch('/customers/{customer}/block', [CustomerManagementController::class, 'block'])
            ->name('customers.block');

        Route::patch('/customers/{customer}/unblock', [CustomerManagementController::class, 'unblock'])
            ->name('customers.unblock');

        Route::get('/staff', [StaffManagementController::class, 'index'])
            ->name('staff');

        Route::post('/staff', [StaffManagementController::class, 'store'])
            ->name('staff.store');

        Route::patch('/staff/{staff}', [StaffManagementController::class, 'update'])
            ->name('staff.update');

        Route::patch('/staff/{staff}/status', [StaffManagementController::class, 'toggleStatus'])
            ->name('staff.status');

        Route::delete('/staff/{staff}', [StaffManagementController::class, 'destroy'])
            ->name('staff.destroy');

        Route::patch('/staff/{id}/restore', [StaffManagementController::class, 'restore'])
            ->name('staff.restore');

        Route::delete('/staff/{id}/force-delete', [StaffManagementController::class, 'forceDelete'])
            ->name('staff.force-delete');

        Route::delete('/staff-day-offs/{dayOff}', [StaffManagementController::class, 'destroyDayOff'])
            ->name('staff.day-offs.destroy');

        Route::get('/content', [ContentManagementController::class, 'index'])
            ->name('content');

        Route::patch('/content', [ContentManagementController::class, 'update'])
            ->name('content.update');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->name('auditlogs');

        Route::view('/settings', 'admin.settings')
            ->name('settings');

        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications');

        Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])
            ->name('notifications.read-all');

        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])
            ->name('notifications.read');
    });