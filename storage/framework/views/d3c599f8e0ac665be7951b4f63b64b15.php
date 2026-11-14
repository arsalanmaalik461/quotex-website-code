<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title><?php echo $__env->yieldContent('title', 'Quotex: An innovative platform for online investment'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/app.css?v=<?php echo e(time()); ?>">
  <link rel="stylesheet" href="/css/platform.css?v=<?php echo e(time()); ?>">
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
  <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="quotex-platform-body">
  <?php echo $__env->make('layouts.preloader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="app__page page">
    <!-- Platform Header (Exact Quotex DOM) -->
    <header class="page__header header">
      <div class="header__brand">
        <a href="<?php echo e(route('trade')); ?>" class="logo">
          <img src="/images/logo-dark.svg" alt="Quotex" height="26" style="height:26px;width:auto;display:block;">
        </a>
        <div class="slogan">Web Trading Platform</div>
      </div>

      <!-- Welcome Bonus Banner (Exact Quotex DOM) -->
      <div class="q04vx header-banner-top">
        <div class="r7UKG">
          <span>Get a <b>50% bonus</b> on your deposit!</span>
          <div class="VRCVp">50%</div>
        </div>
      </div>

      <div class="header__container">
        <!-- User Menu Dropdown -->
        <div class="header__usermenu usermenu">
          <div class="usermenu__info" id="usermenu-trigger">
            <span class="usermenu__trigger-icon">
              <?php if(($user->account_type ?? 'demo') === 'live'): ?>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="#00c853"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
              <?php else: ?>
                <span style="font-size: 13px;">🎓</span>
              <?php endif; ?>
            </span>
            <div class="usermenu__info-text">
              <div class="usermenu__info-name <?php echo e(($user->account_type ?? 'demo') === 'demo' ? 'text-orange' : 'text-green'); ?>">
                <?php echo e(($user->account_type ?? 'demo') === 'live' ? 'LIVE ACCOUNT' : 'DEMO ACCOUNT'); ?>

              </div>
              <div class="usermenu__info-balance">
                $<?php echo e(number_format(($user->account_type ?? 'demo') === 'live' ? ($user->live_balance ?? 0) : ($user->demo_balance ?? 3897.26), 2)); ?>

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
                  <div class="usermenu__email-row"><?php echo e($user->email); ?></div>
                  <div class="usermenu__id-row">ID: <?php echo e($user->id ? (93090570 + $user->id) : '93090578'); ?></div>
                  <div class="usermenu__curr-row">
                    <span>Currency:</span>
                    <b>USD</b>
                    <button type="button" class="btn-change-badge">CHANGE</button>
                  </div>
                </div>

                <!-- Account Radio Selection -->
                <div class="usermenu__account-options">
                  <div class="usermenu__acc-option <?php echo e(($user->account_type ?? 'demo') === 'live' ? 'active' : ''); ?>" onclick="switchAccountType('live')">
                    <label class="qx-radio-label">
                      <input type="radio" name="popover_acc_type" value="live" <?php echo e(($user->account_type ?? 'demo') === 'live' ? 'checked' : ''); ?>>
                      <span class="qx-radio-dot"></span>
                      <span class="qx-acc-name">Live Account</span>
                    </label>
                    <div class="qx-acc-balance">$<?php echo e(number_format($user->live_balance ?? 0, 2)); ?></div>
                  </div>

                  <div class="usermenu__acc-option <?php echo e(($user->account_type ?? 'demo') === 'demo' ? 'active' : ''); ?>" onclick="switchAccountType('demo')">
                    <label class="qx-radio-label">
                      <input type="radio" name="popover_acc_type" value="demo" <?php echo e(($user->account_type ?? 'demo') === 'demo' ? 'checked' : ''); ?>>
                      <span class="qx-radio-dot"></span>
                      <span class="qx-acc-name">Demo Account</span>
                    </label>
                    <div class="qx-acc-balance">$<?php echo e(number_format($user->demo_balance ?? 3897.26, 2)); ?></div>
                  </div>
                </div>
              </div>

              <!-- Right Column: Navigation Links -->
              <div class="usermenu__right-col">
                <nav class="usermenu__nav-links">
                  <a href="<?php echo e(route('deposit')); ?>" class="usermenu__nav-link">Deposit</a>
                  <a href="<?php echo e(route('withdrawal')); ?>" class="usermenu__nav-link">Withdrawal</a>
                  <a href="<?php echo e(route('deposit')); ?>" class="usermenu__nav-link">Payments</a>
                  <a href="<?php echo e(route('history')); ?>" class="usermenu__nav-link">Trades</a>
                  <a href="<?php echo e(route('profile')); ?>" class="usermenu__nav-link">Account</a>
                  <a href="<?php echo e(route('referral')); ?>" class="usermenu__nav-link" style="color:#00e676 !important;">Referral</a>
                </nav>
                <form action="<?php echo e(route('logout')); ?>" method="POST" class="usermenu__logout-form">
                  <?php echo csrf_field(); ?>
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
        <button type="button" class="btn-platform-bell" title="Notifications">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          <span class="bell-count">2</span>
        </button>

        <!-- Sidebar action buttons -->
        <div class="header__sidebar">
          <a class="header__sidebar-button button--success" href="<?php echo e(route('deposit')); ?>">
            <span>Deposit</span>
          </a>
          <a class="header__sidebar-button button--withdrawal" href="<?php echo e(route('withdrawal')); ?>">
            Withdrawal
          </a>
        </div>
      </div>
    </header>

    <!-- Platform Subnav Navigation Bar (Exact Quotex DOM) -->
    <div class="page__content-header">
      <div class="page__content-nav tabs tabs--collapse-md">
        <nav class="tabs__bar">
          <a href="<?php echo e(route('withdrawal')); ?>" class="tabs__tab <?php echo e(request()->routeIs('withdrawal*') ? 'active' : ''); ?>">
            Withdrawal
          </a>
          <a href="<?php echo e(route('deposit')); ?>" class="tabs__tab <?php echo e(request()->routeIs('deposit*') || request()->routeIs('balance*') ? 'active' : ''); ?>">
            Payments
          </a>
          <a href="<?php echo e(route('history')); ?>" class="tabs__tab <?php echo e(request()->routeIs('history*') || request()->routeIs('trades*') ? 'active' : ''); ?>">
            Trades
          </a>
          <a href="<?php echo e(route('profile')); ?>" class="tabs__tab <?php echo e(request()->routeIs('profile*') || request()->routeIs('settings*') ? 'active' : ''); ?>">
            My account
          </a>
          <a href="<?php echo e(route('referral')); ?>" class="tabs__tab <?php echo e(request()->routeIs('referral*') || request()->routeIs('affiliate*') ? 'active' : ''); ?>">
            Referral
          </a>
          <a href="<?php echo e(route('market')); ?>" class="tabs__tab <?php echo e(request()->routeIs('market*') ? 'active' : ''); ?>">
            Market
          </a>
          <a href="<?php echo e(route('tournaments')); ?>" class="tabs__tab <?php echo e(request()->routeIs('tournaments*') ? 'active' : ''); ?>">
            Tournaments
          </a>
          <a href="<?php echo e(route('analytics')); ?>" class="tabs__tab <?php echo e(request()->routeIs('analytics*') ? 'active' : ''); ?>">
            Analytics
          </a>
        </nav>
      </div>
    </div>

    <!-- Main Body Content -->
    <main class="page__body <?php echo e(request()->routeIs('analytics*') || request()->routeIs('withdrawal*') || request()->routeIs('market*') || request()->routeIs('tournaments*') ? 'page__body--full' : ''); ?>" id="platform-page">
      <?php echo $__env->yieldContent('content'); ?>
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
      fetch("<?php echo e(route('trade.switch')); ?>", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').content
        },
        body: JSON.stringify({ account_type: type })
      }).then(() => window.location.reload());
    }
  </script>
  <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/layouts/platform.blade.php ENDPATH**/ ?>