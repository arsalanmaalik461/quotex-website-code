<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppDownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\LGPayController;
use Illuminate\Support\Facades\Route;

// TEMP: One-time admin creation route - DELETE AFTER USE

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/landing', [HomeController::class, 'index'])->name('landing');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/blog', [HomeController::class, 'blog'])->name('blog');
Route::get('/download/app', fn() => redirect('/'))->name('app.download'); // APK download disabled - silent redirect

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/sign-in', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/sign-in', [AuthController::class, 'login']);
    Route::get('/sign-up', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/sign-up', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::match(['get', 'post'], '/deposit/lgpay/callback', [LGPayController::class, 'callback'])->name('lgpay.callback');

Route::middleware('auth')->group(function () {
    // Trading terminal
    Route::get('/trade', [TradeController::class, 'index'])->name('trade');
    Route::get('/trading', [TradeController::class, 'index'])->name('trade.index');
    Route::get('/en/trade', [TradeController::class, 'index'])->name('trade.en.trade');

    // Trade AJAX actions
    Route::post('/trade/place', [TradeController::class, 'placeTrade'])->name('trade.place');
    Route::post('/trade/settle/{trade}', [TradeController::class, 'settleTrade'])->name('trade.settle');
    Route::post('/trade/switch-account', [TradeController::class, 'switchAccount'])->name('trade.switch');
    Route::post('/trade/refill-demo', [TradeController::class, 'refillDemo'])->name('trade.refill');
});
Route::get('/trade/price-data', [TradeController::class, 'priceData'])->name('trade.price');
Route::get('/trade/signals', [\App\Http\Controllers\SignalController::class, 'getSignals'])->name('trade.signals');

Route::middleware('auth')->group(function () {
    // Account management & Quotex exact screens
    Route::get('/deposit', [AccountController::class, 'deposit'])->name('deposit');
    Route::get('/en/balance', [AccountController::class, 'deposit'])->name('balance.en');
    Route::post('/deposit', [AccountController::class, 'processDeposit'])->name('deposit.process');
    Route::post('/deposit/lgpay', [LGPayController::class, 'initiate'])->name('lgpay.initiate');
    Route::get('/deposit/lgpay/return/{transaction}', [LGPayController::class, 'paymentReturn'])->name('lgpay.return');

    Route::get('/withdrawal', [AccountController::class, 'withdrawal'])->name('withdrawal');
    Route::get('/en/withdrawal', [AccountController::class, 'withdrawal'])->name('withdrawal.en');
    Route::post('/withdrawal', [AccountController::class, 'processWithdrawal'])->name('withdrawal.process');

    Route::get('/history', [AccountController::class, 'history'])->name('history');
    Route::get('/trades', [AccountController::class, 'history'])->name('trades');
    Route::get('/en/trades', [AccountController::class, 'history'])->name('trades.en');
    Route::get('/pending-trades', [AccountController::class, 'pendingTrades'])->name('pending-trades');
    Route::get('/en/pending-trades', [AccountController::class, 'pendingTrades'])->name('pending-trades.en');

    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::get('/settings', [AccountController::class, 'profile'])->name('settings');
    Route::get('/en/settings', [AccountController::class, 'profile'])->name('settings.en');
    Route::post('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
});
Route::get('/profile/images/{any}', function ($any) {
    $path = public_path('images/' . $any);
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
})->where('any', '.*');

Route::middleware('auth')->group(function () {
    Route::get('/market', [AccountController::class, 'market'])->name('market');
    Route::get('/en/market', [AccountController::class, 'market'])->name('market.en');
    Route::post('/market/apply', [AccountController::class, 'applyPromo'])->name('market.apply');
    Route::post('/en/market/apply', [AccountController::class, 'applyPromo'])->name('market.apply.en');

    Route::get('/tournaments', [AccountController::class, 'tournaments'])->name('tournaments');
    Route::get('/en/tournaments', [AccountController::class, 'tournaments'])->name('tournaments.en');
    Route::get('/tournament/{tournament}', [AccountController::class, 'tournamentDetail'])->name('tournament.detail');
    Route::get('/en/tournament/{tournament}', [AccountController::class, 'tournamentDetail'])->name('tournament.detail.en');

    Route::get('/analytics', [AccountController::class, 'analytics'])->name('analytics');
    Route::get('/en/analytics', [AccountController::class, 'analytics'])->name('analytics.en');

    // KYC Verification routes
    Route::get('/kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::get('/en/kyc', [KycController::class, 'index'])->name('kyc.en');
    Route::post('/kyc', [KycController::class, 'submit'])->name('kyc.submit');

    // Referral & Affiliate routes
    Route::get('/referral', [AccountController::class, 'referral'])->name('referral');
    Route::get('/en/referral', [AccountController::class, 'referral'])->name('referral.en');
    Route::get('/affiliate', [AccountController::class, 'referral'])->name('affiliate');
    Route::get('/en/affiliate', [AccountController::class, 'referral'])->name('affiliate.en');
});

