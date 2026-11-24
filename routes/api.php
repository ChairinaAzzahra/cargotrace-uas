<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierApiController;
use App\Http\Controllers\Api\ExceptionApiController;
use App\Http\Controllers\Api\LocationApiController;
use App\Http\Controllers\Api\ShipmentApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CargoTrace REST API Routes (UAS Brief Slide 12 & 13)
|--------------------------------------------------------------------------
*/

// Public Authentication Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected API Endpoints (Requires Laravel Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Shipments Resource
    Route::get('/shipments', [ShipmentApiController::class, 'index']);
    Route::post('/shipments', [ShipmentApiController::class, 'store'])->middleware('role:admin,operator');
    Route::get('/shipments/{id}', [ShipmentApiController::class, 'show']);
    Route::match(['put', 'patch'], '/shipments/{id}', [ShipmentApiController::class, 'update'])->middleware('role:admin,operator');
    Route::delete('/shipments/{id}', [ShipmentApiController::class, 'destroy'])->middleware('role:admin');

    // Lifecycle Status Transition
    Route::post('/shipments/{id}/transition', [ShipmentApiController::class, 'transition'])->middleware('role:admin,operator');

    // Tracking Events
    Route::get('/shipments/{id}/tracking', [ShipmentApiController::class, 'tracking']);
    Route::post('/shipments/{id}/tracking', [ShipmentApiController::class, 'addTracking'])->middleware('role:admin,operator');

    // Carriers & Locations
    Route::get('/carriers', [CarrierApiController::class, 'index']);
    Route::post('/carriers', [CarrierApiController::class, 'store'])->middleware('role:admin');
    Route::get('/locations', [LocationApiController::class, 'index']);
    Route::post('/locations', [LocationApiController::class, 'store'])->middleware('role:admin');

    // Shipment Exceptions
    Route::get('/shipments/{id}/exceptions', [ExceptionApiController::class, 'indexForShipment']);
    Route::post('/shipments/{id}/exceptions', [ExceptionApiController::class, 'store'])->middleware('role:admin,operator');
    Route::patch('/exceptions/{id}', [ExceptionApiController::class, 'resolve'])->middleware('role:admin,operator');
});
