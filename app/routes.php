<?php

use App\Controllers\ApiController;
use App\Core\Route;

Route::post('/api/login', ApiController::class, 'login');
Route::post('/api/register', ApiController::class, 'register');
Route::patch('/api/profile/edit', ApiController::class, 'profileEdit');
