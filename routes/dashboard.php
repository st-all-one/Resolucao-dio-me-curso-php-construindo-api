<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Tela somente leitura, sem sessão: registrada com o grupo `api` via then().
Route::get('/', DashboardController::class)->name('dashboard');
