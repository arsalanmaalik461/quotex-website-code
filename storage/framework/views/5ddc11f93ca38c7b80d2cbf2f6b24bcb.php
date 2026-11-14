<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Admin Panel'); ?> — TradePro Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0a0e1a;
            --bg-secondary: #0f1629;
            --bg-card: #131929;
            --bg-hover: #1a2235;
            --border: #1e2d47;
            --accent: #4f8ef7;
            --accent-glow: rgba(79,142,247,0.15);
            --green: #00d17a;
            --red: #ff4c6a;
            --orange: #f59e0b;
            --text-primary: #e8edf5;
            --text-secondary: #7a8aaa;
            --text-muted: #3d4f6e;
            --sidebar-width: 260px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--bg-primary); color:var(--text-primary); display:flex; min-height:100vh; }

        /* ── Sidebar ─────────────────────────── */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-secondary);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top:0; left:0; height:100vh;
            overflow-y: auto;
            z-index: 100;
        }
        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-logo .logo-icon {
            width:36px; height:36px;
            background: linear-gradient(135deg, #4f8ef7, #7b5ea7);
            border-radius: 10px;
            display: flex; align-items:center; justify-content:center;
            font-size: 18px; font-weight:700;
        }
        .sidebar-logo .logo-text { font-size:16px; font-weight:700; color:var(--text-primary); }
        .sidebar-logo .logo-sub  { font-size:11px; color:var(--text-secondary); }

        .sidebar-section {
            padding: 8px 12px;
            margin-top: 8px;
        }
        .sidebar-section-title {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 8px 8px 4px;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background: var(--bg-hover);
            color: var(--text-primary);
        }
        .sidebar-nav a.active { background: var(--accent-glow); color: var(--accent); }
        .sidebar-nav a .nav-icon { font-size:16px; width:20px; text-align:center; }
        .sidebar-nav a .badge {
            margin-left: auto;
            background: var(--red);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 10px;
        }

        /* ── Main content ─────────────────────── */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Topbar ──────────────────────────── */
        .topbar {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        .topbar-title { font-size:15px; font-weight:600; color:var(--text-primary); }
        .topbar-right { display:flex; align-items:center; gap:16px; }
        .admin-badge {
            font-size:12px;
            background: var(--accent-glow);
            color: var(--accent);
            border: 1px solid rgba(79,142,247,0.3);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight:600;
        }
        .topbar-logout {
            font-size:12px; color:var(--text-secondary);
            text-decoration:none; padding:6px 12px;
            border-radius:6px; border:1px solid var(--border);
            transition:.15s;
        }
        .topbar-logout:hover { color:var(--red); border-color:var(--red); }

        /* ── Impersonation banner ─────────────── */
        .impersonation-banner {
            background: linear-gradient(90deg, rgba(245,158,11,0.15), rgba(245,158,11,0.05));
            border-bottom: 1px solid rgba(245,158,11,0.3);
            padding: 10px 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: var(--orange);
        }
        .impersonation-banner form { margin-left:auto; }
        .impersonation-banner button {
            background: var(--orange);
            color: #000;
            border:none;
            padding: 6px 14px;
            border-radius:6px;
            font-size:12px;
            font-weight:700;
            cursor:pointer;
        }

        /* ── Page content ────────────────────── */
        .page-content { padding:28px; flex:1; }

        /* ── Cards ───────────────────────────── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        }
        .card-header {
            display:flex; align-items:center; justify-content:space-between;
            margin-bottom: 16px;
        }
        .card-title { font-size:14px; font-weight:600; color:var(--text-primary); }

        /* ── Stats grid ───────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px 20px;
            transition:.2s;
        }
        .stat-card:hover { border-color: var(--accent); transform:translateY(-1px); }
        .stat-label { font-size:12px; color:var(--text-secondary); margin-bottom:6px; }
        .stat-value { font-size:24px; font-weight:700; color:var(--text-primary); }
        .stat-value.green { color:var(--green); }
        .stat-value.red   { color:var(--red); }
        .stat-value.orange{ color:var(--orange); }
        .stat-sub { font-size:11px; color:var(--text-muted); margin-top:4px; }

        /* ── Tables ───────────────────────────── */
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        th { padding:10px 14px; text-align:left; color:var(--text-muted); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--border); }
        td { padding:12px 14px; border-bottom:1px solid rgba(30,45,71,0.5); color:var(--text-secondary); vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:rgba(255,255,255,0.02); color:var(--text-primary); }

        /* ── Badges ───────────────────────────── */
        .badge-status {
            display:inline-flex; align-items:center; gap:4px;
            padding: 3px 9px; border-radius:20px;
            font-size:11px; font-weight:600;
        }
        .badge-status.green  { background:rgba(0,209,122,0.1); color:var(--green); }
        .badge-status.red    { background:rgba(255,76,106,0.1); color:var(--red); }
        .badge-status.orange { background:rgba(245,158,11,0.1); color:var(--orange); }
        .badge-status.blue   { background:rgba(79,142,247,0.1); color:var(--accent); }
        .badge-status.gray   { background:rgba(122,138,170,0.1); color:var(--text-secondary); }

        /* ── Buttons ──────────────────────────── */
        .btn {
            display:inline-flex; align-items:center; gap:6px;
            padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600;
            text-decoration:none; border:none; cursor:pointer; transition:.15s;
        }
        .btn-primary { background:var(--accent); color:#fff; }
        .btn-primary:hover { background:#3d7de8; }
        .btn-danger  { background:rgba(255,76,106,0.15); color:var(--red); border:1px solid rgba(255,76,106,0.3); }
        .btn-danger:hover { background:rgba(255,76,106,0.25); }
        .btn-success { background:rgba(0,209,122,0.15); color:var(--green); border:1px solid rgba(0,209,122,0.3); }
        .btn-success:hover { background:rgba(0,209,122,0.25); }
        .btn-warning { background:rgba(245,158,11,0.15); color:var(--orange); border:1px solid rgba(245,158,11,0.3); }
        .btn-warning:hover { background:rgba(245,158,11,0.25); }
        .btn-ghost   { background:transparent; color:var(--text-secondary); border:1px solid var(--border); }
        .btn-ghost:hover { color:var(--text-primary); border-color:var(--text-secondary); }
        .btn-sm { padding:5px 10px; font-size:12px; }

        /* ── Forms ────────────────────────────── */
        .form-group { margin-bottom:16px; }
        .form-label { display:block; font-size:12px; font-weight:600; color:var(--text-secondary); margin-bottom:6px; }
        .form-control {
            width:100%; padding:10px 14px;
            background:var(--bg-primary); border:1px solid var(--border);
            border-radius:8px; color:var(--text-primary); font-size:13px;
            font-family:'Inter',sans-serif;
            transition:.15s;
        }
        .form-control:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); }
        .form-control option { background:var(--bg-primary); }
        select.form-control { cursor:pointer; }

        /* ── Alerts ───────────────────────────── */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            display:flex; align-items:center; gap:8px;
        }
        .alert-success { background:rgba(0,209,122,0.1); border:1px solid rgba(0,209,122,0.3); color:var(--green); }
        .alert-error   { background:rgba(255,76,106,0.1); border:1px solid rgba(255,76,106,0.3); color:var(--red); }
        .alert-info    { background:var(--accent-glow); border:1px solid rgba(79,142,247,0.3); color:var(--accent); }
        .alert-warning { background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3); color:var(--orange); }

        /* ── Utilities ────────────────────────── */
        .flex { display:flex; }
        .items-center { align-items:center; }
        .justify-between { justify-content:space-between; }
        .gap-2 { gap:8px; }
        .gap-3 { gap:12px; }
        .mt-4 { margin-top:16px; }
        .mb-4 { margin-bottom:16px; }
        .text-muted { color:var(--text-muted); font-size:12px; }
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; }

        /* Pagination */
        .pagination { display:flex; gap:4px; list-style:none; margin-top:16px; justify-content:center; }
        .pagination li a, .pagination li span { display:block; padding:6px 12px; border-radius:6px; background:var(--bg-card); border:1px solid var(--border); color:var(--text-secondary); font-size:13px; text-decoration:none; }
        .pagination li.active span { background:var(--accent); border-color:var(--accent); color:#fff; }
        .pagination li a:hover { border-color:var(--accent); color:var(--accent); }
        .page-content nav[role="navigation"][aria-label*="Pagination"] {
            margin-top: 16px;
            color: var(--text-secondary);
            font-size: 13px;
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] > div:first-child {
            display: none;
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] > div:last-child {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] p {
            color: var(--text-muted);
            font-size: 12px;
            margin: 0;
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] span[aria-current="page"] span,
        .page-content nav[role="navigation"][aria-label*="Pagination"] a,
        .page-content nav[role="navigation"][aria-label*="Pagination"] span[aria-disabled="true"] span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text-secondary);
            line-height: 1;
            text-decoration: none;
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] a:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] span[aria-current="page"] span {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] span[aria-disabled="true"] span {
            opacity: .45;
            cursor: not-allowed;
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] svg {
            width: 16px;
            height: 16px;
            max-width: 16px;
            max-height: 16px;
            display: block;
            flex: 0 0 16px;
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] .flex-1,
        .page-content nav[role="navigation"][aria-label*="Pagination"] .sm\:flex-1 {
            flex: 0 0 auto;
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] .relative {
            position: static;
        }
        .page-content nav[role="navigation"][aria-label*="Pagination"] .shadow-sm {
            box-shadow: none;
        }

        /* ── Mobile Responsive Enhancements ─── */
        .mobile-toggle {
            display: none;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-primary);
            padding: 7px 9px;
            border-radius: 8px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .mobile-toggle:hover {
            background: var(--bg-hover);
            border-color: var(--accent);
        }
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .sidebar-backdrop.active {
            display: block;
            opacity: 1;
        }
        .topbar-profile-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-primary);
            background: var(--bg-card);
            border: 1px solid var(--border);
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.15s;
        }
        .topbar-profile-link:hover, .topbar-profile-link.active {
            border-color: var(--accent);
            color: var(--accent);
            background: var(--accent-glow);
        }

        @media(max-width:900px){
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1000;
                box-shadow: none;
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 0 0 50px rgba(0,0,0,0.85);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                min-width: 0;
            }
            .mobile-toggle {
                display: flex;
            }
            .topbar {
                padding: 0 16px;
                height: 56px;
            }
            .topbar-title {
                font-size: 14px;
            }
            .profile-name {
                display: none;
            }
            .page-content {
                padding: 16px 12px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                margin-bottom: 16px;
            }
            .stat-card {
                padding: 12px 14px;
            }
            .stat-value {
                font-size: 18px;
            }
            .grid-2, .grid-3 {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .card {
                padding: 14px;
            }
            .card-header {
                margin-bottom: 12px;
                flex-wrap: wrap;
                gap: 8px;
            }
            .table-wrap {
                margin: 0 -14px;
                padding: 0 14px;
                -webkit-overflow-scrolling: touch;
            }
            table {
                min-width: 600px;
            }
        }

        @media(max-width:480px){
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .topbar-logout {
                padding: 4px 8px;
                font-size: 11px;
            }
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>


<div class="sidebar-backdrop" id="adminSidebarBackdrop"></div>


<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="<?php echo e(route('admin.dashboard')); ?>" style="display:flex;align-items:center;text-decoration:none;">
            <img src="/profile/images/logo-dark.svg" alt="Quotex" height="24" style="height:24px;width:auto;display:block;">
        </a>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Overview</div>
        <nav class="sidebar-nav">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="<?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                <span class="nav-icon">⚡</span> Dashboard
            </a>
        </nav>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Users</div>
        <nav class="sidebar-nav">
            <a href="<?php echo e(route('admin.users.index')); ?>" class="<?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
                <span class="nav-icon">👥</span> Manage Users
            </a>
            <a href="<?php echo e(route('admin.kyc.index')); ?>" class="<?php echo e(request()->routeIs('admin.kyc.*') ? 'active' : ''); ?>">
                <span class="nav-icon">🪪</span> KYC Verifications
                <?php $pendKyc = \Illuminate\Support\Facades\Schema::hasTable('kyc_verifications') ? \App\Models\KycVerification::where('status', 'pending')->count() : 0; ?>
                <?php if($pendKyc > 0): ?>
                    <span class="badge" style="background:var(--orange); color:#000;"><?php echo e($pendKyc); ?></span>
                <?php endif; ?>
            </a>
        </nav>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Trading</div>
        <nav class="sidebar-nav">
            <a href="<?php echo e(route('admin.assets.index')); ?>" class="<?php echo e(request()->routeIs('admin.assets.*') ? 'active' : ''); ?>">
                <span class="nav-icon">📊</span> Trading Pairs
            </a>
            <a href="<?php echo e(route('admin.signals.index')); ?>" class="<?php echo e(request()->routeIs('admin.signals.*') ? 'active' : ''); ?>">
                <span class="nav-icon">📡</span> Trading Signals
                <?php $activeSigCount = \Illuminate\Support\Facades\Schema::hasTable('signals') ? \App\Models\Signal::where('is_active', true)->where('expires_at', '>', now())->count() : 0; ?>
                <?php if($activeSigCount > 0): ?>
                    <span class="badge" style="background:var(--green); color:#000;"><?php echo e($activeSigCount); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo e(route('admin.live-trades.index')); ?>" class="<?php echo e(request()->routeIs('admin.live-trades.*') ? 'active' : ''); ?>">
                <span class="nav-icon">🔴</span> Live Trades
                <?php $activeTrades = \App\Models\Trade::where('result','pending')->count(); ?>
                <?php if($activeTrades > 0): ?>
                    <span class="badge"><?php echo e($activeTrades); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo e(route('admin.pair-control.index')); ?>" class="<?php echo e(request()->routeIs('admin.pair-control.*') ? 'active' : ''); ?>">
                <span class="nav-icon">MC</span> Market Control
            </a>
            <a href="<?php echo e(route('admin.tournaments.index')); ?>" class="<?php echo e(request()->routeIs('admin.tournaments.*') ? 'active' : ''); ?>">
                <span class="nav-icon">🏆</span> Tournaments
                <?php $activeTournCount = \Illuminate\Support\Facades\Schema::hasTable('tournaments') ? \App\Models\Tournament::where('status', 'active')->count() : 0; ?>
                <?php if($activeTournCount > 0): ?>
                    <span class="badge" style="background:#f59e0b; color:#000;"><?php echo e($activeTournCount); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo e(route('admin.promo-codes.index')); ?>" class="<?php echo e(request()->routeIs('admin.promo-codes.*') ? 'active' : ''); ?>">
                <span class="nav-icon">🎁</span> Promo Codes
                <?php $activePromoCount = \Illuminate\Support\Facades\Schema::hasTable('promo_codes') ? \App\Models\PromoCode::where('is_active', true)->count() : 0; ?>
                <?php if($activePromoCount > 0): ?>
                    <span class="badge" style="background:var(--green); color:#000;"><?php echo e($activePromoCount); ?></span>
                <?php endif; ?>
            </a>
        </nav>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Finance</div>
        <nav class="sidebar-nav">
            <a href="<?php echo e(route('admin.transactions.deposits')); ?>" class="<?php echo e(request()->routeIs('admin.transactions.deposits') ? 'active' : ''); ?>">
                <span class="nav-icon">💰</span> Deposits
                <?php $pendDep = \App\Models\Transaction::where('type','deposit')->where('status','pending')->count(); ?>
                <?php if($pendDep > 0): ?> <span class="badge"><?php echo e($pendDep); ?></span> <?php endif; ?>
            </a>
            <a href="<?php echo e(route('admin.transactions.withdrawals')); ?>" class="<?php echo e(request()->routeIs('admin.transactions.withdrawals') ? 'active' : ''); ?>">
                <span class="nav-icon">🏦</span> Withdrawals
                <?php $pendWith = \App\Models\Transaction::where('type','withdrawal')->where('status','pending')->count(); ?>
                <?php if($pendWith > 0): ?> <span class="badge"><?php echo e($pendWith); ?></span> <?php endif; ?>
            </a>
            <a href="<?php echo e(route('admin.transactions.all')); ?>" class="<?php echo e(request()->routeIs('admin.transactions.all') ? 'active' : ''); ?>">
                <span class="nav-icon">📋</span> All Transactions
            </a>
            <a href="<?php echo e(route('admin.payment-methods.index')); ?>" class="<?php echo e(request()->routeIs('admin.payment-methods.*') ? 'active' : ''); ?>">
                <span class="nav-icon">💳</span> Payment Methods
            </a>
            <a href="<?php echo e(route('admin.payment-categories.index')); ?>" class="<?php echo e(request()->routeIs('admin.payment-categories.*') ? 'active' : ''); ?>">
                <span class="nav-icon">▦</span> Payment Categories
            </a>
        </nav>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Affiliate Program</div>
        <nav class="sidebar-nav">
            <a href="<?php echo e(route('admin.referrals.index')); ?>" class="<?php echo e(request()->routeIs('admin.referrals.index') || request()->routeIs('admin.referrals.user') ? 'active' : ''); ?>">
                <span class="nav-icon">🤝</span> Referral Overview
            </a>
            <a href="<?php echo e(route('admin.referrals.settings')); ?>" class="<?php echo e(request()->routeIs('admin.referrals.settings') ? 'active' : ''); ?>">
                <span class="nav-icon">⚙️</span> Commission Settings
            </a>
        </nav>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">System</div>
        <nav class="sidebar-nav">
            <a href="<?php echo e(route('admin.profile')); ?>" class="<?php echo e(request()->routeIs('admin.profile') ? 'active' : ''); ?>">
                <span class="nav-icon">👤</span> Profile & Security
            </a>
            <a href="<?php echo e(route('admin.email-settings.edit')); ?>" class="<?php echo e(request()->routeIs('admin.email-settings.*') ? 'active' : ''); ?>">
                <span class="nav-icon">✉</span> Email Settings
            </a>
            <a href="<?php echo e(route('admin.logs')); ?>" class="<?php echo e(request()->routeIs('admin.logs') ? 'active' : ''); ?>">
                <span class="nav-icon">📝</span> Admin Logs
            </a>
            <a href="<?php echo e(route('trade')); ?>" target="_blank">
                <span class="nav-icon">🌐</span> View Site
            </a>
        </nav>
    </div>
</aside>


<div class="main-content">
    
    <header class="topbar">
        <div style="display:flex; align-items:center; gap:12px;">
            <button class="mobile-toggle" id="adminSidebarToggle" aria-label="Toggle Navigation">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="topbar-title"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></div>
        </div>
        <div class="topbar-right">
            <a href="<?php echo e(route('admin.profile')); ?>" class="topbar-profile-link <?php echo e(request()->routeIs('admin.profile') ? 'active' : ''); ?>" title="Admin Profile & Settings">
                <span>👤</span>
                <span class="profile-name"><?php echo e(auth()->user()->name ?? 'Admin'); ?></span>
            </a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" style="display:inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="topbar-logout">Logout</button>
            </form>
        </div>
    </header>

    
    <?php if(session('admin_impersonating')): ?>
    <div class="impersonation-banner">
        ⚠️ You are impersonating user <strong><?php echo e(auth()->user()->name); ?></strong>
        <form method="POST" action="<?php echo e(route('admin.return-admin')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit">↩ Return to Admin</button>
        </form>
    </div>
    <?php endif; ?>

    
    <div style="padding:0 28px; margin-top:16px;" class="flash-wrap">
        <?php if(session('success')): ?>
            <div class="alert alert-success">✓ <?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-error">✕ <?php echo e(session('error')); ?></div>
        <?php endif; ?>
        <?php if(session('info')): ?>
            <div class="alert alert-info">ℹ <?php echo e(session('info')); ?></div>
        <?php endif; ?>
    </div>

    
    <div class="page-content">
        <?php echo $__env->yieldContent('content'); ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('adminSidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const backdrop = document.getElementById('adminSidebarBackdrop');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (backdrop) backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    // Auto close sidebar when link is clicked on mobile
    document.querySelectorAll('.sidebar-nav a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 900) {
                closeSidebar();
            }
        });
    });
});
</script>

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/layouts/app.blade.php ENDPATH**/ ?>