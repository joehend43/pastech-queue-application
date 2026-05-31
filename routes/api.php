<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/kasir', [ApiController::class, 'getKasir']);
Route::get('/display', [ApiController::class, 'getDisplay']);
Route::post('/login-user', [ApiController::class, 'loginUser']);
Route::post('/logout-user', [ApiController::class, 'logoutUser']);
Route::get('/queues', [ApiController::class, 'getQueues']);
Route::get('/queues/latest', [ApiController::class, 'getLatestQueues']);
Route::get('/queues/call-next', [ApiController::class, 'callNext']);
Route::get('/queues/call', [ApiController::class, 'callDynamic']);
Route::get('/queues/call/{id}', [ApiController::class, 'callQueue']);
Route::get('/queues/recall-current', [ApiController::class, 'recallCurrent']);
Route::get('/queues/count-remaining', [ApiController::class, 'countRemainingByType']);
Route::post('/queues/print-new', [ApiController::class, 'generateQueue']);
Route::post('/toggle-lock-kasir', [ApiController::class, 'toggleLockKasir']);
