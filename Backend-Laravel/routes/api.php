<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppController;

Route::get('/aplikasi', [AppController::class, 'index']);
Route::get('/apps/{id}/detail', [AppController::class, 'detail']);
Route::post('/aplikasi', [AppController::class, 'store']);
Route::put('/aplikasi/{id}', [AppController::class, 'update']);
Route::patch('/aplikasi/{id}', [AppController::class, 'update']);
Route::delete('/aplikasi/{id}', [AppController::class, 'destroy']); 