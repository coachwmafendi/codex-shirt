<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payments\ToyyibPayReturnController;
use App\Http\Controllers\Payments\ToyyibPayWebhookController;

use App\Http\Controllers\Payments\StripeSuccessController;
use App\Http\Controllers\Payments\StripeCancelController;
use App\Http\Controllers\Payments\StripeWebhookController;
use Illuminate\Support\Str;

// Route::view('/', 'welcome')->name('home');


Route::livewire('/', 'pages::landing')
    ->name('landing');

Route::livewire('/checkout', 'pages::checkout')
    ->name('checkout');

// Route::livewire('/thank-you/{order}', 'pages::thank-you')
    // ->name('thank-you');

    Route::livewire('/orders/{order:public_id}/payment-confirmation-order ', 'pages::thank-you')
    ->name('thank-you');

    Route::get('/payment/toyyibpay/return', ToyyibPayReturnController::class)
    ->name('payment.toyyibpay.return');

Route::post('/webhook/toyyibpay', ToyyibPayWebhookController::class)
    ->name('webhook.toyyibpay');

    Route::get('/payment/stripe/success', StripeSuccessController::class)
    ->name('payment.stripe.success');

Route::get('/payment/stripe/cancel/{order}', StripeCancelController::class)
    ->name('payment.stripe.cancel');

Route::post('/webhook/stripe', StripeWebhookController::class)
    ->name('webhook.stripe');
