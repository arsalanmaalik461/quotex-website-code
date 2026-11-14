<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'QUOTEX — Innovative platform for smart investments')</title>
  <meta name="theme-color" content="#1C1F2D">
  <meta name="color-scheme" content="dark">
  <meta name="description" content="Quotex is the official website of the smart investment trading platform. Sign up and get 10,000 USD to your demo account to learn how to trade.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/app.css?v={{ time() }}">
  @stack('styles')
</head>
<body class="market-qx-body">
  @include('layouts.preloader')

  <!-- Navigation -->
  <nav class="qx-navbar">
    <div class="qx-nav-container">
      <a href="{{ route('home') }}" class="qx-brand">
        <img src="/profile/images/logo-dark.svg" alt="Quotex" height="26" style="height:26px;width:auto;display:block;">
      </a>

      <ul class="qx-nav-menu" id="qx-nav-menu">
        <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About us</a></li>
        <li><a href="{{ route('faq') }}" class="{{ request()->routeIs('faq') ? 'active' : '' }}">FAQ</a></li>
        <li><a href="{{ route('blog') }}" class="{{ request()->routeIs('blog') ? 'active' : '' }}">Blog</a></li>
        @auth
          <li class="qx-drawer-auth-item"><a href="{{ route('trade') }}">Trading Platform</a></li>
          <li class="qx-drawer-auth-item">
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
              @csrf
              <button type="submit" class="qx-btn-logout-drawer">Log out</button>
            </form>
          </li>
        @else
          <li class="qx-drawer-auth-item"><a href="{{ route('login') }}">Log in</a></li>
          <li class="qx-drawer-auth-item"><a href="{{ route('register') }}" style="color:var(--qx-green);">Sign up</a></li>
        @endauth
      </ul>

      <div class="qx-nav-right">
        @auth
          <a href="{{ route('trade') }}" class="qx-btn-green qx-nav-trade-btn">
            <span class="desktop-text">Trading Platform</span>
            <span class="mobile-text">Trade</span>
          </a>
          <form action="{{ route('logout') }}" method="POST" style="display:inline;" class="qx-nav-logout-form">
            @csrf
            <button type="submit" class="qx-btn-dark">Log out</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="qx-btn-dark qx-nav-login-btn">Log in</a>
          <a href="{{ route('register') }}" class="qx-btn-green qx-nav-signup-btn">Sign up</a>
        @endauth

        <div class="qx-lang-btn">
          <span>EN</span>
        </div>

        <button type="button" class="qx-hamburger" id="qx-mobile-nav-toggle" aria-label="Toggle navigation">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main>
    @yield('content')
  </main>

  <!-- Footer -->
  <footer class="qx-footer">
    <div class="qx-footer-inner">
      <div class="qx-footer-top-grid">
        
        <!-- Col 1: Brand -->
        <div class="qx-footer-brand-col">
          <div class="qx-brand">
            <img src="/profile/images/logo-dark.svg" alt="Quotex" height="26" style="height:26px;width:auto;display:block;">
          </div>
        </div>

        <!-- Col 2: FAQ -->
        <div class="qx-footer-links-col">
          <h4>FAQ <span class="qx-chevron">›</span></h4>
          <ul>
            <li><a href="{{ route('faq') }}">General questions</a></li>
            <li><a href="{{ route('faq') }}">Financial questions</a></li>
            <li><a href="{{ route('faq') }}">Verification</a></li>
          </ul>
        </div>

        <!-- Col 3: About us -->
        <div class="qx-footer-links-col">
          <h4>About us <span class="qx-chevron">›</span></h4>
          <ul>
            <li><a href="{{ route('about') }}">Reviews</a></li>
            <li><a href="{{ route('about') }}">Contacts</a></li>
          </ul>
        </div>

        <!-- Col 4: More -->
        <div class="qx-footer-links-col">
          <h4>More</h4>
          <ul>
            <li><a href="{{ route('register') }}">Affiliate program</a></li>
          </ul>
        </div>

        <!-- Col 5: Downloads & Socials -->
        <div class="qx-footer-apps-col">
          <div class="qx-apps-title">Download the app</div>
          <div class="qx-store-badges">
            <a href="{{ route('app.download') }}" class="qx-store-badge">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M3.609 1.814L13.793 12 3.61 22.186a2.44 2.44 0 0 1-.22-.968V2.782c0-.363.078-.7.22-.968zM15.207 13.414l2.457 2.457-11.83 6.76 9.373-9.217zm0-2.828L5.834 1.369l11.83 6.76-2.457 2.457zm1.414 1.414l3.197 1.827a1.44 1.44 0 0 0 0-2.494l-3.197-1.827 1.414 2.494z"/></svg>
              <div>
                <span class="badge-sub">GET IT ON</span>
                <span class="badge-main">Google Play</span>
              </div>
            </a>

            <a href="{{ route('app.download') }}" class="qx-store-badge">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.93-2.85-.9.04-1.99.6-2.64 1.35-.57.65-1.07 1.72-.94 2.74 1 .08 2.03-.49 2.65-1.24z"/></svg>
              <div>
                <span class="badge-sub">Progressive</span>
                <span class="badge-main">Web App</span>
              </div>
            </a>
          </div>

          <div class="qx-socials-box">
            <div class="qx-social-title">Follow us on social media</div>
            <div class="qx-social-buttons">
              <a href="{{ route('home') }}" class="qx-social-btn fb">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                <span>32K+</span>
              </a>
              <a href="{{ route('home') }}" class="qx-social-btn ig">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                <span>110K+</span>
              </a>
              <a href="{{ route('home') }}" class="qx-social-btn tg">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                <span>390K+</span>
              </a>
            </div>
          </div>
        </div>

      </div>

      <!-- Legal & Regulatory Warnings -->
      <div class="qx-footer-legal-row">
        <div class="qx-legal-links">
          <span class="legal-head">Regulations</span>
          <a href="{{ route('faq') }}#privacy">Privacy policy</a>
          <a href="{{ route('faq') }}#service">Service agreement</a>
          <a href="{{ route('faq') }}#risk">Risk disclosure</a>
          <a href="{{ route('faq') }}#trading-rules">Rules of trading operations</a>
          <a href="{{ route('faq') }}#financial">Non-trading operations regulations</a>
          <a href="{{ route('faq') }}#payment">Payment policy</a>
        </div>

        <div class="qx-disclaimer-text">
          <p>ON SPOT GROUP LLC. Address: Main Street, P.O. Box 625, Charlestown, St. Kitts and Nevis.</p>
          <p>The website services are not available in a number of countries, including USA, Canada, Hong Kong, EEA countries, Israel, Russia as well as for persons under 18 years of age.</p>
          <p><strong>Risk Warning:</strong> Trading Forex and Leveraged Financial Instruments involves significant risk and can result in the loss of your invested capital. You should not invest more than you can afford to lose and should ensure that you fully understand the risks involved. Trading leveraged products may not be suitable for all investors. Trading non-leveraged products such as stocks also involves risk as the value of a stock can fall as well as rise, which could mean getting back less than you originally put in. Past performance is no guarantee of future results. Before trading, please take into consideration your level of experience, investment objectives and seek independent financial advice if necessary. It is the responsibility of the Client to ascertain whether he/she is permitted to use the services of the Quotex brand based on the legal requirements in his/her country of residence.</p>
          <p class="copy">{{ config('app.name', 'The platform') }} is the owner of this website.<br>Copyright &copy; {{ date('Y') }} {{ config('app.name', 'Quotex') }}. All rights reserved</p>
        </div>
      </div>
    </div>
  </footer>

  <script src="/js/app.js"></script>
  @if(request()->routeIs('home'))
    @include('partials.app-download-popup')
    <script src="/js/app-download-popup.js"></script>
  @endif
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const toggle = document.getElementById('qx-mobile-nav-toggle');
      const menu = document.getElementById('qx-nav-menu');
      if (toggle && menu) {
        toggle.addEventListener('click', function(e) {
          e.stopPropagation();
          toggle.classList.toggle('open');
          menu.classList.toggle('open');
        });
        document.addEventListener('click', function(e) {
          if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            toggle.classList.remove('open');
            menu.classList.remove('open');
          }
        });
      }
    });
  </script>
  @stack('scripts')
</body>
</html>
