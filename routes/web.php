<?php

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/products', [WelcomeController::class, 'showAllProducts'])->name('products.index');

Route::get('/products/{id}', [WelcomeController::class, 'showProduct'])->name('products.show');
