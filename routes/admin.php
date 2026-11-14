<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminAssetController;
use App\Http\Controllers\Admin\AdminTradeController;
use App\Http\Controllers\Admin\AdminPairControlController;
use App\Http\Controllers\Admin\AdminGlobalControlController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\AdminKycController;
use App\Http\Controllers\Admin\AdminPaymentMethodController;
use App\Http\Controllers\Admin\AdminPaymentCategoryController;
use App\Http\Controllers\Admin\AdminSignalController;
use App\Http\Controllers\Admin\AdminTournamentController;
use App\Http\Controllers\Admin\AdminPromoCodeController;
use App\Http\Controllers\Admin\AdminReferralController;
use App\Http\Controllers\Admin\AdminEmailSettingsController;
use Illuminate\Support\Facades\Route;

// ─── Guest Admin Login Routes ─────────────────────────────────
Route::middleware(['web'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminController::class, 'loginSubmit'])->name('login.submit');
});

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // ─── Admin Profile & Settings ──────────────────────────────────
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');

    // ─── User Management ──────────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::post('/{user}/block', [AdminUserController::class, 'block'])->name('block');
        Route::post('/{user}/unblock', [AdminUserController::class, 'unblock'])->name('unblock');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/add-funds', [AdminUserController::class, 'addFunds'])->name('add-funds');
        Route::post('/{user}/login-as', [AdminUserController::class, 'loginAs'])->name('login-as');
    });

    // Return from impersonation
    Route::post('/return-admin', [AdminUserController::class, 'returnToAdmin'])->name('return-admin');

    // ─── KYC Verifications ─────────────────────────────────────────
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [AdminKycController::class, 'index'])->name('index');
        Route::get('/{kyc}', [AdminKycController::class, 'show'])->name('show');
        Route::post('/{kyc}/approve', [AdminKycController::class, 'approve'])->name('approve');
        Route::post('/{kyc}/reject', [AdminKycController::class, 'reject'])->name('reject');
    });

    // ─── Asset / Trading Pair Management ──────────────────────────
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AdminAssetController::class, 'index'])->name('index');
        Route::get('/create', [AdminAssetController::class, 'create'])->name('create');
        Route::post('/', [AdminAssetController::class, 'store'])->name('store');
        Route::get('/{asset}/edit', [AdminAssetController::class, 'edit'])->name('edit');
        Route::put('/{asset}', [AdminAssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [AdminAssetController::class, 'destroy'])->name('destroy');
        Route::get('/{asset}/control', [AdminAssetController::class, 'control'])->name('control');
        Route::post('/{asset}/control', [AdminAssetController::class, 'updateControl'])->name('control.update');
        Route::post('/{asset}/toggle', [AdminAssetController::class, 'toggle'])->name('toggle');
    });

    // ─── Live Trade Monitor ────────────────────────────────────────
    Route::prefix('live-trades')->name('live-trades.')->group(function () {
        Route::get('/', [AdminTradeController::class, 'index'])->name('index');
        Route::get('/data', [AdminTradeController::class, 'data'])->name('data');
        Route::post('/mode', [AdminTradeController::class, 'setMode'])->name('mode');
        Route::post('/{trade}/force-win', [AdminTradeController::class, 'forceWin'])->name('force-win');
        Route::post('/{trade}/force-loss', [AdminTradeController::class, 'forceLoss'])->name('force-loss');
        Route::post('/{trade}/reset-override', [AdminTradeController::class, 'resetOverride'])->name('reset-override');
    });

    // ─── Pair Trades Control (Market Manipulation) ────────────────
    Route::prefix('pair-control')->name('pair-control.')->group(function () {
        Route::get('/', [AdminPairControlController::class, 'index'])->name('index');
        Route::get('/data', [AdminPairControlController::class, 'data'])->name('data');
        Route::get('/monitor', [AdminPairControlController::class, 'monitor'])->name('monitor');
        Route::post('/{asset}/set-mode', [AdminPairControlController::class, 'setMode'])->name('set-mode');
        Route::post('/{asset}/reset', [AdminPairControlController::class, 'resetMode'])->name('reset');
    });

    Route::prefix('global-control')->name('global-control.')->group(function () {
        Route::get('/', [AdminGlobalControlController::class, 'show'])->name('show');
        Route::post('/', [AdminGlobalControlController::class, 'update'])->name('update');
    });

    // ─── Transactions ──────────────────────────────────────────────
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/deposits', [AdminTransactionController::class, 'deposits'])->name('deposits');
        Route::get('/withdrawals', [AdminTransactionController::class, 'withdrawals'])->name('withdrawals');
        Route::get('/all', [AdminTransactionController::class, 'all'])->name('all');
        Route::post('/{transaction}/approve', [AdminTransactionController::class, 'approve'])->name('approve');
        Route::post('/{transaction}/approve-with-mail', [AdminTransactionController::class, 'approveWithMail'])->name('approve-with-mail');
        Route::post('/{transaction}/reject', [AdminTransactionController::class, 'reject'])->name('reject');
        Route::post('/{transaction}/reject-with-mail', [AdminTransactionController::class, 'rejectWithMail'])->name('reject-with-mail');
    });

    // ─── Payment Methods Management ───────────────────────────────
    Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
        Route::get('/', [AdminPaymentMethodController::class, 'index'])->name('index');
        Route::get('/create', [AdminPaymentMethodController::class, 'create'])->name('create');
        Route::post('/', [AdminPaymentMethodController::class, 'store'])->name('store');
        Route::post('/lgpay/settings', [AdminPaymentMethodController::class, 'updateLGPay'])->name('lgpay.update');
        Route::get('/{paymentMethod}/edit', [AdminPaymentMethodController::class, 'edit'])->name('edit');
        Route::put('/{paymentMethod}', [AdminPaymentMethodController::class, 'update'])->name('update');
        Route::delete('/{paymentMethod}', [AdminPaymentMethodController::class, 'destroy'])->name('destroy');
        Route::post('/{paymentMethod}/toggle', [AdminPaymentMethodController::class, 'toggle'])->name('toggle');
    });

    Route::prefix('payment-categories')->name('payment-categories.')->group(function () {
        Route::get('/', [AdminPaymentCategoryController::class, 'index'])->name('index');
        Route::get('/create', [AdminPaymentCategoryController::class, 'create'])->name('create');
        Route::post('/', [AdminPaymentCategoryController::class, 'store'])->name('store');
        Route::get('/{paymentCategory}/edit', [AdminPaymentCategoryController::class, 'edit'])->name('edit');
        Route::put('/{paymentCategory}', [AdminPaymentCategoryController::class, 'update'])->name('update');
        Route::delete('/{paymentCategory}', [AdminPaymentCategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{paymentCategory}/toggle', [AdminPaymentCategoryController::class, 'toggle'])->name('toggle');
    });

    // ─── Trading Signals Management ────────────────────────────────
    Route::prefix('signals')->name('signals.')->group(function () {
        Route::get('/', [AdminSignalController::class, 'index'])->name('index');
        Route::get('/create', [AdminSignalController::class, 'create'])->name('create');
        Route::post('/', [AdminSignalController::class, 'store'])->name('store');
        Route::delete('/{signal}', [AdminSignalController::class, 'destroy'])->name('destroy');
        Route::post('/{signal}/toggle', [AdminSignalController::class, 'toggle'])->name('toggle');
    });

    // ─── Tournaments Management ────────────────────────────────────
    Route::prefix('tournaments')->name('tournaments.')->group(function () {
        Route::get('/', [AdminTournamentController::class, 'index'])->name('index');
        Route::get('/create', [AdminTournamentController::class, 'create'])->name('create');
        Route::post('/', [AdminTournamentController::class, 'store'])->name('store');
        Route::get('/{tournament}/edit', [AdminTournamentController::class, 'edit'])->name('edit');
        Route::get('/{tournament}', [AdminTournamentController::class, 'show'])->name('show');
        Route::put('/{tournament}', [AdminTournamentController::class, 'update'])->name('update');
        Route::delete('/{tournament}', [AdminTournamentController::class, 'destroy'])->name('destroy');
        Route::post('/{tournament}/toggle', [AdminTournamentController::class, 'toggle'])->name('toggle');
    });

    // ─── Market Promo Codes Management ────────────────────────────
    Route::prefix('promo-codes')->name('promo-codes.')->group(function () {
        Route::get('/', [AdminPromoCodeController::class, 'index'])->name('index');
        Route::get('/create', [AdminPromoCodeController::class, 'create'])->name('create');
        Route::post('/', [AdminPromoCodeController::class, 'store'])->name('store');
        Route::get('/{promoCode}/edit', [AdminPromoCodeController::class, 'edit'])->name('edit');
        Route::put('/{promoCode}', [AdminPromoCodeController::class, 'update'])->name('update');
        Route::delete('/{promoCode}', [AdminPromoCodeController::class, 'destroy'])->name('destroy');
        Route::post('/{promoCode}/toggle', [AdminPromoCodeController::class, 'toggle'])->name('toggle');
    });

    // ─── Referral & Affiliate Management ─────────────────────────
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [AdminReferralController::class, 'index'])->name('index');
        Route::get('/settings', [AdminReferralController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminReferralController::class, 'updateSettings'])->name('settings.update');
        Route::get('/user/{user}', [AdminReferralController::class, 'userReferrals'])->name('user');
    });

    // ─── Admin Logs ────────────────────────────────────────────────
    Route::get('/email-settings', [AdminEmailSettingsController::class, 'edit'])->name('email-settings.edit');
    Route::post('/email-settings', [AdminEmailSettingsController::class, 'update'])->name('email-settings.update');
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
});
