<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\AmbulanceController;
use App\Http\Controllers\Admin\AmbulanceAdminController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('public.home');
Route::get('/doctors-directory', [PublicController::class, 'doctors'])->name('public.doctors');
Route::get('/departments-overview', [PublicController::class, 'departments'])->name('public.departments');
Route::post('/book-appointment-request', [PublicController::class, 'bookRequest'])->name('public.bookRequest');

// Ambulance Module: Discovery Map, Booking & Zomato-Style Live Tracking
Route::get('/ambulance', [AmbulanceController::class, 'index'])->name('ambulance.index');
Route::get('/ambulance/book/{ambulance?}', [AmbulanceController::class, 'showBookingForm'])->name('ambulance.book');
Route::post('/ambulance/book', [AmbulanceController::class, 'storeBooking'])->name('ambulance.book.submit');
Route::get('/ambulance/track/{id}', [AmbulanceController::class, 'track'])->name('ambulance.track');
Route::get('/ambulance/my-bookings', [AmbulanceController::class, 'myBookings'])->name('ambulance.my-bookings');

// Ambulance Live APIs (Coordinates, Tracking & Real-Time Simulation)
Route::get('/ambulance/api/locations', [AmbulanceController::class, 'apiLocations'])->name('ambulance.api.locations');
Route::get('/ambulance/api/track/{id}', [AmbulanceController::class, 'apiTrack'])->name('ambulance.api.track');
Route::post('/ambulance/api/simulate-step/{id}', [AmbulanceController::class, 'apiSimulateStep'])->name('ambulance.api.simulate');
Route::post('/ambulance/api/reposition-near-device', [AmbulanceController::class, 'apiRepositionNearDevice'])->name('ambulance.api.reposition');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/login/demo/{role}', [AuthController::class, 'demoLogin'])->name('login.demo');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Dashboard & Feature Modules
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // User Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Central Dashboard (Auto-redirects by role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 1. Patient Management
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{id}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients/{id}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{id}', [PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{id}', [PatientController::class, 'destroy'])->name('patients.destroy');
    Route::post('/patients/{id}/document', [PatientController::class, 'uploadDocument'])->name('patients.uploadDocument');

    // 2. Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // 3. Doctor Schedules
    Route::get('/doctor/schedule', [DoctorScheduleController::class, 'index'])->name('doctor.schedule');
    Route::post('/doctor/schedule', [DoctorScheduleController::class, 'update'])->name('doctor.schedule.update');

    // 4. Clinical EMR & Medical Records
    Route::get('/medical-records', [MedicalRecordController::class, 'index'])->name('medical-records.index');
    Route::get('/medical-records/create', [MedicalRecordController::class, 'create'])->name('medical-records.create');
    Route::post('/medical-records', [MedicalRecordController::class, 'store'])->name('medical-records.store');
    Route::get('/medical-records/{id}', [MedicalRecordController::class, 'show'])->name('medical-records.show');
    Route::get('/medical-records/{id}/print', [MedicalRecordController::class, 'print'])->name('medical-records.print');

    // 5. Prescriptions & Pharmacy Dispensing
    Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/prescriptions/create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::post('/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::get('/prescriptions/{id}', [PrescriptionController::class, 'show'])->name('prescriptions.show');
    Route::post('/prescriptions/{id}/dispense', [PrescriptionController::class, 'dispense'])->name('prescriptions.dispense');
    Route::get('/prescriptions/{id}/print', [PrescriptionController::class, 'print'])->name('prescriptions.print');

    // 6. Diagnostics & Lab Management
    Route::get('/lab/catalog', [LabController::class, 'index'])->name('lab.catalog');
    Route::get('/lab/requests', [LabController::class, 'requests'])->name('lab.requests');
    Route::post('/lab/order', [LabController::class, 'order'])->name('lab.order');
    Route::post('/lab/requests/{id}/result', [LabController::class, 'storeResult'])->name('lab.requests.result');
    Route::get('/lab/reports/{id}', [LabController::class, 'showReport'])->name('lab.reports.show');
    Route::post('/lab/tests', [LabController::class, 'storeTest'])->name('lab.tests.store');

    // 7. Facilities & Bed Management (ADT)
    Route::get('/facilities/bed-tracker', [FacilityController::class, 'bedTracker'])->name('facilities.bed-tracker');
    Route::get('/facilities/admissions', [FacilityController::class, 'admissions'])->name('facilities.admissions');
    Route::post('/facilities/admit', [FacilityController::class, 'admit'])->name('facilities.admit');
    Route::post('/facilities/discharge/{id}', [FacilityController::class, 'discharge'])->name('facilities.discharge');
    Route::post('/facilities/beds/{id}/status', [FacilityController::class, 'updateBedStatus'])->name('facilities.beds.updateStatus');
    Route::post('/facilities/rooms', [FacilityController::class, 'storeRoom'])->name('facilities.rooms.store');

    // 8. Pharmacy & Inventory Management
    Route::get('/pharmacy', [PharmacyController::class, 'index'])->name('pharmacy.index');
    Route::post('/pharmacy', [PharmacyController::class, 'store'])->name('pharmacy.store');
    Route::put('/pharmacy/{id}', [PharmacyController::class, 'update'])->name('pharmacy.update');
    Route::post('/pharmacy/{id}/adjust', [PharmacyController::class, 'adjustStock'])->name('pharmacy.adjustStock');
    Route::post('/pharmacy/{id}/order', [PharmacyController::class, 'placeOrder'])->name('pharmacy.order');
    Route::get('/pharmacy/dispense-queue', [PharmacyController::class, 'dispenseQueue'])->name('pharmacy.dispense-queue');

    // 9. Billing & Invoicing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/create', [BillingController::class, 'create'])->name('billing.create');
    Route::post('/billing', [BillingController::class, 'store'])->name('billing.store');
    Route::get('/billing/{id}', [BillingController::class, 'show'])->name('billing.show');
    Route::post('/billing/{id}/pay', [BillingController::class, 'collectPayment'])->name('billing.pay');
    Route::get('/billing/{id}/print', [BillingController::class, 'printInvoice'])->name('billing.print');

    // 10. Communication & Notifications
    Route::get('/notifications', [CommunicationController::class, 'notifications'])->name('communication.notifications');
    Route::get('/notifications/{id}/open', [CommunicationController::class, 'openNotification'])->name('communication.notifications.open');
    Route::post('/notifications/{id}/read', [CommunicationController::class, 'markAsRead'])->name('communication.notifications.read');
    Route::post('/notifications/read-all', [CommunicationController::class, 'markAllRead'])->name('communication.notifications.readAll');
    Route::get('/messages', [CommunicationController::class, 'messages'])->name('communication.messages');
    Route::post('/messages', [CommunicationController::class, 'sendMessage'])->name('communication.messages.send');

    // 11. System Administration & RBAC (Superadmin & Admin only)
    Route::middleware(['role:superadmin,admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');

        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/custom', [SystemSettingController::class, 'storeSetting'])->name('settings.store');
        Route::delete('/settings/{id}', [SystemSettingController::class, 'destroySetting'])->name('settings.destroy');

        // Ambulance Fleet Management (Superadmin & Admin only)
        Route::get('/ambulances', [AmbulanceAdminController::class, 'index'])->name('ambulances.index');
        Route::get('/ambulances/create', [AmbulanceAdminController::class, 'create'])->name('ambulances.create');
        Route::post('/ambulances', [AmbulanceAdminController::class, 'store'])->name('ambulances.store');
        Route::get('/ambulances/{id}/edit', [AmbulanceAdminController::class, 'edit'])->name('ambulances.edit');
        Route::put('/ambulances/{id}', [AmbulanceAdminController::class, 'update'])->name('ambulances.update');
        Route::delete('/ambulances/{id}', [AmbulanceAdminController::class, 'destroy'])->name('ambulances.destroy');
        Route::get('/ambulance-drivers', [AmbulanceAdminController::class, 'drivers'])->name('ambulances.drivers');
        Route::post('/ambulance-drivers', [AmbulanceAdminController::class, 'storeDriver'])->name('ambulances.drivers.store');
    });
});
