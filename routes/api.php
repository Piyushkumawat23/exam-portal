<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\PaymentController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:api')->get('/me', [AuthController::class, 'me']);
});

Route::middleware('auth:api')->group(function () {
    
    // Forms
    Route::get('/forms', [FormController::class, 'index']);
    Route::get('/forms/{id}', [FormController::class, 'show']);
    Route::post('/forms', [FormController::class, 'store'])->middleware('admin');
    Route::put('/forms/{id}', [FormController::class, 'update'])->middleware('admin');
    Route::delete('/forms/{id}', [FormController::class, 'destroy'])->middleware('admin');
    Route::get('/forms/{id}/applicants', [FormController::class, 'getApplicants'])->middleware('admin');
    // Submissions
    Route::post('/submissions', [SubmissionController::class, 'store']);
    Route::get('/submissions/{id}', [SubmissionController::class, 'show']);
    Route::get('/user/submissions', [SubmissionController::class, 'userSubmissions']);

    // Payments
    Route::post('/payments/create-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment']);
    Route::get('/payments/receipt/{id}', [PaymentController::class, 'downloadReceipt']);
    Route::post('/payments/fail', [PaymentController::class, 'markFailed']);

});
