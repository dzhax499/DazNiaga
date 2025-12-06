<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoinController;
use App\Http\Controllers\TradingController;
use App\Http\Controllers\WalletController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Coins
    Route::get('/coins', [CoinController::class, 'index']);
    Route::post('/coins', [CoinController::class, 'store']);
    Route::get('/coins/{coin}', [CoinController::class, 'show']);
    Route::get('/my-coins', [CoinController::class, 'myCoins']);
    Route::post('/coins/{coin}/update-price', [CoinController::class, 'updatePrice']);

    // Trading
    Route::post('/positions', [TradingController::class, 'openPosition']);
    Route::get('/positions', [TradingController::class, 'getPositions']);
    Route::post('/positions/{position}/close', [TradingController::class, 'closePosition']);
    Route::get('/positions/update', [TradingController::class, 'updatePositions']);

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index']);
    Route::get('/wallet/balance', [WalletController::class, 'getBalance']);
});
