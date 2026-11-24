<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard (Live Database Aggregated KPIs)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shipments Visibility & Operations
    Route::resource('shipments', ShipmentController::class);
    Route::post('/shipments/{shipment}/transition', [ShipmentController::class, 'transition'])
        ->name('shipments.transition')
        ->middleware('role:admin,operator');
    Route::post('/shipments/{shipment}/tracking', [ShipmentController::class, 'addTracking'])
        ->name('shipments.tracking.store')
        ->middleware('role:admin,operator');
    Route::post('/shipments/{shipment}/exceptions', [ShipmentController::class, 'raiseException'])
        ->name('shipments.exceptions.store')
        ->middleware('role:admin,operator');
    Route::post('/shipments/{shipment}/exceptions/{exception}/resolve', [ShipmentController::class, 'resolveException'])
        ->name('shipments.exceptions.resolve')
        ->middleware('role:admin,operator');

    // Carriers & Locations (Admin & Operator)
    Route::resource('carriers', CarrierController::class)->only(['index', 'store'])->middleware('role:admin,operator');
    Route::resource('locations', LocationController::class)->only(['index', 'store'])->middleware('role:admin,operator');

    // Audit Logs (Admin Only - UAS Brief slide 5)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->name('audit-logs.index')
        ->middleware('role:admin');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
