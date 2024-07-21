<?php

use Illuminate\Support\Facades\Route;

Route::post('/payment/{order}', [\App\Http\Controllers\OrderController::class, 'orderPayment']);