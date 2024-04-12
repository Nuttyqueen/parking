<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FindController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\ReportController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('cards', [FindController::class, 'getAllCards']);
Route::get('findParking', [FindController::class, 'getFindParking']);
Route::get('detailParkingLot', [FindController::class, 'getDetailParkingLot']);

Route::post('checkIn', [CheckInController::class, 'checkIn']);
Route::post('checkOut', [CheckOutController::class, 'checkOut']);

Route::post('calculateParking', [CheckOutController::class, 'calculateParking']);

/* Route::get('/report/{parkingLotId}', [ReportController::class, 'report']); */
Route::get('report', [ReportController::class, 'report']);
