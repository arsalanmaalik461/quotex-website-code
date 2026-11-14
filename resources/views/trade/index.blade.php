<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $selectedAsset->symbol }} — QUOTEX Trading Platform</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/app.css?v={{ time() }}">
  <link rel="stylesheet" href="/css/trade.css?v={{ time() }}">
</head>
<body class="trade-body">
  @include('layouts.preloader')

  @php
    $renderAssetFlags = function($symbol, $flag = null, $type = 'forex') {
      $cleanSym = trim(preg_replace('/\(.*?\)/', '', $symbol));
      $parts = explode('/', $cleanSym);
      $map = [
        'USD'=>'us','EUR'=>'eu','GBP'=>'gb','JPY'=>'jp','AUD'=>'au',
        'CHF'=>'ch','CAD'=>'ca','NZD'=>'nz','BDT'=>'bd','IDR'=>'id',
        'BRL'=>'br','INR'=>'in','PKR'=>'pk','NGN'=>'ng','DZD'=>'dz',
        'TRY'=>'tr','RUB'=>'ru','MXN'=>'mx','ZAR'=>'za','CNY'=>'cn',
        'SGD'=>'sg','AED'=>'ae','COP'=>'co','PHP'=>'ph','MYR'=>'my','EGP'=>'eg',
        'ARS'=>'ar','CLP'=>'cl','NOK'=>'no','SEK'=>'se'
      ];
      if (count($parts) === 2) {
        $c1 = strtoupper(trim($parts[0]));
        $c2 = strtoupper(trim($parts[1]));
        $f1 = $map[$c1] ?? null;
        $f2 = $map[$c2] ?? null;
        if ($f1 && $f2) {
          return '<span class="qx-dual-flags"><img src="/images/flags/'.$f1.'.svg" class="qx-flag-circle qx-flag-base" alt="'.$c1.'"><img src="/images/flags/'.$f2.'.svg" class="qx-flag-circle qx-flag-quote" alt="'.$c2.'"></span>';
        }
      }
      if (stripos($symbol, 'BTC') !== false || stripos($symbol, 'Bitcoin') !== false) {
        return '<span class="qx-dual-flags"><span class="qx-flag-circle qx-crypto-circle btc">₿</span><img src="/images/flags/us.svg" class="qx-flag-circle qx-flag-quote" alt="USD"></span>';
      }
      if (stripos($symbol, 'ETH') !== false || stripos($symbol, 'Ethereum') !== false) {
        return '<span class="qx-dual-flags"><span class="qx-flag-circle qx-crypto-circle eth">⟠</span><img src="/images/flags/us.svg" class="qx-flag-circle qx-flag-quote" alt="USD"></span>';
      }
      if (stripos($symbol, 'XAU') !== false || stripos($symbol, 'Gold') !== false) {
        return '<span class="qx-dual-flags"><span class="qx-flag-circle qx-commodity-circle gold">🥇</span><img src="/images/flags/us.svg" class="qx-flag-circle qx-flag-quote" alt="USD"></span>';
      }
      return '<span class="qx-flag-circle-generic">'.($flag ?? '🌐').'</span>';
    };
  @endphp

  @php
    $assetDigits = $selectedAsset->digits ?? null;
    $basePrice = (float) ($selectedAsset->current_price ?? 1335.50);
    $symUpper = strtoupper($selectedAsset->symbol ?? '');
    if ($assetDigits === null || ($assetDigits > 2 && ($basePrice >= 500 || str_contains($symUpper, 'BTC') || str_contains($symUpper, 'ETH') || str_contains($symUpper, 'GOLD') || str_contains($symUpper, 'XAU')))) {
      $assetDigits = 2;
    } elseif ($assetDigits > 3 && ($basePrice >= 50 || str_contains($symUpper, 'JPY'))) {
      $assetDigits = 3;
    }
  @endphp

  <!-- Hidden State Inputs -->
  <input type="hidden" id="asset-id-val" value="{{ $selectedAsset->id }}">
  <input type="hidden" id="asset-payout-val" value="{{ $selectedAsset->payout_percent }}">
  <input type="hidden" id="base-price" value="{{ $selectedAsset->current_price ?? 1335.50 }}">
  <input type="hidden" id="asset-digits-val" value="{{ $assetDigits }}">
  <input type="hidden" id="user-account-type" value="{{ $user->account_type }}">

  <div class="trade-layout">
    
    <!-- 1. LEFT SIDEBAR (EXACT QUOTEX) -->
    <!-- 1. LEFT SIDEBAR (EXACT QUOTEX SCREENSHOT) -->
    <aside class="trade-sidebar app__sidebar TIdji">
      <div class="sidebar-top">
        <nav class="sidebar-nav">
          <a href="{{ route('trade') }}" class="sidebar-btn active" id="btn-nav-trade" title="Trade">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 16l4-4 4 4 5-5"/></svg>
            <span class="btn-lbl">TRADE</span>
          </a>

          <a href="{{ route('faq') }}" class="sidebar-btn" id="btn-nav-support" title="Support">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span class="btn-lbl">SUPPORT</span>
          </a>

          <a href="{{ route('profile') }}" class="sidebar-btn" id="btn-nav-account" title="Account">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span class="btn-lbl">ACCOUNT</span>
          </a>

          <a href="{{ route('tournaments') }}" class="sidebar-btn sidebar-btn-tournaments" id="btn-nav-tournaments" title="Tournaments">
            <div class="sidebar-nav-icon-wrap">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 14.66V17c0 .55-.45 1-1 1H7v4h10v-4h-2c-.55 0-1-.45-1-1v-2.34M18 4H6v7a6 6 0 0 0 12 0V4z"/></svg>
              <span class="sidebar-nav-badge">3</span>
            </div>
            <span class="btn-lbl">TOURNAMENTS</span>
          </a>

          <button type="button" class="sidebar-btn" id="btn-nav-more" title="More" onclick="toggleSidebarMenu()">
            <div class="sidebar-nav-icon-wrap">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
              <span class="sidebar-nav-badge">4</span>
            </div>
            <span class="btn-lbl">MORE</span>
          </button>
        </nav>
      </div>

      <!-- Exact Quotex "More" Slide-out Drawer (DOM & Screenshot Exact) -->
      <div id="sidebar-popup-menu" class="qx-more-drawer iUF_b" style="display: none;">
        <div class="more-drawer-header">
          <div class="L1kvo more-drawer-title">More</div>
          <button type="button" class="more-drawer-close" onclick="toggleSidebarMenu()">✕</button>
        </div>
        
        <div class="more-drawer-nav">
          <!-- Analytics Link -->
          <a class="e2OnI more-drawer-item" href="{{ route('analytics') }}">
            <div class="more-item-left">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10h-10z"/></svg>
              <span class="KYcVi">Analytics</span>
            </div>
            <span class="more-item-chevron">›</span>
          </a>

          <!-- TOP / Leaderboard Item -->
          <div class="e2OnI more-drawer-item" onclick="openLeaderboardModal()">
            <div class="more-item-left">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
              <span class="KYcVi">TOP</span>
            </div>
            <span class="more-item-chevron">›</span>
          </div>

          <!-- Signals Item -->
          <div class="e2OnI more-drawer-item" onclick="openSignalsDrawer()">
            <div class="more-item-left">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.93 4.93a10 10 0 0 1 14.14 0M7.76 7.76a6 6 0 0 1 8.48 0M10.59 10.59a2 2 0 0 1 2.82 0"/></svg>
              <span class="KYcVi">Signals</span>
            </div>
            <span class="more-item-chevron">›</span>
          </div>

          <!-- Account / Profile Link -->
          <a class="e2OnI AclVm more-drawer-item" href="{{ route('profile') }}">
            <div class="more-item-left">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <span class="UMMVb">Account</span>
            </div>
            <span class="more-item-chevron">›</span>
          </a>

          <!-- Tournaments Link with Badge 4 -->
          <a class="e2OnI Aez8T more-drawer-item" href="{{ route('tournaments') }}">
            <div class="more-item-left">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 14.66V17c0 .55-.45 1-1 1H7v4h10v-4h-2c-.55 0-1-.45-1-1v-2.34M18 4H6v7a6 6 0 0 0 12 0V4z"/></svg>
              <span class="UMMVb">Tournaments</span>
            </div>
            <div class="more-item-right">
              <span class="more-badge-blue VixvO SYV91">4</span>
              <span class="more-item-chevron">›</span>
            </div>
          </a>

          <!-- Market Link with Badge 4 -->
          <a class="e2OnI jCZrK more-drawer-item" href="{{ route('market') }}">
            <div class="more-item-left">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
              <span class="UMMVb">Market</span>
            </div>
            <div class="more-item-right">
              <span class="more-badge-blue VixvO SYV91">4</span>
              <span class="more-item-chevron">›</span>
            </div>
          </a>

          <!-- Referral & Affiliate Program -->
          <a class="e2OnI more-drawer-item" href="{{ route('referral') }}" style="border-top:1px solid #1e2638; margin-top:4px; padding-top:10px;">
            <div class="more-item-left">
              <svg width="18" height="18" fill="none" stroke="#00e676" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              <span class="UMMVb" style="color:#00e676;">Referral / Affiliate</span>
            </div>
            <span class="more-item-chevron">›</span>
          </a>
        </div>
      </div>

      <div class="sidebar-bottom">
        <button type="button" class="sidebar-icon-mini" id="btn-fullscreen-toggle" title="Fullscreen" onclick="toggleFullscreen()">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
        </button>

        <button type="button" class="sidebar-icon-mini" id="btn-toggle-sound" title="Sound On — click to mute" onclick="toggleSound()">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
        </button>

        <!-- Settings Gear Button (Opens Settings Sidepanel) -->
        <button type="button" class="sidebar-icon-mini" id="btn-settings-toggle" title="Settings" onclick="toggleSettingsSidepanel()">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </button>

        <a href="https://facebook.com" target="_blank" class="sidebar-icon-join" title="Join Us">
          <div class="join-icon-stack">
            <span>📷</span>
            <span class="join-text">JOIN US</span>
          </div>
        </a>

        <a href="{{ route('faq') }}" class="sidebar-btn-help-green" title="Help">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <span>Help</span>
        </a>
      </div>
    </aside>

    <!-- 2. MAIN CENTER AREA -->
    <div class="trade-main">
      
      <!-- TOP HEADER (Exact Screenshot: Clean logo, no middle bonus banner, deposit, withdrawal) -->
      <header class="trade-header">
        
        <!-- Left: Hamburger, Clean QUOTEX Logo & Subtitle -->
        <div class="header-left">
          <button type="button" class="btn-hamburger" id="btn-hamburger" title="Menu" onclick="toggleSidebarMenu()">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>

          <a href="{{ route('trade') }}" class="header-brand">
            <img src="/profile/images/logo-dark.svg" alt="Quotex" height="24" style="height:24px;width:auto;display:block;">
            <span class="brand-sub-dot">·</span>
            <span class="brand-sub-text">WEB TRADING PLATFORM</span>
          </a>
        </div>

        <!-- Center: 50% Rocket Bonus Banner (Exact Quotex Screenshot) -->
        <div class="header-center">
          <a href="{{ route('deposit') }}" class="header-bonus-banner" title="Deposit and get 50% bonus">
            <span class="bonus-rocket-img">🚀</span>
            <span class="bonus-banner-text">Get a <b>50% bonus</b> on your deposit!</span>
            <span class="bonus-percent-badge">50%</span>
          </a>
        </div>

        <!-- Right: Account Group (Balance + Notification Bell), Green Deposit, Gray Withdrawal -->
        <div class="header-right">
          <div class="header-acc-group">
            <!-- Account Switcher Pill -->
            <div class="account-switcher-wrap">
              <!-- Account Popover Backdrop for Mobile & Desktop -->
              <div class="account-popover-backdrop" id="account-popover-backdrop" style="display: none;" onclick="closeAccountDropdown()"></div>

              @php
                $isLive = ($user->account_type === 'live');
              @endphp
              <div class="account-switcher" id="account-switcher-toggle">
                <div class="account-info">
                  <span class="account-icon-container" id="account-icon-container">
                    @if($isLive)
                      <svg class="acc-plane-icon" width="18" height="18" viewBox="0 0 24 24" fill="#00d17a"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    @else
                      <span class="acc-icon-cap">🎓</span>
                    @endif
                  </span>
                  <div class="acc-text-col">
                    <span class="account-type {{ $isLive ? 'text-green' : 'text-orange' }}" id="account-type-label">{{ $isLive ? 'LIVE ACCOUNT' : 'DEMO ACCOUNT' }}</span>
                    <span class="account-balance" id="account-balance">${{ number_format($user->getBalance(), 2) }}</span>
                  </div>
                  <svg class="acc-chevron-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#8fa0b5" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
              </div>

              <div class="account-dropdown qx-account-popover p0Ijl Qx5RW" id="account-dropdown" dir="ltr">
                <!-- Left: Account & Profit Details -->
                <ul class="IkdIG acc-popover-main">
                  <li class="RDtBn acc-popover-user-row">
                    <div class="Xun17 standard-profit-pill">
                      <div class="OCZRh">
                        <div class="wqiyJ">
                          <svg class="paper-plane-icon" width="16" height="16" viewBox="0 0 24 24" fill="#00c853"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                          <span class="qjGlZ">STANDARD:</span>
                          <span class="VgpLl">+0% profit</span>
                        </div>
                      </div>
                      <div class="popover-top-actions">
                        <button type="button" class="btn-hide-balance-eye" title="Hide/Show Balance">
                          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button type="button" class="btn-close-popover" onclick="closeAccountDropdown()" title="Close">✕</button>
                      </div>
                    </div>
                    
                    <div class="MBy5L acc-user-meta">
                      <div class="AWI3t">
                        <div class="TSVxV user-email-val">{{ $user->email }}</div>
                        <span class="xHywM user-id-val">ID: {{ $user->id ? (93090570 + $user->id) : '93090578' }}</span>
                      </div>
                    </div>

                    <div class="P6HXN acc-curr-row">
                      <span class="aGgsi">Currency:</span>
                      <b class="jNiTn">USD</b>
                      <button type="button" class="ip2D2 btn-change-curr" onclick="alert('Account currency is set to USD.')">CHANGE</button>
                    </div>
                  </li>

                  <!-- Radio Accounts Switcher Section -->
                  <div class="grOBD acc-types-group">
                    <!-- Live Account Item -->
                    <li class="RDtBn acc-item-box {{ $user->account_type === 'live' ? 'active' : '' }}" onclick="switchAccountType('live')">
                      <div class="acc-item-header">
                        <label class="pBke3 ajYMk custom-radio-label">
                          <input type="radio" class="r29tN" name="acc_type_radio" value="live" {{ $user->account_type === 'live' ? 'checked' : '' }}>
                          <span class="radio-mark"></span>
                          <span class="acc-item-name">Live Account</span>
                        </label>
                      </div>
                      <b class="YnoT0 acc-balance-val">${{ number_format($user->live_balance, 2) }}</b>
                      <div class="KZUc1 acc-limit-note">The daily limit is not set</div>
                      <button type="button" class="s3gwg btn-set-limit" onclick="event.stopPropagation(); alert('Daily limit settings');">SET LIMIT</button>
                    </li>

                    <!-- Demo Account Item -->
                    <li class="RDtBn acc-item-box {{ $user->account_type === 'demo' ? 'active' : '' }}" onclick="switchAccountType('demo')">
                      <div class="acc-item-header">
                        <label class="pBke3 ajYMk custom-radio-label">
                          <input type="radio" class="r29tN" name="acc_type_radio" value="demo" {{ $user->account_type === 'demo' ? 'checked' : '' }}>
                          <span class="radio-mark"></span>
                          <span class="acc-item-name">Demo Account</span>
                        </label>
                        <button type="button" class="btn-edit-pencil" title="Edit value" onclick="event.stopPropagation(); toggleEditDemoInput();">✎</button>
                      </div>
                      <div class="WjlOy demo-bal-row">
                        <b class="YnoT0 acc-balance-val" id="dd-demo-balance">${{ number_format($user->demo_balance, 2) }}</b>
                        <button type="button" class="btn-refill-inline" id="btn-refill-demo" title="Reset Demo Balance" onclick="event.stopPropagation(); refillDemoBalance();">⟳</button>
                      </div>

                      <!-- Editable Demo Balance Input Box (From Quotex DOM) -->
                      <div class="phkwf edit-demo-box" id="edit-demo-box" style="display: none;" onclick="event.stopPropagation();">
                        <div class="edit-demo-field">
                          <span class="edit-demo-label">New value</span>
                          <input type="number" id="input-new-demo-val" class="edit-demo-input" placeholder="Enter the amount" value="{{ $user->demo_balance }}" min="0" step="any" onclick="event.stopPropagation();">
                        </div>
                        <button type="button" class="btn-confirm-demo" onclick="event.stopPropagation(); confirmNewDemoBalance();">Confirm</button>
                      </div>
                    </li>
                  </div>
                </ul>

                <!-- Right: Quick Navigation Menu (Exact Quotex) -->
                <ul class="iD5aW acc-popover-nav">
                  <li class="UyTSL"><a class="deiRA" href="{{ route('deposit') }}">Deposit</a></li>
                  <li class="UyTSL"><a class="deiRA" href="{{ route('withdrawal') }}">Withdrawal</a></li>
                  <li class="UyTSL"><a class="deiRA" href="{{ route('deposit') }}">Payments</a></li>
                  <li class="UyTSL"><a class="deiRA" href="{{ route('trade') }}">Trades</a></li>
                  <li class="UyTSL"><a class="deiRA" href="{{ route('profile') }}">My account</a></li>
                  <li class="d3QmE">
                    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                      @csrf
                      <button type="submit" class="Oqhbk PbkxI btn-logout-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        <span>Logout</span>
                      </button>
                    </form>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Bell Notification Wrap (Positioned Directly Beside Balance) -->
            <div class="notif-bell-wrap">
              <button type="button" class="btn-header-bell" id="btn-header-bell" onclick="toggleNotifications(event)" title="Notifications">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="bell-count-badge" id="bell-count-badge">4</span>
              </button>

              <!-- Notifications Popover Backdrop -->
              <div class="notifications-backdrop" id="notifications-backdrop" style="display: none;" onclick="closeNotifications()"></div>

              <!-- Notifications Popover Dropdown -->
              <div class="notifications-popover" id="notifications-popover" style="display: none;" dir="ltr">
                <div class="notif-popover-header">
                  <div class="notif-header-title-wrap">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="notif-header-title">Notifications</span>
                    <span class="notif-header-count">4</span>
                  </div>
                  <div class="notif-header-actions">
                    <button type="button" class="btn-mark-all-read" onclick="markAllNotificationsRead()" title="Mark all as read">Mark as read</button>
                    <button type="button" class="btn-close-notif" onclick="closeNotifications()" title="Close">✕</button>
                  </div>
                </div>

                <!-- Filter Tabs -->
                <div class="notif-tabs">
                  <button type="button" class="notif-tab active" data-tab="all" onclick="filterNotifications('all', this)">All</button>
                  <button type="button" class="notif-tab" data-tab="deposit" onclick="filterNotifications('deposit', this)">
                    Deposits <span class="notif-tab-badge">2</span>
                  </button>
                  <button type="button" class="notif-tab" data-tab="withdrawal" onclick="filterNotifications('withdrawal', this)">
                    Withdrawals <span class="notif-tab-badge">2</span>
                  </button>
                </div>

                <!-- Notifications List -->
                <div class="notif-list" id="notif-list-container">
                  @php
                    $txs = $recentTransactions ?? (\App\Models\Transaction::where('user_id', $user->id)->latest()->limit(8)->get() ?? collect());
                  @endphp

                  @if($txs->isNotEmpty())
                    @foreach($txs as $tx)
                      @php
                        $isDep = ($tx->type === 'deposit');
                        $isSuccess = in_array(strtolower($tx->status), ['completed', 'approved', 'success']);
                        $isPending = in_array(strtolower($tx->status), ['pending', 'processing', 'in_review']);
                      @endphp
                      <div class="notif-item unread notif-type-{{ $tx->type }}" data-type="{{ $tx->type }}">
                        <div class="notif-icon-col {{ $isDep ? 'notif-icon-deposit' : 'notif-icon-withdrawal' }}">
                          @if($isDep)
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00d17a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-5-5m5 5l5-5"/></svg>
                          @else
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#026FD3" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l-5 5m5-5l5 5"/></svg>
                          @endif
                        </div>
                        <div class="notif-content-col">
                          <div class="notif-title-row">
                            <span class="notif-title {{ $isDep ? 'text-deposit' : 'text-withdrawal' }}">
                              {{ $isDep ? ($isSuccess ? 'Deposit Successful' : 'Deposit ' . ucfirst($tx->status)) : ($isSuccess ? 'Withdrawal Processed' : 'Withdrawal ' . ucfirst($tx->status)) }}
                            </span>
                            <span class="notif-badge-status {{ $isSuccess ? 'status-success' : ($isPending ? 'status-pending' : 'status-danger') }}">
                              {{ ucfirst($tx->status) }}
                            </span>
                          </div>
                          <div class="notif-amount-row">
                            <span class="notif-amount {{ $isDep ? 'text-deposit' : 'text-withdrawal' }}">
                              {{ $isDep ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                            </span>
                            <span class="notif-method">{{ $tx->method ?? 'USDT' }}</span>
                          </div>
                          <p class="notif-desc">
                            {{ $isDep ? 'Funds credited to Live balance.' : 'Withdrawal payout processed to destination.' }}
                          </p>
                          <div class="notif-meta-row">
                            <span class="notif-time">{{ $tx->created_at ? $tx->created_at->diffForHumans() : 'Recently' }}</span>
                            <a href="{{ $isDep ? route('deposit') : route('withdrawal') }}" class="notif-action-link">View Details →</a>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @endif

                  <!-- Default Standard Deposit & Withdrawal Notifications -->
                  <!-- 1. Deposit Successful Notification -->
                  <div class="notif-item unread notif-type-deposit" data-type="deposit">
                    <div class="notif-icon-col notif-icon-deposit">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00d17a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-5-5m5 5l5-5"/></svg>
                    </div>
                    <div class="notif-content-col">
                      <div class="notif-title-row">
                        <span class="notif-title text-deposit">Deposit Successful</span>
                        <span class="notif-badge-status status-success">Completed</span>
                      </div>
                      <div class="notif-amount-row">
                        <span class="notif-amount text-deposit">+$50.00</span>
                        <span class="notif-method">USDT (TRC20)</span>
                      </div>
                      <p class="notif-desc">Your payment has been verified and deposited into your Live Trading account.</p>
                      <div class="notif-meta-row">
                        <span class="notif-time">15 min ago</span>
                        <a href="{{ route('deposit') }}" class="notif-action-link">Deposit More →</a>
                      </div>
                    </div>
                  </div>

                  <!-- 2. Withdrawal Processed Notification -->
                  <div class="notif-item unread notif-type-withdrawal" data-type="withdrawal">
                    <div class="notif-icon-col notif-icon-withdrawal">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#026FD3" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l-5 5m5-5l5 5"/></svg>
                    </div>
                    <div class="notif-content-col">
                      <div class="notif-title-row">
                        <span class="notif-title text-withdrawal">Withdrawal Processed</span>
                        <span class="notif-badge-status status-success">Success</span>
                      </div>
                      <div class="notif-amount-row">
                        <span class="notif-amount text-withdrawal">-$100.00</span>
                        <span class="notif-method">TRC-20 Wallet</span>
                      </div>
                      <p class="notif-desc">Your withdrawal request has been approved and sent to your blockchain address.</p>
                      <div class="notif-meta-row">
                        <span class="notif-time">2 hours ago</span>
                        <a href="{{ route('withdrawal') }}" class="notif-action-link">View Details →</a>
                      </div>
                    </div>
                  </div>

                  <!-- 3. 50% Deposit Bonus Notification -->
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

                  <!-- 4. Withdrawal In Review Notification -->
                  <div class="notif-item notif-type-withdrawal" data-type="withdrawal">
                    <div class="notif-icon-col notif-icon-pending">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="notif-content-col">
                      <div class="notif-title-row">
                        <span class="notif-title text-pending">Withdrawal Request</span>
                        <span class="notif-badge-status status-pending">In Review</span>
                      </div>
                      <div class="notif-amount-row">
                        <span class="notif-amount text-pending">-$50.00</span>
                        <span class="notif-method">Binance Pay</span>
                      </div>
                      <p class="notif-desc">Your withdrawal request was received and is undergoing automated risk review.</p>
                      <div class="notif-meta-row">
                        <span class="notif-time">Yesterday</span>
                        <a href="{{ route('withdrawal') }}" class="notif-action-link">Check Status →</a>
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
            </div>
          </div>

          <!-- Quick Deposit Button (Green) -->
          <a href="{{ route('deposit') }}" class="btn-deposit-header">
            <span>+ Deposit</span>
          </a>

          <!-- Withdrawal Button (Dark Gray) -->
          <a href="{{ route('withdrawal') }}" class="btn-withdrawal-header">
            Withdrawal
          </a>
        </div>
      </header>

      <!-- CHART AREA (With Watermark, Expiry Lines & Floating Tools) -->
      <div class="chart-area" id="chart-area-container">
        
        <!-- Mobile Floating Chart Controls Stack (Top-Left: [...] Tools & [💼 0] Active Deals) -->
        <div class="mobile-chart-float-stack" id="mobile-chart-float-stack">
          <button type="button" class="btn-mobile-chart-menu" id="btn-mobile-chart-menu" onclick="toggleTimeframePopover(event, 'mobile')" title="Select Candle Timeframe">
            <span id="mobile-dots-icon" style="display:inline-flex; align-items:center;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
            </span>
            <span id="mobile-dots-close" style="display:none; font-size:16px; font-weight:700; line-height:1;">✕</span>
          </button>
          <button type="button" class="btn-mobile-briefcase" id="btn-mobile-briefcase" onclick="toggleMobileTradesDrawer()" title="Active Deals">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            <span class="mobile-briefcase-count" id="mobile-briefcase-count">0</span>
          </button>
        </div>

        <!-- Vertical Sentiment Strip on Left Edge of Chart (Exact Screenshot) -->
        <div class="sidebar-sentiment-strip">
          <span class="sent-top-pct" id="sent-top-val">57%</span>
          <div class="sent-line-track">
            <div class="sent-line-red" id="sent-red-line" style="height: 57%;"></div>
            <div class="sent-line-green" id="sent-green-line" style="height: 43%;"></div>
          </div>
          <span class="sent-bottom-pct" id="sent-bottom-val">43%</span>
        </div>

        <!-- Floating Chart Top-Left Controls Stack (Exact Quotex Screenshot) -->
        <div class="chart-top-left-stack">
          <!-- Asset Tabs Bar: Blue Square Plus + Open Tabs Strip -->
          <div class="qx-open-tabs-bar Q02Z1" id="qx-open-tabs-bar">
            <button type="button" class="btn-add-asset-square" id="btn-add-asset-tab" title="Add Asset">+</button>
            
            @php
              $tabAssets = [
                [
                  'id'     => $selectedAsset->id,
                  'symbol' => $selectedAsset->symbol,
                  'name'   => $selectedAsset->name,
                  'flag'   => $selectedAsset->flag ?? $selectedAsset->flag_icon ?? '🌐',
                  'payout' => $selectedAsset->payout_percent,
                  'base'   => $selectedAsset->current_price ?? 1.0,
                  'digits' => $selectedAsset->digits ?? 5,
                ]
              ];
            @endphp
            <script>window.INITIAL_ASSET_TABS = @json($tabAssets);</script>

            @foreach($tabAssets as $tab)
              @php
                $isActive = ($selectedAsset->symbol === $tab['symbol']);
                $dbAsset = $assets->firstWhere('symbol', $tab['symbol']);
                $tabUrl = $dbAsset ? route('trade', ['asset' => $dbAsset->id]) : '#';
              @endphp
              <div class="dJ15T vXMlv qx-tab-pill {{ $isActive ? 'active AmO6b' : '' }}" 
                   id="{{ $isActive ? 'tab-active' : '' }}"
                   data-symbol="{{ $tab['symbol'] }}"
                   title="{{ $tab['name'] }}">
                <div class="ZyIJD">
                  <div class="H4wMV">
                    <div class="qHbv0">
                      <span class="flag-icon" @if($isActive) id="asset-flag" @endif>{!! $renderAssetFlags($tab['symbol'], $tab['flag']) !!}</span>
                      <div class="WRocw symbol-name" @if($isActive) id="asset-symbol" @endif>{{ $tab['symbol'] }}</div>
                    </div>
                    <div class="DyILR">
                      <div class="ElyTP payout-pill-pct {{ $isActive ? 'text-gold' : 'text-green' }}" @if($isActive) id="asset-payout" @endif>{{ $tab['payout'] }}%</div>
                    </div>
                    @if($isActive)
                      <span class="tab-chevron">▼</span>
                      <span class="tab-close-icon" onclick="event.stopPropagation();">✕</span>
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <!-- Live Ticking Clock Floating Top-Left -->
          <div class="floating-clock-box">
            <span class="live-dot-green"></span>
            <span class="clock-time-val" id="live-clock">21:22:34</span>
            <span class="clock-tz-val">UTC+5</span>
          </div>

          <!-- Floating Pair Information Capsule -->
          <button type="button" class="btn-floating-pair-info" id="btn-pair-info">
            <span class="info-bubble">ⓘ</span>
            <span>PAIR INFORMATION</span>
          </button>
        </div>

        <!-- Floating Green Trade Opened Toasts (stacked, one per placed trade — real Quotex) -->
        <div id="chart-trade-toasts" class="chart-trade-toasts"></div>

        <!-- Giant Faint Background Watermark DEMO -->
        <div class="chart-demo-watermark">DEMO</div>

        <div id="trade-chart" class="trade-chart-canvas"></div>
        <div id="indicators-legend" class="indicators-legend" style="display:none;"></div>
        <svg id="drawings-layer" class="drawings-layer"></svg>
        <div id="drawings-capture" class="drawings-capture" style="display:none;"></div>
        <div id="drawings-panel" class="drawings-panel" style="display:none;">
          <div class="drawings-panel-header">
            <span>Drawings</span>
            <button type="button" class="drawings-panel-close" onclick="toggleDrawingsPanel(false)">✕</button>
          </div>
          <div class="drawings-list" id="drawings-list"></div>
          <button type="button" class="drawings-delete-all" onclick="deleteAllDrawings()">🗑 Delete all</button>
          <div class="drawings-section-title">Technical Indicators</div>
          <div class="drawings-indicators">
            <label class="indicator-option"><span>SMA (10)</span><input type="checkbox" id="chk-ind-sma"></label>
            <label class="indicator-option"><span>EMA (20)</span><input type="checkbox" id="chk-ind-ema"></label>
            <label class="indicator-option"><span>Bollinger Bands</span><input type="checkbox" id="chk-ind-bollinger"></label>
            <label class="indicator-option"><span>RSI (14)</span><input type="checkbox" id="chk-ind-rsi"></label>
          </div>
          <div class="drawings-hint" id="drawings-hint" style="display:none;"></div>
        </div>

        <!-- Floating Bottom-Left Chart Tools (Pencil, Timeframe 1m, Candles, Crosshair) -->
        <div class="chart-floating-tools-left">
          <div class="chart-tool-pop-wrap">
            <button type="button" class="chart-float-btn" id="btn-indicators-toggle" title="Drawing / Indicators">
              <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </button>
          </div>

          <div class="chart-tool-pop-wrap">
            <button type="button" class="chart-float-btn text-bold" id="btn-tf-toggle" onclick="toggleTimeframePopover(event, 'desktop')" title="Select Timeframe">1m</button>
          </div>

          <button type="button" class="chart-float-btn" id="btn-chart-type-toggle" title="Chart Type">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 3v18M17 3v18M5 7h4v10H5zM15 5h4v6h-4z"/></svg>
          </button>

          <button type="button" class="chart-float-btn" id="btn-crosshair-toggle" title="Crosshair">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="12" y1="3" x2="12" y2="21"/><line x1="3" y1="12" x2="21" y2="12"/></svg>
          </button>
        </div>

        <!-- Candle Countdown Badge on Current Price Line -->
        <div id="candle-timer-badge" class="candle-timer-badge">00:00</div>

        <!-- Quotex Exact Floating Timeframe Popover (Available for Mobile & Desktop) -->
        <div class="quotex-timeframe-popover" id="quotex-timeframe-popover" style="display: none;">
          <div class="tf-popover-grid">
            <button type="button" class="tf-grid-btn" data-tf="5s">5s</button>
            <button type="button" class="tf-grid-btn" data-tf="10s">10s</button>
            <button type="button" class="tf-grid-btn" data-tf="15s">15s</button>
            <button type="button" class="tf-grid-btn" data-tf="30s">30s</button>
            <button type="button" class="tf-grid-btn active" data-tf="1m">1m</button>
            <button type="button" class="tf-grid-btn" data-tf="2m">2m</button>
            <button type="button" class="tf-grid-btn" data-tf="3m">3m</button>
            <button type="button" class="tf-grid-btn" data-tf="5m">5m</button>
            <button type="button" class="tf-grid-btn" data-tf="10m">10m</button>
            <button type="button" class="tf-grid-btn" data-tf="15m">15m</button>
            <button type="button" class="tf-grid-btn" data-tf="30m">30m</button>
            <button type="button" class="tf-grid-btn" data-tf="1h">1h</button>
            <button type="button" class="tf-grid-btn" data-tf="4h">4h</button>
            <button type="button" class="tf-grid-btn" data-tf="1d">1d</button>
          </div>
        </div>

        <!-- Floating Zoom Controls -->
        <div class="chart-floating-zoom">
          <button type="button" class="zoom-btn" id="btn-zoom-out">−</button>
          <button type="button" class="zoom-btn" id="btn-zoom-in">+</button>
        </div>

        <!-- Vertical dashed trade guide lines on canvas -->
        <div class="trade-marker-lines-overlay" id="trade-marker-lines-overlay">
          <div class="trade-guide-line line-begin">
            <span>Beginning of trade ►</span>
            <div class="trade-guide-timer-badge" id="guide-timer-badge" style="display: none;">00:00</div>
          </div>
          <div class="trade-guide-line line-end"><span>◄ End of trade</span></div>
        </div>

        <!-- Active Trades on Chart (Quotex Exact: Line + Node + Pill + Timer) -->
        <div class="chart-active-trades-overlay" id="chart-active-trades-container"></div>

        <!-- FLOATING PENDING TRADE POPUP (Exact Screenshot 2 & 3) -->
        <div class="pending-trade-popup" id="pending-trade-popup" style="display: none;">
          <div class="pending-popup-tabs">
            <button type="button" class="pending-tab active" id="tab-pending-quote" onclick="switchPendingTab('quote')">QUOTE</button>
            <button type="button" class="pending-tab" id="tab-pending-time" onclick="switchPendingTab('time')">TIME</button>
          </div>
          
          <!-- Quote Mode Tab Body (Screenshot 2) -->
          <div class="pending-tab-body" id="pending-body-quote">
            <div class="pending-field-block">
              <label>Quote:</label>
              <input type="text" id="pending-quote-input" class="pending-val-input" value="1341.13">
              <div class="pending-hint-text">Current quote: <span id="pending-cur-quote-lbl">1341.22</span></div>
            </div>

            <div class="pending-field-block">
              <label>Period:</label>
              <div class="pending-period-box" id="pending-period-quote">M1</div>
            </div>

            <div class="pending-field-block">
              <label>Investment:</label>
              <div class="pending-invest-control">
                <button type="button" class="btn-step-sm" onclick="adjustPendingAmt(-1)">−</button>
                <span id="pending-invest-val" class="pending-invest-num">10 %</span>
                <button type="button" class="btn-step-sm" onclick="adjustPendingAmt(1)">+</button>
                <button type="button" class="pending-switch-link" onclick="togglePendingMode()">SWITCH</button>
              </div>
            </div>

            <div class="pending-action-buttons">
              <button type="button" class="btn-pending-call" onclick="submitPendingOrder('up')">
                <span class="btn-text">Up</span>
                <span class="arrow-round">↑</span>
              </button>
              <button type="button" class="btn-pending-put" onclick="submitPendingOrder('down')">
                <span class="btn-text">Put</span>
                <span class="arrow-round">↓</span>
              </button>
            </div>

            <div class="pending-footer-link">
              <a href="#" onclick="alert('Pending orders trigger automatically when the asset reaches the specified quote price.')">HOW IT WORKS?</a>
            </div>
          </div>

          <!-- Time Mode Tab Body (Screenshot 3) -->
          <div class="pending-tab-body" id="pending-body-time" style="display: none;">
            <div class="pending-field-block">
              <label>Time:</label>
              <input type="datetime-local" step="1" id="pending-time-input" class="pending-val-input">
              <div class="pending-hint-text">Current time: <span id="pending-cur-time-lbl">21:22:34</span></div>
            </div>

            <div class="pending-field-block">
              <label>Period:</label>
              <div class="pending-period-box" id="pending-period-time">M1</div>
            </div>

            <div class="pending-field-block">
              <label>Investment:</label>
              <div class="pending-invest-control">
                <button type="button" class="btn-step-sm" onclick="adjustPendingAmt(-1)">−</button>
                <span id="pending-invest-time-val" class="pending-invest-num">10 %</span>
                <button type="button" class="btn-step-sm" onclick="adjustPendingAmt(1)">+</button>
                <button type="button" class="pending-switch-link" onclick="togglePendingMode()">SWITCH</button>
              </div>
            </div>

            <div class="pending-action-buttons">
              <button type="button" class="btn-pending-call" onclick="submitPendingOrder('up')">
                <span class="btn-text">Up</span>
                <span class="arrow-round">↑</span>
              </button>
              <button type="button" class="btn-pending-put" onclick="submitPendingOrder('down')">
                <span class="btn-text">Put</span>
                <span class="arrow-round">↓</span>
              </button>
            </div>

            <div class="pending-footer-link">
              <a href="#" onclick="alert('Pending orders trigger automatically when the specified time is reached.')">HOW IT WORKS?</a>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- 3. RIGHT PANEL (EXACT QUOTEX SCREENSHOT) -->
    <aside class="trade-panel">
      
      <!-- Row 1: Asset Selector (Left) & Pending Trade Toggle (Right) -->
      <div class="panel-top-row">
        <!-- Pair Info Summary Header -->
        <div class="panel-section asset-summary-box">
          <div class="panel-asset-row" id="panel-asset-btn" title="Change asset">
            <span class="panel-flag-icon" id="panel-flag">{!! $renderAssetFlags($selectedAsset->symbol, $selectedAsset->flag) !!}</span>
            <span class="panel-asset-name" id="panel-symbol">{{ $selectedAsset->symbol }}</span>
            <span class="panel-payout-val" id="panel-payout">{{ $selectedAsset->payout_percent }}%</span>
            <span class="panel-asset-chevron">▼</span>
          </div>
        </div>

        <!-- Pending Trade Row with Blue iOS Toggle Switch -->
        <div class="pending-trade-row">
          <div class="pending-label-wrap">
            <span class="pending-label">PENDING TRADE</span>
          </div>
          <label class="toggle-switch">
            <input id="pending-checkbox" type="checkbox" class="pending-chk" onchange="togglePendingWindow(this.checked)">
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>

      <!-- Controls Wrapper with Quotex Fieldsets -->
      <div class="panel-controls-wrap">
        <!-- Time Fieldset -->
        <fieldset class="control-fieldset">
          <legend class="fieldset-legend" id="time-mode-label">Timer</legend>
          <div class="control-input-group">
            <button type="button" class="btn-step" id="time-minus">−</button>
            <div class="control-val-display" id="time-display">00:01:00</div>
            <button type="button" class="btn-step" id="time-plus">+</button>
          </div>
          <span class="control-switch-label" id="btn-switch-time-mode" style="cursor:pointer;" title="Switch between duration and expiration time">SWITCH TIME</span>
        </fieldset>

        <!-- Investment Fieldset -->
        <fieldset class="control-fieldset">
          <legend class="fieldset-legend">Investment</legend>
          <div class="control-input-group">
            <button type="button" class="btn-step" id="invest-minus">−</button>
            <div class="control-input-wrap">
              <input type="number" id="investment-input" value="1" min="1" step="1" class="control-input">
              <span class="currency-suffix" id="invest-suffix">$</span>
            </div>
            <button type="button" class="btn-step" id="invest-plus">+</button>
          </div>
          <span class="control-switch-label">SWITCH</span>
        </fieldset>
      </div>

      <!-- Payout row with dots line & Investment preview -->
      <div class="payout-summary-box">
        <div class="payout-sum-row">
          <span class="payout-label">Payout</span>
          <span class="payout-dots">...................................................</span>
          <b class="payout-val" id="payout-display">184.00 $</b>
        </div>
        <div class="payout-sum-row invest-row">
          <span class="payout-label">Investment</span>
          <span class="payout-dots">...................................................</span>
          <span class="payout-invest-val" id="invest-preview-display">100 $</span>
        </div>
      </div>

      <!-- Big Action Buttons (UP / DOWN with round arrow badge) -->
      <div class="trade-buttons-row">
        <button type="button" class="btn-trade btn-call" id="btn-up">
          <span class="btn-trade-title">Buy</span>
          <span class="trade-btn-circle-arrow">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
          </span>
        </button>

        <button type="button" class="btn-trade btn-put" id="btn-down">
          <span class="btn-trade-title">Sell</span>
          <span class="trade-btn-circle-arrow">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
          </span>
        </button>
      </div>


      <div class="otc-deal-notice">
        Opening deals by time is currently available only for OTC trading.
      </div>

      <!-- Trades Section Header -->
      <div class="panel-trades">
        <div class="trades-nav-header">
          <div class="trades-nav-title" id="trades-title-btn">
            <span>Trades</span>
            <span class="trades-count-pill" id="active-trades-count">{{ count($activeTrades ?? []) }}</span>
          </div>
          <div class="trades-nav-icons">
            <button type="button" class="btn-trades-icon" id="btn-toggle-history" title="History">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </button>
            <span class="trades-count-pill" id="history-trades-count">{{ count($recentTrades ?? []) }}</span>
          </div>
        </div>

        <!-- Combined Trades Scroll Container (Exact Image 1) -->
        <div class="trades-scroll-container">
          <!-- Date Header Banner (Quotex Exact) -->
          <div class="trades-date-banner" id="trades-date-banner">
            <span class="date-text" id="trades-date-text">{{ strtoupper(now()->format('j F')) }}</span>
            <span class="date-count-pill" id="trades-date-count">{{ count($activeTrades ?? []) + count($recentTrades ?? []) }}</span>
          </div>

          <!-- Active Trades Slot -->
          <div class="trades-active-slot" id="active-trades-container">
            <!-- Active deals rendered dynamically here by trade.js -->
          </div>

          <!-- Closed Trades History Slot -->
          <div class="trades-closed-slot" id="trade-list">
            @forelse($recentTrades as $trade)
              @php
                $cardProfit = \App\Services\TradeResultService::settledProfit($trade);
                $cardIsDraw = ($trade->result === 'draw' || $trade->result === 'tie');
                $cardProfitStr = ($cardProfit > 0 ? '+' : ($cardProfit < 0 ? '-' : '')) . number_format(abs($cardProfit), 2) . ' $';
                $cardDiffPips = null;
                if ($trade->close_price !== null && $trade->open_price !== null) {
                    $cardDigits = $trade->asset->digits ?? 5;
                    // Same pip convention as the engine: pip = 10^(-digits).
                    $cardDiffPips = (int) round(($trade->close_price - $trade->open_price) * pow(10, $cardDigits));
                }
              @endphp
              <div class="qx-deal-card closed-deal {{ $trade->result === 'win' ? 'deal-win' : ($cardIsDraw ? '' : 'deal-loss') }}">
                <div class="deal-card-main" onclick="this.parentElement.classList.toggle('expanded')">
                  <div class="deal-row-top">
                    <div class="deal-sym-wrap">
                      <span class="deal-expand-arrow">⌄</span>
                      <span class="deal-flag">{{ $trade->asset->flag_icon ?? $trade->asset->flag ?? '🌐' }}</span>
                      <span class="deal-sym-name">{{ Str::limit($trade->asset->symbol, 13) }}</span>
                    </div>
                    <span class="deal-duration-val">00:00:{{ sprintf('%02d', $trade->duration_seconds ?? 5) }}</span>
                  </div>
                  <div class="deal-row-mid">
                    <div class="deal-invest-dir {{ $trade->result === 'win' ? 'win' : ($cardIsDraw ? '' : 'loss') }}">
                      <span class="deal-arrow-icon">{{ $trade->result === 'win' ? '+' : ($cardIsDraw ? '=' : '-') }}</span>
                      <span class="deal-invest-val">{{ number_format($trade->investment, 0) }} $</span>
                    </div>
                    <span class="deal-payout-val {{ $trade->result === 'win' ? 'profit text-green' : ($cardIsDraw ? '' : 'loss text-red') }}">
                      {{ $cardProfitStr }}
                    </span>
                  </div>
                </div>
                <div class="deal-card-details">
                  <div class="deal-detail-row"><span>ID:</span><b>{{ $trade->id }}-{{ substr(md5($trade->id), 0, 4) }}</b></div>
                  <div class="deal-detail-row"><span>Trade Pair:</span><b>{{ $trade->asset->symbol }} {{ $trade->asset->payout_percent }}%</b></div>
                  <div class="deal-detail-row"><span>Open Price:</span><b>{{ $trade->open_price }}</b></div>
                  <div class="deal-detail-row"><span>Close Price:</span><b>{{ $trade->close_price ?? $trade->open_price }}</b></div>
                  <div class="deal-detail-row"><span>Open time:</span><b>{{ $trade->created_at->format('d.m.Y H:i:s') }}</b></div>
                  <div class="deal-detail-row"><span>Close time:</span><b>{{ $trade->updated_at->format('d.m.Y H:i:s') }}</b></div>
                  <div class="deal-detail-row"><span>Duration:</span><b>00:00:{{ sprintf('%02d', $trade->duration_seconds ?? 5) }}</b></div>
                  <div class="deal-detail-row"><span>Difference:</span><b class="{{ $trade->result === 'win' ? 'text-green' : ($cardIsDraw ? '' : 'text-red') }}">{{ $cardDiffPips === null ? '—' : (($cardDiffPips > 0 ? '+' : '') . $cardDiffPips) }}</b></div>
                </div>
              </div>
            @empty
              <div class="empty-trades-quotex-box" id="empty-history-msg" style="{{ count($activeTrades ?? []) > 0 ? 'display:none;' : '' }}">
                <p>You don't have a trade history yet. You can open a trade using the form above.</p>
              </div>
            @endforelse
          </div>
        </div>
        <div class="panel-bottom-expander">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 8l6 6H6z"/></svg>
        </div>
      </div>

    </aside>

  </div>

  <!-- 4. ASSET SELECTION MODAL (EXACT QUOTEX SCREENSHOT) -->
  <div class="asset-panel-drawer qx-asset-modal" id="asset-panel">
    <div class="drawer-header qx-modal-header">
      <div class="drawer-top-row">
        <h3 class="qx-modal-title">Select trade pair</h3>
        <button type="button" class="drawer-close" id="btn-close-asset-drawer">✕</button>
      </div>
      
      <!-- Category Tabs: CURRENCIES, CRYPTO, COMMODITIES, STOCKS -->
      <div class="asset-cat-tabs qx-cat-pills">
        <button type="button" class="cat-btn active" data-cat="forex">CURRENCIES</button>
        <button type="button" class="cat-btn" data-cat="crypto">CRYPTO</button>
        <button type="button" class="cat-btn" data-cat="commodity">COMMODITIES</button>
        <button type="button" class="cat-btn" data-cat="stock">STOCKS</button>
      </div>

      <!-- Search Row: Favorite Badge + Search Input -->
      <div class="qx-search-bar-row">
        <div class="qx-fav-badge-btn" title="Favorites">
          <span class="star-icon">★</span>
          <span class="fav-count">0</span>
        </div>
        <div class="asset-search-wrap qx-search-input-wrap">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input type="text" id="asset-search" placeholder="Search">
        </div>
      </div>

      <!-- Table Header -->
      <div class="qx-asset-table-head">
        <span class="col-th col-th-fav"></span>
        <span class="col-th col-th-name">Name</span>
        <span class="col-th col-th-change">24h changing</span>
        <span class="col-th col-th-p1">Profit 1+ min ▼</span>
        <span class="col-th col-th-p5">5+ min</span>
      </div>
    </div>
    
    <div class="asset-items-list qx-asset-table-body" id="asset-list">
      @foreach($assets as $asset)
        @php
          // Real values attached by TradeController::index() via OtcPriceEngine::assetStats():
          // 24h change from real candle closes; payouts are the stored admin values.
          $changeVal = (float) ($asset->change_24h_pct ?? 0);
          $changeStr = ($changeVal >= 0 ? '+' : '') . number_format($changeVal, 2) . '%';
          $isPositive = $changeVal >= 0;
          $payout1m = (float) ($asset->payout_1m_pct ?? $asset->payout_percent);
          $payout5m = (float) ($asset->payout_5m_pct ?? $asset->payout_percent);
        @endphp
        <div class="asset-list-item qx-asset-row {{ $asset->id == $selectedAsset->id ? 'selected' : '' }}"
             data-id="{{ $asset->id }}"
             data-symbol="{{ $asset->symbol }}"
             data-name="{{ $asset->name }}"
             data-payout="{{ $asset->payout_percent }}"
             data-flag="{{ $asset->flag ?? $asset->flag_icon ?? '🌐' }}"
             data-base="{{ $asset->current_price ?? 1.0 }}"
              @php
                $itemDigits = $asset->digits ?? null;
                $itemBase = (float)($asset->current_price ?? 1.0);
                $itemSym = strtoupper($asset->symbol ?? '');
                if ($itemDigits === null || ($itemDigits > 2 && ($itemBase >= 500 || str_contains($itemSym, 'BTC') || str_contains($itemSym, 'ETH') || str_contains($itemSym, 'GOLD') || str_contains($itemSym, 'XAU')))) {
                  $itemDigits = 2;
                } elseif ($itemDigits > 3 && ($itemBase >= 50 || str_contains($itemSym, 'JPY'))) {
                  $itemDigits = 3;
                }
              @endphp
              data-digits="{{ $itemDigits }}"
             data-cat="{{ $asset->type }}">
          <div class="qx-row-fav">☆</div>
          <div class="qx-row-identity">
            <span class="item-flag qx-flag">{!! $renderAssetFlags($asset->symbol, $asset->flag, $asset->type) !!}</span>
            <strong class="item-symbol qx-symbol">{{ $asset->symbol }}</strong>
            @if($asset->id == $selectedAsset->id)
              <span class="qx-selected-check" title="Active pair">✓</span>
            @endif
          </div>
          <div class="qx-row-change">
            <span class="qx-change-pill {{ $isPositive ? 'positive' : 'negative' }}">
              {{ $isPositive ? '↑' : '↓' }} {{ $changeStr }}
            </span>
          </div>
          <div class="qx-row-payout qx-payout-1m">{{ $payout1m }}%</div>
          <div class="qx-row-payout qx-payout-5m">{{ $payout5m }}%</div>
        </div>
      @endforeach
    </div>
  </div>

  <!-- 5. SIGNALS MODAL DRAWER -->
  <div class="side-modal-drawer" id="signals-drawer">
    <div class="side-drawer-header">
      <div class="drawer-title-row">
        <span>📡 Quotex Signals (87% Accuracy)</span>
        <button type="button" class="drawer-close" id="btn-close-signals">✕</button>
      </div>
      <small class="text-muted">Real-time algorithmic trading recommendations</small>
    </div>
    <div class="signals-list">
      <div class="signal-card" data-symbol="USD/NGN" data-dir="up" data-dur="60">
        <div class="sig-hdr">
          <strong>🇺🇸🇳🇬 USD/NGN</strong>
          <span class="sig-conf text-green">89% Confidence</span>
        </div>
        <div class="sig-body">
          <span class="sig-badge up">↑ CALL (UP)</span>
          <span class="sig-tf">Time: 1m</span>
        </div>
        <button type="button" class="btn-copy-sig">Copy Signal</button>
      </div>

      <div class="signal-card" data-symbol="EUR/USD" data-dir="up" data-dur="60">
        <div class="sig-hdr">
          <strong>🇪🇺🇺🇸 EUR/USD</strong>
          <span class="sig-conf text-green">87% Confidence</span>
        </div>
        <div class="sig-body">
          <span class="sig-badge up">↑ CALL (UP)</span>
          <span class="sig-tf">Time: 1m</span>
        </div>
        <button type="button" class="btn-copy-sig">Copy Signal</button>
      </div>

      <div class="signal-card" data-symbol="BTC/USD" data-dir="down" data-dur="120">
        <div class="sig-hdr">
          <strong>₿🇺🇸 BTC/USD</strong>
          <span class="sig-conf text-yellow">83% Confidence</span>
        </div>
        <div class="sig-body">
          <span class="sig-badge down">↓ PUT (DOWN)</span>
          <span class="sig-tf">Time: 2m</span>
        </div>
        <button type="button" class="btn-copy-sig">Copy Signal</button>
      </div>
    </div>
  </div>

  <!-- 6. EXACT QUOTEX LEADER BOARD OF THE DAY (DOM & Screenshot Exact) -->
  <div class="M_p_O qx-leaderboard-drawer" id="leaderboard-drawer" style="display: none;">
    <div class="xJo6L">
      <button type="button" class="lb-nav-btn" onclick="closeLeaderboardDrawer()" title="Back">‹</button>
      <div class="kQm9I">
        <div class="hLMh1">
          Leader Board
        </div>
        <div class="Rx6ee">
          of the Day
        </div>
      </div>
      <button type="button" class="lb-nav-btn" onclick="closeLeaderboardDrawer()" title="Close">✕</button>
    </div>

    <div class="xQvJK">
      <div class="egHS4">
        <div class="Bsca8">
          <div class="d6ijp">
            <div class="lb-user-flag">
              <img src="/images/flags/pk.svg" alt="pk">
            </div>
            <p>
              #{{ $user ? (str_pad($user->id, 8, '930', STR_PAD_LEFT)) : '93090578' }}
            </p>
          </div>
          <div class="ord28 o8xRM">
            ${{ number_format($todayProfit ?? 0, 2) }}
          </div>
        </div>
        <div class="c_7BP">
          <div class="YkAuV">
            Your position:
          </div>
          -
        </div>
      </div>
      <div class="Fyul2" onclick="showRatingWorkInfo()">
        <span style="font-size: 14px; margin-right: 6px;">🧰</span>
        <div>
          How does this rating work?
        </div>
      </div>
    </div>

    <div class="tlUK7">
      <!-- 1 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM rank-1">
            <div class="FdU8g">
              1
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/pk.svg" alt="pk"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#1e3c72,#2a5298);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="QukingTRADER">
            QukingTRADER
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $30,000.00+
        </div>
      </div>

      <!-- 2 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM rank-2">
            <div class="FdU8g">
              2
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/pk.svg" alt="pk"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#ff416c,#ff4b2b);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="Acterph">
            Acterph
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $30,000.00+
        </div>
      </div>

      <!-- 3 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM rank-3">
            <div class="FdU8g">
              3
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/bd.svg" alt="bd"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#00b09b,#96c93d);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="HT07 PERSONAL( JANNATUL FERDOUS)">
            HT07 PERSONAL( JANNATUL FERDOUS)
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $28,431.00
        </div>
      </div>

      <!-- 4 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              4
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/eg.svg" alt="eg"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#8a2387,#e94057);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="TradeWithX">
            TradeWithX
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $28,340.00
        </div>
      </div>

      <!-- 5 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              5
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/in.svg" alt="in"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#3a7bd5,#3a6073);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="Sangeeta">
            Sangeeta
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $27,555.00
        </div>
      </div>

      <!-- 6 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              6
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/bd.svg" alt="bd"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#4b6cb7,#182848);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="QM VIP - News King">
            QM VIP - News King
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $25,588.60
        </div>
      </div>

      <!-- 7 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              7
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/in.svg" alt="in"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#11998e,#38ef7d);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="#93108189">
            #93108189
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $25,396.10
        </div>
      </div>

      <!-- 8 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              8
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/mx.svg" alt="mx"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#fc4a1a,#f7b733);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="CristianBLW">
            CristianBLW
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $21,877.00
        </div>
      </div>

      <!-- 9 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              9
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/in.svg" alt="in"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#302b63,#24243e);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="BADSHAH ON TOP">
            BADSHAH ON TOP
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $20,842.25
        </div>
      </div>

      <!-- 10 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              10
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/in.svg" alt="in"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#1f4037,#99f2c8);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="#Momai">
            #Momai
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $19,990.60
        </div>
      </div>

      <!-- 11 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              11
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/ae.svg" alt="ae"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#654ea3,#eaafc8);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="Milan Jain">
            Milan Jain
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $19,986.41
        </div>
      </div>

      <!-- 12 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              12
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/in.svg" alt="in"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#134e5e,#71b280);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="Jai Baba ki">
            Jai Baba ki
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $19,950.00
        </div>
      </div>

      <!-- 13 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              13
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/in.svg" alt="in"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#0052d4,#4364f7);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="TW_PARUL⚡">
            TW_PARUL⚡
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $18,835.20
        </div>
      </div>

      <!-- 14 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              14
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/in.svg" alt="in"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#f857a6,#ff5858);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="ANYAA TRADER">
            ANYAA TRADER
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $17,968.47
        </div>
      </div>

      <!-- 15 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              15
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/eg.svg" alt="sy"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#2c3e50,#3498db);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="عزيز سوريا TOP1">
            عزيز سوريا TOP1
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $17,062.00
        </div>
      </div>

      <!-- 16 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              16
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/bd.svg" alt="bd"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#e65c00,#f9d423);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="TRADING BY PIYASH বাটপারদের যম">
            TRADING BY PIYASH বাটপারদের যম
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $15,848.22
        </div>
      </div>

      <!-- 17 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              17
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/in.svg" alt="in"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#1d2671,#c33764);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="Mahadev Trader Farhan Personal">
            Mahadev Trader Farhan Personal
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $13,282.42
        </div>
      </div>

      <!-- 18 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              18
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/in.svg" alt="in"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#2b5876,#4e4376);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="Tradernaaz">
            Tradernaaz
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $13,205.00
        </div>
      </div>

      <!-- 19 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              19
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/in.svg" alt="in"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#3f2b96,#a8c0ff);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="ROHIT">
            ROHIT
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $13,172.95
        </div>
      </div>

      <!-- 20 -->
      <div class="CYmPX">
        <div class="spCR5">
          <div class="anZcM">
            <div class="FdU8g _0J2mJ">
              20
            </div>
          </div>
          <div class="HPYiu">
            <div class="lb-country-flag"><img src="/images/flags/bd.svg" alt="bd"></div>
            <div class="QyASJ">
              <div class="avatar-default-icon" style="background:linear-gradient(135deg,#b92b27,#1565c0);width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;">👤</div>
            </div>
          </div>
          <div class="hKWVz" title="Alveefx Personal Signal Rajib">
            Alveefx Personal Signal Rajib
          </div>
        </div>
        <div class="ePgNa iXGFm">
          $12,971.00
        </div>
      </div>
    </div>
  </div>

  <!-- EXACT QUOTEX "HOW DOES IT WORK?" MODAL (Matching Screenshot) -->
  <div class="qx-rating-info-modal-backdrop" id="rating-info-modal" style="display: none;" onclick="closeRatingWorkModal(event)">
    <div class="qx-rating-info-card" onclick="event.stopPropagation()">
      <button type="button" class="qx-rating-info-close" onclick="closeRatingWorkModal()">&times;</button>
      
      <div class="qx-rating-info-header">
        <div class="qx-rating-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <rect x="3" y="6" width="18" height="15" rx="3" fill="#f59e0b"/>
            <rect x="7" y="3" width="10" height="4" rx="1.5" fill="#f59e0b"/>
            <rect x="10.5" y="8" width="3" height="4" rx="0.5" fill="#1e293b"/>
          </svg>
        </div>
        <div class="qx-rating-header-text">
          <div class="qx-rating-sub">Top</div>
          <div class="qx-rating-title">How does it work?</div>
        </div>
      </div>

      <div class="qx-rating-desc-box">
        <p class="qx-rating-intro">All traders on our platform participate in this rating. Main features of the rating:</p>
        <ul class="qx-rating-list">
          <li>Calculated every day, from 00:00 UTC to 23:59 UTC</li>
          <li>Participants can only be those who trade in live mode</li>
          <li>Your position depends on the amount of earned money</li>
        </ul>
      </div>

      <div class="qx-my-rating-sec">
        <div class="qx-my-rating-title">My rating in the Leader Board</div>
        <div class="qx-my-rating-track">
          <div class="qx-my-rating-fill" style="width: 0%;"></div>
        </div>
        <div class="qx-my-rating-bottom">
          <div class="qx-my-user-left">
            <span class="qx-my-flag">
              <img src="/images/flags/pk.svg" alt="PK">
            </span>
            <span class="qx-my-id">{{ $user ? (str_pad($user->id, 8, '930', STR_PAD_LEFT)) : '93090578' }}</span>
          </div>
          <div class="qx-my-user-right">
            <span>Your position: -</span>
            <span class="qx-my-dot">•</span>
            <span class="qx-my-profit">${{ number_format($todayProfit ?? 0, 2) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 7. EXACT QUOTEX SETTINGS SIDEPANEL (DOM & Screenshot Exact) -->
  <aside class="FczWc app__sidepanel U6IJq HGMSN S0jcI settings-sidepanel" id="settings-sidepanel" dir="ltr" style="display: none;">
    <div class="E8RlX">
      <div class="RRe9J">
        <div class="settings-panel-header">
          <p class="LWRnv settings-panel-title">Settings</p>
          <button type="button" class="settings-panel-close" onclick="toggleSettingsSidepanel()">✕</button>
        </div>
        
        <div class="J8yrL settings-controls-container">
          <!-- Language Select -->
          <label class="input-control input-control--select input-control--dark hXCvw" for="language-select">
            <span class="input-control__label">Language</span>
            <input class="input-control__input" id="language-select" type="hidden" value="en">
            <div class="input-control__value">English</div>
          </label>

          <!-- Timezone Select -->
          <label class="input-control input-control--select input-control--dark" for="timezone-select">
            <span class="input-control__label">Timezone</span>
            <input class="input-control__input" id="timezone-select" type="hidden" value="18000">
            <div class="input-control__value">(UTC+05:00)</div>
          </label>

          <!-- Template Selection -->
          <div class="Vd53z settings-block">
            <p class="XgJ2S settings-sub-title">Template</p>
            <label class="Zxbe5 theme-radio-opt">
              <div class="T_Qzx">
                <div class="Gch4K">Light Mode</div>
              </div>
              <span><input type="radio" name="theme_template" value="light"></span>
            </label>
            <label class="Zxbe5 theme-radio-opt">
              <div class="T_Qzx">
                <div class="Gch4K">Twilight</div>
              </div>
              <span><input type="radio" name="theme_template" value="twilight"></span>
            </label>
            <label class="Zxbe5 theme-radio-opt">
              <div class="T_Qzx MSpPE">
                <div class="Gch4K">Full Night</div>
              </div>
              <span><input type="radio" name="theme_template" value="night" checked></span>
            </label>
          </div>

          <!-- Platform Settings -->
          <div class="jny_1 settings-block">
            <div class="UTFT_ settings-sub-title">Platform</div>
            
            <div class="pI5vA">
              <label class="input-control input-control--number input-control--dark">
                <span class="input-control__label">Grid's opacity</span>
                <div class="grid-opacity-ctrl">
                  <button type="button" class="input-control__button" onclick="adjustGridOpacity(-1)">−</button>
                  <input class="input-control__input" type="text" id="gridsopacity-val" value="8" readonly>
                  <button type="button" class="input-control__button" onclick="adjustGridOpacity(1)">+</button>
                </div>
              </label>
            </div>

            <div class="pI5vA setting-chk-row">
              <label class="QH0iF THkhw">
                <input type="checkbox" class="MKlND" id="chk-auto-scroll" checked>
                <span>Auto-scrolling</span>
              </label>
              <p class="qv0wV">Automatic graphic scrolling</p>
            </div>

            <div class="pI5vA setting-chk-row">
              <label class="QH0iF THkhw">
                <input type="checkbox" class="MKlND" id="chk-one-click" checked>
                <span>1-click trade</span>
              </label>
              <p class="qv0wV">Open trades without confirmation</p>
            </div>

            <div class="pI5vA setting-chk-row">
              <label class="QH0iF THkhw">
                <input type="checkbox" class="MKlND" id="chk-performance-mode" checked>
                <span>Perfomance Mode</span>
              </label>
              <p class="qv0wV">Use optimized rendering for chart and candles</p>
            </div>

            <div class="pI5vA setting-chk-row">
              <label class="QH0iF THkhw">
                <input type="checkbox" class="MKlND" id="chk-short-label">
                <span>Short order label</span>
              </label>
              <p class="qv0wV">Use short order element mode</p>
            </div>

            <div class="pI5vA setting-chk-row">
              <label class="QH0iF THkhw">
                <input type="checkbox" class="MKlND" id="chk-hide-results">
                <span>Hide trade results</span>
              </label>
              <p class="qv0wV">Use hide trade results mode</p>
            </div>
          </div>

          <!-- Graph Colors -->
          <div class="gpGYh settings-block">
            <p class="eYeZo settings-sub-title">Graph colors</p>
            <div class="taBCa graph-color-row">
              <div class="KvX9A">Up Trend</div>
              <input type="color" id="up-trend-color" value="#00c853" class="color-picker-input">
            </div>
            <div class="taBCa graph-color-row">
              <div class="KvX9A">Down Trend</div>
              <input type="color" id="down-trend-color" value="#ff3b53" class="color-picker-input">
            </div>
          </div>

          <!-- Background Upload -->
          <div class="DtLeD settings-block">
            <p class="JTI5P settings-sub-title">Background</p>
            <label class="Q0zrU bg-upload-label">
              <input class="n5q53" type="file" accept="image/*" onchange="handleChartBackgroundUpload(this)" style="display:none;">
              <div class="kI6KL">
                <div class="pV2Zn">Choose file</div>
                <div class="zShVT">(Max size — 2 Mb)</div>
              </div>
            </label>
            <button type="button" id="btn-remove-chart-bg" class="bg-remove-btn" onclick="removeChartBackground()" style="display:none;">Remove background</button>
          </div>
        </div>
      </div>
    </div>
  </aside>

  <!-- Exact Quotex Trading Signals Slide-out Drawer (Matching Screenshot) -->
  <div id="trading-signals-drawer" class="qx-signals-drawer" style="display: none;">
    <div class="signals-drawer-backdrop" onclick="closeSignalsDrawer()"></div>
    <div class="signals-drawer-content">
      <div class="signals-drawer-header">
        <div class="signals-header-left" onclick="closeSignalsDrawer()">
          <span class="signals-back-arrow">‹</span>
          <span class="signals-title">Trading signals</span>
        </div>
        <button type="button" class="signals-drawer-close" onclick="closeSignalsDrawer()">✕</button>
      </div>

      <div class="signals-help-link-wrap">
        <a href="javascript:void(0);" onclick="alert('Trading signals provide real-time recommendations for profitable asset trades based on advanced market indicators and admin analysis.')" class="signals-help-link">WHAT'S IT?</a>
      </div>

      <div class="signals-drawer-body">
        <!-- Live Tradeable Signals -->
        <div id="active-signals-container" class="signals-list-block">
          <div id="active-signals-list" class="signals-list">
            <!-- Populated via JS / API -->
          </div>
        </div>

        <!-- Past / Expired Signals (Cannot be traded) -->
        <div class="past-signals-header">
          <span>PAST SIGNALS</span>
        </div>
        <div id="past-signals-container" class="signals-list-block">
          <div id="past-signals-list" class="signals-list past-list">
            <!-- Populated via JS / API -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Active Trades Drawer (Screenshot 2 & 3 Exact) -->
  <div class="mobile-trades-drawer" id="mobile-trades-drawer" style="display: none;">
    <div class="mobile-drawer-backdrop" onclick="toggleMobileTradesDrawer()"></div>
    <div class="mobile-drawer-sheet">
      <div class="mobile-drawer-sheet-header">
        <div class="mobile-drawer-tabs">
          <button type="button" class="mobile-d-tab active" id="mob-tab-trades" onclick="switchMobileDrawerTab('trades')">
            Trades <span class="mob-d-badge" id="mob-drawer-active-count">0</span>
          </button>
          <button type="button" class="mobile-d-tab" id="mob-tab-orders" onclick="switchMobileDrawerTab('orders')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span class="mob-d-badge" id="mob-drawer-pending-count">0</span>
          </button>
        </div>
        <button type="button" class="mobile-drawer-close" onclick="toggleMobileTradesDrawer()">✕</button>
      </div>
      <div class="mobile-drawer-body">
        <div id="mobile-drawer-active-list" class="mobile-drawer-list"></div>
        <div id="mobile-drawer-orders-list" class="mobile-drawer-list" style="display: none;">
          <div class="empty-trades-quotex-box">
            <p>No pending orders</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- TOAST NOTIFICATION -->
  <div id="trade-toast" class="trade-toast">
    <div class="toast-title">Notification</div>
    <div class="toast-body">Message here</div>
  </div>

  <!-- Trade confirm modal (shown when 1-click trade is OFF instead of a native confirm()) -->
  <!-- Top-level sibling of the toast, NOT nested inside it: the toast is
       opacity:0 / pointer-events:none until shown, which made the modal
       invisible and unclickable while it lived inside the toast element. -->
  <div id="trade-confirm-modal" class="trade-confirm-backdrop" style="display:none;">
    <div class="trade-confirm-box">
      <div class="trade-confirm-title">Confirm trade</div>
      <p id="trade-confirm-text" class="trade-confirm-text"></p>
      <div class="trade-confirm-actions">
        <button type="button" class="trade-confirm-btn cancel" onclick="closeTradeConfirm()">Cancel</button>
        <button type="button" class="trade-confirm-btn ok" onclick="confirmTradePlacement()">Confirm</button>
      </div>
    </div>
  </div>

  <!-- LightweightCharts Standalone -->
  <script src="https://unpkg.com/lightweight-charts@4.1.1/dist/lightweight-charts.standalone.production.js"></script>
  <script>
    window.SERVER_TIMESTAMP = {{ time() }};
    window.INITIAL_ACTIVE_TRADES = {!! json_encode($activeTrades ?? []) !!};
  </script>
  <script src="/js/trade.js?v={{ time() }}"></script>
</body>
</html>
