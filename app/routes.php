<?php

use App\Controllers\ApiController;
use App\Core\Route;

Route::add('/api/login', ApiController::class, 'login');
Route::add('/api/register', ApiController::class, 'register');
