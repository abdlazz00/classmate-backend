<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bot\FonnteWebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::post('/fonnte/webhook', [\App\Http\Controllers\Bot\FonnteWebhookController::class, 'receive']);
