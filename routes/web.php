<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppDownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\PendingOrderController;
use App\Http\Controllers\LGPayController;
use App\Http\Controllers\TournamentRegistrationController;
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

    // Pending orders (real By Price / By Time orders — Workstream D).
    // Creating an order only records the intent; execution happens via
    // `pending:execute` (host cron, every minute) or the opportunistic
    // check-due hook below, through the normal trade placement path.
    Route::post('/pending-orders', [PendingOrderController::class, 'store'])->name('pending-orders.store');
    Route::get('/pending-orders', [PendingOrderController::class, 'index'])->name('pending-orders.index');
    Route::post('/pending-orders/check-due', [PendingOrderController::class, 'checkDue'])->name('pending-orders.check-due');
    Route::delete('/pending-orders/{pendingOrder}', [PendingOrderController::class, 'cancel'])->name('pending-orders.cancel');
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

    // Unified money history (all transaction types in one list: deposits,
    // withdrawals, promo bonuses, tournament entries, ...)
    Route::get('/transactions', [AccountController::class, 'transactions'])->name('transactions.index');

    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::get('/settings', [AccountController::class, 'profile'])->name('settings');
    Route::get('/en/settings', [AccountController::class, 'profile'])->name('settings.en');
    Route::post('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile', [AccountController::class, 'deleteAccount'])->name('profile.delete');
    Route::post('/profile/2fa', [AccountController::class, 'updateTwoFactor'])->name('profile.two-factor');
    Route::post('/profile/resend-verification', [AccountController::class, 'resendVerificationEmail'])->name('profile.resend-verification');
});
Route::get('/email/verify/{id}/{hash}', [AccountController::class, 'verifyEmail'])->name('verification.verify')->middleware('signed');
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
    Route::post('/market/purchase', [AccountController::class, 'purchasePromo'])->name('market.purchase');
    Route::post('/en/market/purchase', [AccountController::class, 'purchasePromo'])->name('market.purchase.en');

    Route::get('/tournaments', [AccountController::class, 'tournaments'])->name('tournaments');
    Route::get('/en/tournaments', [AccountController::class, 'tournaments'])->name('tournaments.en');
    Route::get('/tournament/{tournament}', [AccountController::class, 'tournamentDetail'])->name('tournament.detail');
    Route::get('/en/tournament/{tournament}', [AccountController::class, 'tournamentDetail'])->name('tournament.detail.en');
    Route::post('/tournament/{tournament}/register', [TournamentRegistrationController::class, 'register'])->name('tournament.register');

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

