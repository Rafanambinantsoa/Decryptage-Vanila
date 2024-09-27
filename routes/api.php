<?php

use App\Http\Controllers\DecryptController;
use App\Http\Controllers\PaymentController;
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
Route::post('/decrypt',[DecryptController::class , 'initiatePayment']);
Route::post('/pay',[PaymentController::class , 'initPayment']);
Route::get('/success',[PaymentController::class , 'success']);
Route::get('/echec',[PaymentController::class , 'failed']);
Route::get('/retour',[PaymentController::class , 'failed']);

