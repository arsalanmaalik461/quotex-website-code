<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Quotex: An innovative platform for online investment')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/app.css?v={{ time() }}">
  <link rel="stylesheet" href="/css/platform.css?v={{ time() }}">
  <style>
    /* Exact Quotex 2-Column User Popover (Screenshot 1 & 2 Match) */
    .usermenu {
      position: relative;
    }
    .usermenu__info {
      background: #181c28 !important;
      border: 1px solid #283144 !important;
      border-radius: 6px !important;
      padding: 5px 12px !important;
      display: flex !important;
      align-items: center !important;
      gap: 10px !important;
      cursor: pointer !important;
      transition: all 0.15s !important;
    }
    .usermenu__info:hover {
      border-color: #3b4760 !important;
    }
    .usermenu__trigger-icon {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    }
    .usermenu__info-text {
      display: flex !important;
      flex-direction: column !important;
      gap: 2px !important;
    }
    .usermenu__info-name {
      font-size: 10px !important;
      font-weight: 700 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
    }
    .usermenu__info-name.text-green {
      color: #00c853 !important;
    }
    .usermenu__info-name.text-orange {
      color: #ff9800 !important;
    }
    .usermenu__info-balance {
      font-size: 13px !important;
      font-weight: 800 !important;
      color: #ffffff !important;
      font-variant-numeric: tabular-nums !important;
    }
    .usermenu-arrow {
      font-size: 9px !important;
      color: #8b9ab4 !important;
    }

    .usermenu__dropdown {
      position: absolute !important;
      top: calc(100% + 6px) !important;
      right: 0 !important;
      width: 420px !important;
      background: #181c28 !important;
      border: 1px solid #283144 !important;
      border-radius: 8px !important;
      box-shadow: 0 16px 48px rgba(0, 0, 0, 0.85) !important;
      z-index: 10000 !important;
      overflow: hidden !important;
    }
    .usermenu__popover-container {
      display: flex !important;
      width: 100% !important;
    }
    .usermenu__left-col {
      width: 275px !important;
      padding: 14px !important;
      display: flex !important;
      flex-direction: column !important;
      gap: 12px !important;
      box-sizing: border-box !important;
    }
    .usermenu__account-meta-card {
      background: #131722 !important;
      border: 1px solid #242c3e !important;
      border-radius: 6px !important;
      padding: 10px 12px !important;
      display: flex !important;
      flex-direction: column !important;
      gap: 6px !important;
    }
    .usermenu__standard-header {
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
    }
    .usermenu__standard-badge {
      display: flex !important;
      align-items: center !important;
      gap: 6px !important;
      font-size: 11px !important;
    }
    .meta-level {
      color: #8b9ab4 !important;
      font-weight: 700 !important;
      font-size: 10px !important;
      letter-spacing: 0.5px !important;
    }
    .meta-profit {
      color: #ffffff !important;
      font-weight: 800 !important;
      font-size: 11px !important;
    }
    .btn-eye-toggle {
      background: #1c2232 !important;
      border: 1px solid #283348 !important;
      border-radius: 4px !important;
      padding: 3px 5px !important;
      color: #8b9ab4 !important;
      cursor: pointer !important;
      display: flex !important;
      align-items: center !important;
    }
    .btn-eye-toggle:hover {
      color: #ffffff !important;
    }
    .usermenu__email-row {
      font-size: 12px !important;
      font-weight: 700 !important;
      color: #ffffff !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }
    .usermenu__id-row {
      font-size: 11px !important;
      color: #64748b !important;
      font-weight: 600 !important;
    }
    .usermenu__curr-row {
      display: flex !important;
      align-items: center !important;
      gap: 6px !important;
      font-size: 11px !important;
      color: #7b879d !important;
      border-top: 1px solid #1e2638 !important;
      padding-top: 6px !important;
      margin-top: 2px !important;
    }
    .usermenu__curr-row b {
      color: #ffffff !important;
    }
    .btn-change-badge {
      background: #026FD3 !important;
      border: none !important;
      color: #ffffff !important;
      font-size: 9px !important;
      font-weight: 800 !important;
      padding: 2px 7px !important;
      border-radius: 4px !important;
      cursor: pointer !important;
      letter-spacing: 0.4px !important;
      margin-left: 2px !important;
    }
    .btn-change-badge:hover {
      filter: brightness(1.15) !important;
    }

    .usermenu__account-options {
      display: flex !important;
      flex-direction: column !important;
      gap: 8px !important;
    }
    .usermenu__acc-option {
      display: flex !important;
      flex-direction: column !important;
      gap: 2px !important;
      padding: 8px 10px !important;
      border-radius: 6px !important;
      cursor: pointer !important;
      transition: background 0.15s !important;
    }
    .usermenu__acc-option:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }
    .usermenu__acc-option.active {
      background: rgba(2, 111, 211, 0.08) !important;
    }
    .qx-radio-label {
      display: flex !important;
      align-items: center !important;
      gap: 8px !important;
      cursor: pointer !important;
    }
    .qx-radio-label input[type="radio"] {
      display: none !important;
    }
    .qx-radio-dot {
      width: 14px !important;
      height: 14px !important;
      border-radius: 50% !important;
      border: 1.5px solid #4a5568 !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      flex-shrink: 0 !important;
      transition: all 0.15s !important;
    }
    .usermenu__acc-option.active .qx-radio-dot {
      border-color: #026FD3 !important;
    }
    .usermenu__acc-option.active .qx-radio-dot::after {
      content: '' !important;
      width: 6px !important;
      height: 6px !important;
      border-radius: 50% !important;
      background: #026FD3 !important;
    }
    .qx-acc-name {
      font-size: 12px !important;
      font-weight: 700 !important;
      color: #cbd5e1 !important;
    }
    .qx-acc-balance {
      font-size: 15px !important;
      font-weight: 800 !important;
      color: #ffffff !important;
      margin-left: 22px !important;
      font-variant-numeric: tabular-nums !important;
    }

    .usermenu__right-col {
      width: 145px !important;
      background: #111520 !important;
      border-left: 1px solid #232c3f !important;
      padding: 16px 14px !important;
      display: flex !important;
      flex-direction: column !important;
      justify-content: space-between !important;
      box-sizing: border-box !important;
    }
    .usermenu__nav-links {
      display: flex !important;
      flex-direction: column !important;
      gap: 14px !important;
    }
    .usermenu__nav-link {
      color: #cbd5e1 !important;
      text-decoration: none !important;
      font-size: 13px !important;
      font-weight: 600 !important;
      transition: color 0.15s !important;
      display: block !important;
    }
    .usermenu__nav-link:hover {
      color: #ffffff !important;
    }
    .usermenu__logout-form {
      margin-top: 20px !important;
    }
    .usermenu__btn-logout {
      background: transparent !important;
      border: none !important;
      color: #FF6251 !important;
      font-size: 13px !important;
      font-weight: 700 !important;
      display: flex !important;
      align-items: center !important;
      gap: 6px !important;
      cursor: pointer !important;
      padding: 0 !important;
      transition: filter 0.15s !important;
    }
    .usermenu__btn-logout:hover {
      filter: brightness(1.2) !important;
    }
  </style>
  @stack('styles')
</head>
<body class="quotex-platform-body">
  @include('layouts.preloader')

  <div class="app__page page">
    <!-- Platform Header (Exact Quotex DOM) -->
    <header class="page__header header">
      <div class="header__brand">
        <a href="{{ route('trade') }}" class="logo">
          <img src="/images/logo-dark.svg" alt="Quotex" height="26" style="height:26px;width:auto;display:block;">
        </a>
        <div class="slogan">Web Trading Platform</div>
      </div>

      <!-- Welcome Bonus Banner (Exact Quotex DOM) -->
      <a href="{{ route('deposit', ['promo' => 'WELCOME50']) }}" class="q04vx header-banner-top" title="Deposit and get 50% bonus">
        <div class="r7UKG">
          <span>Get a <b>50% bonus</b> on your deposit!</span>
          <div class="VRCVp">50%</div>
        </div>
      </a>

      <div class="header__container">
        <!-- User Menu Dropdown -->
        <div class="header__usermenu usermenu">
          <div class="usermenu__info" id="usermenu-trigger">
            <span class="usermenu__trigger-icon">
              @if(($user->account_type ?? 'demo') === 'live')
                <svg width="15" height="15" viewBox="0 0 24 24" fill="#00c853"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
              @else
                <span style="font-size: 13px;">🎓</span>
              @endif
            </span>
            <div class="usermenu__info-text">
              <div class="usermenu__info-name {{ ($user->account_type ?? 'demo') === 'demo' ? 'text-orange' : 'text-green' }}">
                {{ ($user->account_type ?? 'demo') === 'live' ? 'LIVE ACCOUNT' : 'DEMO ACCOUNT' }}
              </div>
              <div class="usermenu__info-balance">
                ${{ number_format(($user->account_type ?? 'demo') === 'live' ? ($user->live_balance ?? 0) : ($user->demo_balance ?? 3897.26), 2) }}
              </div>
            </div>
            <span class="usermenu-arrow" id="usermenu-arrow">▼</span>
          </div>

          <div class="usermenu__dropdown" id="usermenu-dropdown" style="display: none;">
            <div class="usermenu__popover-container">
              <!-- Left Column: User Details & Accounts -->
              <div class="usermenu__left-col">
                <div class="usermenu__account-meta-card">
                  <div class="usermenu__standard-header">
                    <div class="usermenu__standard-badge">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="#00c853"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                      <span class="meta-level">STANDARD:</span>
                      <span class="meta-profit">+0% profit</span>
                    </div>
                    <button type="button" class="btn-eye-toggle" title="Hide/Show Balance" onclick="toggleBalanceVisibility()">
                      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                  </div>
                  <div class="usermenu__email-row">{{ $user->email }}</div>
                  <div class="usermenu__id-row">ID: {{ $user->id ? (93090570 + $user->id) : '93090578' }}</div>
                  <div class="usermenu__curr-row">
                    <span>Currency:</span>
                    <b>USD</b>
                    <button type="button" class="btn-change-badge">CHANGE</button>
                  </div>
                </div>

                <!-- Account Radio Selection -->
                <div class="usermenu__account-options">
                  <div class="usermenu__acc-option {{ ($user->account_type ?? 'demo') === 'live' ? 'active' : '' }}" onclick="switchAccountType('live')">
                    <label class="qx-radio-label">
                      <input type="radio" name="popover_acc_type" value="live" {{ ($user->account_type ?? 'demo') === 'live' ? 'checked' : '' }}>
                      <span class="qx-radio-dot"></span>
                      <span class="qx-acc-name">Live Account</span>
                    </label>
                    <div class="qx-acc-balance">${{ number_format($user->live_balance ?? 0, 2) }}</div>
                  </div>

                  <div class="usermenu__acc-option {{ ($user->account_type ?? 'demo') === 'demo' ? 'active' : '' }}" onclick="switchAccountType('demo')">
                    <label class="qx-radio-label">
                      <input type="radio" name="popover_acc_type" value="demo" {{ ($user->account_type ?? 'demo') === 'demo' ? 'checked' : '' }}>
                      <span class="qx-radio-dot"></span>
                      <span class="qx-acc-name">Demo Account</span>
                    </label>
                    <div class="qx-acc-balance">${{ number_format($user->demo_balance ?? 3897.26, 2) }}</div>
                  </div>
                </div>
              </div>

              <!-- Right Column: Navigation Links -->
              <div class="usermenu__right-col">
                <nav class="usermenu__nav-links">
                  <a href="{{ route('deposit') }}" class="usermenu__nav-link">Deposit</a>
                  <a href="{{ route('withdrawal') }}" class="usermenu__nav-link">Withdrawal</a>
                  <a href="{{ route('deposit') }}" class="usermenu__nav-link">Payments</a>
                  <a href="{{ route('history') }}" class="usermenu__nav-link">Trades</a>
                  <a href="{{ route('profile') }}" class="usermenu__nav-link">Account</a>
                  <a href="{{ route('referral') }}" class="usermenu__nav-link" style="color:#00e676 !important;">Referral</a>
                </nav>
                <form action="{{ route('logout') }}" method="POST" class="usermenu__logout-form">
                  @csrf
                  <button type="submit" class="usermenu__btn-logout">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    <span>Logout</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Notification Bell -->
        @php
          $pNotifTxs = (isset($user) && $user)
            ? \App\Models\Transaction::where('user_id', $user->id)->latest()->limit(8)->get()
            : collect();
          $pNotifDep = $pNotifTxs->where('type', 'deposit')->count();
          $pNotifWdl = $pNotifTxs->where('type', 'withdrawal')->count();
          // +1 = the always-present 50% Welcome Bonus announcement below.
          $pNotifTotal = $pNotifTxs->count() + 1;
        @endphp
        <button type="button" class="btn-platform-bell" id="btn-platform-bell" onclick="togglePlatformNotifications(event)" title="Notifications">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          <span class="bell-count" id="platform-bell-count">{{ $pNotifTotal }}</span>
        </button>

        <!-- Notifications Popover Backdrop -->
        <div class="notifications-backdrop" id="platform-notifications-backdrop" style="display: none;" onclick="closePlatformNotifications()"></div>

        <!-- Notifications Popover Dropdown -->
        <div class="notifications-popover" id="platform-notifications-popover" style="display: none;" dir="ltr">
          <div class="notif-popover-header">
            <div class="notif-header-title-wrap">
              <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
              <span class="notif-header-title">Notifications</span>
              <span class="notif-header-count">{{ $pNotifTotal }}</span>
            </div>
            <div class="notif-header-actions">
              <button type="button" class="btn-mark-all-read" onclick="markAllPlatformNotificationsRead()" title="Mark all as read">Mark as read</button>
              <button type="button" class="btn-close-notif" onclick="closePlatformNotifications()" title="Close">✕</button>
            </div>
          </div>

          <!-- Filter Tabs -->
          <div class="notif-tabs">
            <button type="button" class="notif-tab active" data-tab="all" onclick="filterPlatformNotifications('all', this)">All</button>
            <button type="button" class="notif-tab" data-tab="deposit" onclick="filterPlatformNotifications('deposit', this)">
              Deposits <span class="notif-tab-badge">{{ $pNotifDep + 1 }}</span>
            </button>
            <button type="button" class="notif-tab" data-tab="withdrawal" onclick="filterPlatformNotifications('withdrawal', this)">
              Withdrawals <span class="notif-tab-badge">{{ $pNotifWdl }}</span>
            </button>
          </div>

          <!-- Notifications List -->
          <div class="notif-list" id="platform-notif-list">
            @foreach($pNotifTxs as $tx)
              @php
                $pIsDep = ($tx->type === 'deposit');
                $pIsSuccess = in_array(strtolower($tx->status), ['completed', 'approved', 'success']);
                $pIsPending = in_array(strtolower($tx->status), ['pending', 'processing', 'in_review']);
              @endphp
              <div class="notif-item unread notif-type-{{ $tx->type }}" data-type="{{ $tx->type }}">
                <div class="notif-icon-col {{ $pIsDep ? 'notif-icon-deposit' : 'notif-icon-withdrawal' }}">
                  @if($pIsDep)
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00d17a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-5-5m5 5l5-5"/></svg>
                  @else
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#026FD3" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l-5 5m5-5l5 5"/></svg>
                  @endif
                </div>
                <div class="notif-content-col">
                  <div class="notif-title-row">
                    <span class="notif-title {{ $pIsDep ? 'text-deposit' : 'text-withdrawal' }}">
                      {{ $pIsDep ? ($pIsSuccess ? 'Deposit Successful' : 'Deposit ' . ucfirst($tx->status)) : ($pIsSuccess ? 'Withdrawal Processed' : 'Withdrawal ' . ucfirst($tx->status)) }}
                    </span>
                    <span class="notif-badge-status {{ $pIsSuccess ? 'status-success' : ($pIsPending ? 'status-pending' : 'status-danger') }}">
                      {{ ucfirst($tx->status) }}
                    </span>
                  </div>
                  <div class="notif-amount-row">
                    <span class="notif-amount {{ $pIsDep ? 'text-deposit' : 'text-withdrawal' }}">
                      {{ $pIsDep ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                    </span>
                    <span class="notif-method">{{ $tx->method ?? 'USDT' }}</span>
                  </div>
                  <p class="notif-desc">
                    {{ $pIsDep ? 'Funds credited to Live balance.' : 'Withdrawal payout processed to destination.' }}
                  </p>
                  <div class="notif-meta-row">
                    <span class="notif-time">{{ $tx->created_at ? $tx->created_at->diffForHumans() : 'Recently' }}</span>
                    <a href="{{ $pIsDep ? route('deposit') : route('withdrawal') }}" class="notif-action-link">View Details →</a>
                  </div>
                </div>
              </div>
            @endforeach

            @if($pNotifTxs->isEmpty())
              <div class="notif-item notif-type-deposit" data-type="deposit">
                <div class="notif-icon-col notif-icon-deposit">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00d17a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-5-5m5 5l5-5"/></svg>
                </div>
                <div class="notif-content-col">
                  <div class="notif-title-row">
                    <span class="notif-title text-deposit">No transactions yet</span>
                  </div>
                  <p class="notif-desc">Your deposits and withdrawals will appear here.</p>
                  <div class="notif-meta-row">
                    <a href="{{ route('deposit') }}" class="notif-action-link">Make a Deposit →</a>
                  </div>
                </div>
              </div>
            @endif

            <!-- 50% Deposit Bonus Notification -->
            <div class="notif-item notif-type-deposit" data-type="deposit">
              <div class="notif-icon-col notif-icon-bonus">
                <span class="bonus-emoji">🚀</span>
              </div>
              <div class="notif-content-col">
                <div class="notif-title-row">
                  <span class="notif-title text-bonus">50% Welcome Bonus</span>
                  <span class="notif-badge-status status-bonus">Active</span>
                </div>
                <div class="notif-amount-row">
                  <span class="notif-amount text-bonus">+$25.00 Bonus</span>
                  <span class="notif-method">Promo: WELCOME50</span>
                </div>
                <p class="notif-desc">50% extra bonus credited to boost your initial trading volume and earnings.</p>
                <div class="notif-meta-row">
                  <span class="notif-time">Today, 11:20 AM</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer with Quick Action Links -->
          <div class="notif-popover-footer">
            <a href="{{ route('deposit') }}" class="btn-notif-deposit">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
              Deposit Now
            </a>
            <a href="{{ route('withdrawal') }}" class="btn-notif-withdrawal">
              Withdrawal
            </a>
          </div>
        </div>

        <!-- Sidebar action buttons -->
        <div class="header__sidebar">
          <a class="header__sidebar-button button--success" href="{{ route('deposit') }}">
            <span>Deposit</span>
          </a>
          <a class="header__sidebar-button button--withdrawal" href="{{ route('withdrawal') }}">
            Withdrawal
          </a>
        </div>
      </div>
    </header>

    <!-- Platform Subnav Navigation Bar (Exact Quotex DOM) -->
    <div class="page__content-header">
      <div class="page__content-nav tabs tabs--collapse-md">
        <nav class="tabs__bar">
          <a href="{{ route('withdrawal') }}" class="tabs__tab {{ request()->routeIs('withdrawal*') ? 'active' : '' }}">
            Withdrawal
          </a>
          <a href="{{ route('deposit') }}" class="tabs__tab {{ request()->routeIs('deposit*') || request()->routeIs('balance*') ? 'active' : '' }}">
            Payments
          </a>
          <a href="{{ route('history') }}" class="tabs__tab {{ request()->routeIs('history*') || request()->routeIs('trades*') ? 'active' : '' }}">
            Trades
          </a>
          <a href="{{ route('profile') }}" class="tabs__tab {{ request()->routeIs('profile*') || request()->routeIs('settings*') ? 'active' : '' }}">
            My account
          </a>
          <a href="{{ route('referral') }}" class="tabs__tab {{ request()->routeIs('referral*') || request()->routeIs('affiliate*') ? 'active' : '' }}">
            Referral
          </a>
          <a href="{{ route('market') }}" class="tabs__tab {{ request()->routeIs('market*') ? 'active' : '' }}">
            Market
          </a>
          <a href="{{ route('tournaments') }}" class="tabs__tab {{ request()->routeIs('tournaments*') ? 'active' : '' }}">
            Tournaments
          </a>
          <a href="{{ route('analytics') }}" class="tabs__tab {{ request()->routeIs('analytics*') ? 'active' : '' }}">
            Analytics
          </a>
        </nav>
      </div>
    </div>

    <!-- Main Body Content -->
    <main class="page__body {{ request()->routeIs('analytics*') || request()->routeIs('withdrawal*') || request()->routeIs('market*') || request()->routeIs('tournaments*') ? 'page__body--full' : '' }}" id="platform-page">
      @yield('content')
    </main>
  </div>

  <script>
    // Header dropdown toggle
    const trigger = document.getElementById('usermenu-trigger');
    const dropdown = document.getElementById('usermenu-dropdown');
    const arrow = document.getElementById('usermenu-arrow');
    if (trigger && dropdown) {
      trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isHidden = dropdown.style.display === 'none' || dropdown.style.display === '';
        dropdown.style.display = isHidden ? 'flex' : 'none';
        if (arrow) arrow.textContent = isHidden ? '▲' : '▼';
      });
      document.addEventListener('click', () => {
        dropdown.style.display = 'none';
        if (arrow) arrow.textContent = '▼';
      });
      dropdown.addEventListener('click', (e) => e.stopPropagation());
    }

    let balancesHidden = false;
    function toggleBalanceVisibility() {
      balancesHidden = !balancesHidden;
      document.querySelectorAll('.usermenu__info-balance, .qx-acc-balance').forEach(el => {
        if (!el.dataset.origVal) el.dataset.origVal = el.textContent;
        el.textContent = balancesHidden ? '••••••' : el.dataset.origVal;
      });
    }

    function switchAccountType(type) {
      fetch("{{ route('trade.switch') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').content
        },
        body: JSON.stringify({ account_type: type })
      }).then(() => window.location.reload());
    }

    // Platform header bell — notifications popover (same behaviour as the trade page bell)
    function closePlatformNotifications() {
      const pop = document.getElementById('platform-notifications-popover');
      const bdrop = document.getElementById('platform-notifications-backdrop');
      if (pop) {
        pop.style.display = 'none';
        pop.classList.remove('open');
      }
      if (bdrop) {
        bdrop.style.display = 'none';
      }
    }

    function togglePlatformNotifications(e) {
      if (e) {
        e.stopPropagation();
        e.preventDefault();
      }
      const pop = document.getElementById('platform-notifications-popover');
      const bdrop = document.getElementById('platform-notifications-backdrop');
      if (!pop) return;

      const isOpen = pop.classList.contains('open') && (pop.style.display === 'flex' || pop.style.display === 'block');
      if (isOpen) {
        closePlatformNotifications();
        return;
      }

      const bellBtn = document.getElementById('btn-platform-bell');
      const isMobile = window.innerWidth <= 768;
      if (bellBtn && !isMobile) {
        const rect = bellBtn.getBoundingClientRect();
        pop.style.position = 'fixed';
        pop.style.top = (rect.bottom + 6) + 'px';
        pop.style.left = Math.max(10, rect.right - 360) + 'px';
        pop.style.right = 'auto';
        pop.style.zIndex = '99999';
      } else {
        pop.style.position = 'fixed';
        pop.style.top = '52px';
        pop.style.left = '10px';
        pop.style.right = '10px';
        pop.style.width = 'auto';
        pop.style.zIndex = '99999';
      }

      pop.style.display = 'flex';
      pop.classList.add('open');
      if (bdrop) {
        bdrop.style.display = 'block';
        bdrop.style.zIndex = '99998';
      }
    }

    function filterPlatformNotifications(type, el) {
      document.querySelectorAll('#platform-notifications-popover .notif-tab').forEach(t => t.classList.remove('active'));
      if (el) el.classList.add('active');
      document.querySelectorAll('#platform-notifications-popover .notif-item').forEach(item => {
        const itemType = item.getAttribute('data-type');
        item.style.display = (type === 'all' || itemType === type) ? 'flex' : 'none';
      });
    }

    function markAllPlatformNotificationsRead() {
      document.querySelectorAll('#platform-notifications-popover .notif-item.unread').forEach(i => i.classList.remove('unread'));
      const badge = document.getElementById('platform-bell-count');
      if (badge) badge.style.display = 'none';
      const headerCount = document.querySelector('#platform-notifications-popover .notif-header-count');
      if (headerCount) headerCount.textContent = '0';
    }

    // Close the platform notifications popover on any click outside it / the bell
    document.addEventListener('click', (e) => {
      const pop = document.getElementById('platform-notifications-popover');
      const bell = document.getElementById('btn-platform-bell');
      if (pop && !pop.contains(e.target) && (!bell || !bell.contains(e.target))) {
        closePlatformNotifications();
      }
    });

    // Move the popover + backdrop to <body> so no overflow:hidden ancestor can clip them
    (function () {
      const bdrop = document.getElementById('platform-notifications-backdrop');
      const pop = document.getElementById('platform-notifications-popover');
      if (bdrop && bdrop.parentElement !== document.body) document.body.appendChild(bdrop);
      if (pop && pop.parentElement !== document.body) document.body.appendChild(pop);
    })();
  </script>
  @stack('scripts')
</body>
</html>
