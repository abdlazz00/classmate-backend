<?php

use App\Http\Controllers\Api\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login',[\App\Http\Controllers\Api\AuthController::class,'login']);
Route::middleware('auth:sanctum')->prefix('student')->group(function() {
    Route::get('/dashboard', [StudentController::class, 'dashboard']);
    Route::get('/schedules', [StudentController::class, 'schedules']);
    Route::get('/assignments', [StudentController::class, 'assignments']);
    Route::get('/materials', [StudentController::class, 'materials']);
    Route::get('/announcements', [StudentController::class, 'announcements']);
    Route::get('/material/download/{mediaId}', [StudentController::class, 'download'])
    ->name('api.student.material.download');
});
