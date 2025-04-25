<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

// Test route
Route::get('test', function () {
    \Log::info('Test route accessed');
    return response()->json(['message' => 'API is working']);
});

// Auth routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

// Task routes
Route::middleware('auth:api')->group(function () {
    Route::resource('tasks', TaskController::class);
});