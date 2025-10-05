<?php
use Illuminate\Support\Facades\Route;
use App\Plugins\PhpIpam\Controllers\ApiController;

Route::middleware(['auth'])->prefix('phpipam-plugin')->group(function () {
    Route::get('/', [ApiController::class, 'index']);
    Route::get('/test', [ApiController::class, 'test']);
    Route::post('/sync', [ApiController::class, 'sync']);
});
