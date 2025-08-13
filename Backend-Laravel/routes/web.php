<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\Api\AppController;
use Fruitcake\Cors\HandleCors;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/import-excel', [ExcelImportController::class, 'showForm']);
Route::post('/import-excel', [ExcelImportController::class, 'import']);

Route::prefix('api')->group(function() {
    // Route API sudah dipindah ke routes/api.php
});
