<?php

/**
 * @copyright 2021 - N'Guessan Kouadio Elisée (eliseekn@gmail.com)
 * @license MIT (https://opensource.org/licenses/MIT)
 * @link https://github.com/eliseekn/tinymvc
 */

use Framework\Routing\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailController;
use App\Http\Controllers\Auth\PasswordController;

/**
 * Authentication routes
 */

Route::groupMiddlewares(['remember'], function () {
    Route::get('login', [AuthController::class, 'login']);
    Route::get('signup', [AuthController::class, 'signup']);
})->register();

Route::get('logout', [AuthController::class, 'logout'])->register();
Route::post('authenticate', [AuthController::class, 'authenticate'])->register();
Route::post('register', [AuthController::class, 'register'])->register();
Route::get('password/forgot', 'auth.password.forgot')->register();
Route::get('password/reset', [PasswordController::class, 'reset'])->register();
Route::post('password/notify', [PasswordController::class, 'notify'])->register();
Route::post('password/update', [PasswordController::class, 'update'])->register();

Route::groupPrefix('email', function () {
    Route::get('confirm', [EmailController::class, 'confirm']);
    Route::get('authenticate', [EmailController::class, 'authenticate']);
})->register();
