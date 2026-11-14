@extends('layouts.app')

@section('title', 'QUOTEX — Innovative platform for smart investments')

@section('content')
<!-- HERO SECTION -->
<section class="qx-hero">
  <div class="qx-hero-inner">
    <h1 class="qx-hero-title">
      Innovative platform for<br>
      smart investments
    </h1>
    <p class="qx-hero-sub">
      Sign up and get 10,000 USD to your demo account to learn how to trade.
    </p>

    <div class="qx-hero-cta-row">
      <a href="{{ route('register') }}" class="qx-hero-btn-create">Create a free account</a>
      <span class="qx-hero-min-deposit">* The minimum deposit amount to start real trading is 10 USD</span>
    </div>

    <!-- Exact Web Trading Platform Terminal Mockup -->
    <div class="qx-terminal-window">
      <!-- Terminal Header -->
      <div class="qx-term-topbar">
        <div class="qx-term-left">
          <div class="qx-term-menu-btn">
            <span></span><span></span><span></span>
          </div>
          <div class="qx-term-logo" style="display:flex;align-items:center;gap:10px;">
            <img src="/profile/images/logo-dark.svg" alt="Quotex" height="20" style="height:20px;width:auto;display:block;">
            <span class="qx-term-badge">WEB TRADING PLATFORM</span>
          </div>
        </div>

        <div class="qx-term-right">
          <div class="qx-term-icon-btn">🔔<span class="qx-term-dot">1</span></div>
          <div class="qx-term-acc-pill">
            <span class="acc-badge">LIVE ACCOUNT</span>
            <span class="acc-val">$12,766.66 ▾</span>
          </div>
          <button class="qx-term-btn-dep">+ Deposit</button>
          <button class="qx-term-btn-wdr">Withdrawal</button>
        </div>
      </div>

      <!-- Terminal Body -->
      <div class="qx-term-body">
        <!-- Left slim toolbar -->
        <div class="qx-term-sidebar">
          <div class="term-tool-item active">📊<span>Trade</span></div>
          <div class="term-tool-item">🎧<span>Support</span></div>
          <div class="term-tool-item">👤<span>Account</span></div>
          <div class="term-tool-item">🏆<span class="num-badge">2</span><span>Tournaments</span></div>
          <div class="term-tool-item">💎<span>Market</span></div>
          <div class="term-tool-item">⋯<span>More</span></div>
          <div class="term-tool-spacer"></div>
          <div class="term-tool-item">⚙️</div>
          <div class="term-tool-item term-help-btn">Help</div>
        </div>

        <!-- Center chart -->
        <div class="qx-term-chart">
          <!-- Asset Tabs Strip -->
          <div class="qx-term-tabs">
            <div class="term-tab-btn">+</div>
            <div class="term-asset-tab">
              <span>🇦🇺/🇨🇭 AUD/CHF</span>
              <span class="tab-pct">86%</span>
              <span class="tab-close">✕</span>
            </div>
            <div class="term-asset-tab">
              <span>🇺🇸/🇨🇦 USD/CAD</span>
              <span class="tab-pct">88%</span>
              <span class="tab-close">✕</span>
            </div>
            <div class="term-asset-tab active">
              <span>🇪🇬 USD/EGP (OTC)</span>
              <span class="tab-pct">93%</span>
              <span class="tab-close">✕</span>
            </div>
          </div>

          <!-- Realistic Candlestick Canvas SVG -->
          <div class="qx-term-svg-wrap">
            <svg class="qx-term-svg" viewBox="0 0 700 360" preserveAspectRatio="none">
              <defs>
                <linearGradient id="termGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#00c853" stop-opacity="0.2"/>
                  <stop offset="100%" stop-color="#00c853" stop-opacity="0.0"/>
                </linearGradient>
              </defs>
              <!-- Grid lines -->
              <line x1="0" y1="60" x2="700" y2="60" stroke="#1f2636" stroke-width="1"/>
              <line x1="0" y1="120" x2="700" y2="120" stroke="#1f2636" stroke-width="1"/>
              <line x1="0" y1="180" x2="700" y2="180" stroke="#1f2636" stroke-width="1"/>
              <line x1="0" y1="240" x2="700" y2="240" stroke="#1f2636" stroke-width="1"/>
              <line x1="0" y1="300" x2="700" y2="300" stroke="#1f2636" stroke-width="1"/>

              <!-- Candlesticks mockup -->
              <!-- Green candles -->
              <line x1="60" y1="180" x2="60" y2="250" stroke="#00c853" stroke-width="1.5"/>
              <rect x="54" y="195" width="12" height="40" fill="#00c853"/>

              <line x1="100" y1="160" x2="100" y2="230" stroke="#00c853" stroke-width="1.5"/>
              <rect x="94" y="170" width="12" height="45" fill="#00c853"/>

              <!-- Red candle -->
              <line x1="140" y1="170" x2="140" y2="270" stroke="#ff3b53" stroke-width="1.5"/>
              <rect x="134" y="180" width="12" height="70" fill="#ff3b53"/>

              <!-- Big Green candle -->
              <line x1="180" y1="110" x2="180" y2="240" stroke="#00c853" stroke-width="1.5"/>
              <rect x="174" y="130" width="12" height="95" fill="#00c853"/>

              <!-- Big Red candle -->
              <line x1="220" y1="130" x2="220" y2="310" stroke="#ff3b53" stroke-width="1.5"/>
              <rect x="214" y="150" width="12" height="130" fill="#ff3b53"/>

              <!-- Green candle -->
              <line x1="260" y1="200" x2="260" y2="280" stroke="#00c853" stroke-width="1.5"/>
              <rect x="254" y="215" width="12" height="50" fill="#00c853"/>

              <!-- Red candle -->
              <line x1="300" y1="190" x2="300" y2="290" stroke="#ff3b53" stroke-width="1.5"/>
              <rect x="294" y="210" width="12" height="60" fill="#ff3b53"/>

              <!-- Green candle -->
              <line x1="340" y1="170" x2="340" y2="240" stroke="#00c853" stroke-width="1.5"/>
              <rect x="334" y="180" width="12" height="40" fill="#00c853"/>

              <!-- Trade indicator line -->
              <line x1="340" y1="180" x2="520" y2="180" stroke="#3b82f6" stroke-width="1.5" stroke-dasharray="4"/>
              <line x1="520" y1="0" x2="520" y2="360" stroke="#ef4444" stroke-width="1.5" stroke-dasharray="2"/>

              <!-- Current Price Line -->
              <line x1="0" y1="205" x2="700" y2="205" stroke="#3b82f6" stroke-width="1"/>
            </svg>

            <!-- Price axis badges -->
            <div class="qx-price-scale">
              <span>44.0020</span>
              <span>44.0010</span>
              <span>44.0000</span>
              <span>43.9990</span>
              <span>43.9980</span>
              <span>43.9970</span>
              <span>43.9960</span>
              <span class="active-price-tag">43.9944</span>
              <span>43.9930</span>
              <span>43.9920</span>
            </div>
          </div>
        </div>

        <!-- Right order controls -->
        <div class="qx-term-panel">
          <div class="term-panel-pair">
            <span class="flag">🇪🇬</span>
            <div>
              <strong>USD/EGP (OTC)</strong>
              <small>93%</small>
            </div>
          </div>

          <div class="term-ctrl-box">
            <div class="term-ctrl-header">
              <span>Time</span>
              <span class="switch-link">SWITCH TIME</span>
            </div>
            <div class="term-stepper">
              <button>−</button>
              <span>00:01:00</span>
              <button>+</button>
            </div>
          </div>

          <div class="term-ctrl-box">
            <div class="term-ctrl-header">
              <span>Investment</span>
              <span class="switch-link">SWITCH</span>
            </div>
            <div class="term-stepper">
              <button>−</button>
              <span>15 $</span>
              <button>+</button>
            </div>
          </div>

          <div class="term-payout-row">
            <span>Your payout:</span>
            <strong>28.95 $</strong>
          </div>

          <button class="term-btn-up">
            <span>Up</span>
            <span class="arrow">↑</span>
          </button>

          <button class="term-btn-down">
            <span>Down</span>
            <span class="arrow">↓</span>
          </button>

          <div class="term-mini-history">
            <div class="history-hdr">
              <span>Trades <span class="cnt">0</span></span>
              <span>21 MAY ▾</span>
            </div>
            <div class="mini-trade-item">
              <span class="flag">🇦🇺/🇨🇭 AUD/CHF</span>
              <span class="gain text-green">+27.90 $</span>
            </div>
            <div class="mini-trade-item">
              <span class="flag">🇦🇺/🇨🇭 AUD/CHF</span>
              <span class="gain text-green">+18.60 $</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES OF THE PLATFORM -->
<section class="qx-features-section">
  <div class="qx-section-container">
    <div class="qx-sec-header">
      <h2 class="qx-sec-title">Features of the platform</h2>
      <p class="qx-sec-sub">We regularly improve our platform to make your trading comfortable and safe.</p>
    </div>

    <div class="qx-features-grid">
      <!-- 1. User-friendly interface -->
      <div class="qx-f-card">
        <div class="qx-f-icon purple">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/></svg>
        </div>
        <h3 class="qx-f-title">User-friendly interface</h3>
        <p class="qx-f-text">You have access to all the trading instruments you need, and their speed is impressive.</p>
        <a href="{{ route('register') }}" class="qx-f-link">Sign up →</a>
      </div>

      <!-- 2. Integrated signals -->
      <div class="qx-f-card">
        <div class="qx-f-icon orange">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="2"/><path d="M16.24 7.76a6 6 0 0 1 0 8.49M7.76 16.24a6 6 0 0 1 0-8.49M19.07 4.93a10 10 0 0 1 0 14.14M4.93 19.07a10 10 0 0 1 0-14.14"/></svg>
        </div>
        <h3 class="qx-f-title">Integrated signals</h3>
        <p class="qx-f-text">Signals with 87% accuracy rate will help you to build a profitable strategy.</p>
        <a href="{{ route('register') }}" class="qx-f-link">Try it →</a>
      </div>

      <!-- 3. Trading indicators -->
      <div class="qx-f-card">
        <div class="qx-f-icon blue">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <h3 class="qx-f-title">Trading indicators</h3>
        <p class="qx-f-text">We have collected the most useful trading indicators for you. Test them on a demo account to see which ones best suit your trading style.</p>
        <a href="{{ route('trade') }}" class="qx-f-link">Explore →</a>
      </div>

      <!-- 4. Support 24/7 -->
      <div class="qx-f-card">
        <div class="qx-f-icon yellow">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
        </div>
        <h3 class="qx-f-title">Support 24/7</h3>
        <p class="qx-f-text">Our highly trained support staff is ready to assist you at any time.</p>
        <a href="{{ route('faq') }}" class="qx-f-link">Submit a request →</a>
      </div>

      <!-- 5. Bonus programs -->
      <div class="qx-f-card">
        <div class="qx-f-icon green">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="13" rx="2"/><path d="M12 8v13M3 12h18M12 8a3 3 0 1 0-3-3 3 3 0 0 0 3 3zm0 0a3 3 0 1 1 3-3 3 3 0 0 1-3 3z"/></svg>
        </div>
        <h3 class="qx-f-title">Bonus programs</h3>
        <p class="qx-f-text">Participate in tournaments and giveaways for traders to get bonuses.</p>
        <a href="{{ route('register') }}" class="qx-f-link">Get a bonus →</a>
      </div>

      <!-- 6. Deposits and withdrawals -->
      <div class="qx-f-card">
        <div class="qx-f-icon cyan">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 1l4 4-4 4M21 5H9a4 4 0 0 0-4 4v1M7 23l-4-4 4-4M3 19h12a4 4 0 0 0 4-4v-1"/></svg>
        </div>
        <h3 class="qx-f-title">Deposits and withdrawals</h3>
        <p class="qx-f-text">Various deposit options and fast withdrawal of funds. The minimum deposit is only 10 USD.</p>
        <a href="{{ route('register') }}" class="qx-f-link">Start trading →</a>
      </div>
    </div>

    <!-- Demo Banner Strip -->
    <div class="qx-demo-banner">
      <div class="qx-demo-b-left">
        <div class="qx-grad-cap-icon">🎓</div>
        <div>
          <h4>Trade on demo – no registration is required!</h4>
          <p>Or register a personal account to access additional features.</p>
        </div>
      </div>
      <div class="qx-demo-b-right">
        <a href="{{ route('trade') }}" class="qx-btn-dark">Try demo</a>
        <a href="{{ route('register') }}" class="qx-btn-green">Register an account</a>
      </div>
    </div>
  </div>
</section>

<!-- WHITE SECTION: GROW YOUR CAPITAL -->
<section class="qx-grow-section">
  <div class="qx-section-container">
    <div class="qx-grow-card">
      <div class="qx-grow-left">
        <h2 class="qx-grow-title">
          Grow your capital by<br>
          making the right trading<br>
          predictions
        </h2>
        <p class="qx-grow-sub">
          Will the price go up or down? Predict the price movement of a trading asset and place a trade.
        </p>

        <div class="qx-grow-action">
          <a href="{{ route('register') }}" class="qx-btn-green-grow">Try it for free</a>
          <span class="qx-grow-note">‹ Practice on a demo account without registration.</span>
        </div>

        <!-- Decorative curved wave chart -->
        <div class="qx-grow-wave">
          <svg viewBox="0 0 400 90" fill="none">
            <path d="M0,80 Q 50,60 100,75 T 200,40 T 300,15 T 400,20" stroke="#3b82f6" stroke-width="2.5"/>
            <circle cx="300" cy="15" r="4" fill="#3b82f6"/>
            <circle cx="300" cy="15" r="8" stroke="#3b82f6" stroke-opacity="0.3"/>
          </svg>
        </div>
      </div>

      <div class="qx-grow-right">
        <div class="qx-steps-2x2">
          <!-- Step 1 -->
          <div class="qx-step-box">
            <div class="step-icon-circle green">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
            </div>
            <strong>1. Select an asset</strong>
          </div>

          <!-- Step 2 -->
          <div class="qx-step-box">
            <div class="step-icon-circle green">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 15l3-3 2 2 4-5"/></svg>
            </div>
            <strong>2. Monitor the chart</strong>
          </div>

          <!-- Step 3 -->
          <div class="qx-step-box">
            <div class="step-icon-circle green">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </div>
            <strong>3. Place a trade</strong>
          </div>

          <!-- Step 4 -->
          <div class="qx-step-box">
            <div class="step-icon-circle green">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
            </div>
            <strong>4. Get the result</strong>
          </div>
        </div>

        <div class="qx-money-stack-decor">
          <div class="money-bundle">💵💵💵</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- WHAT PEOPLE SAY ABOUT US (REVIEWS) -->
<section class="qx-reviews-section">
  <div class="qx-section-container">
    <div class="qx-sec-header">
      <h2 class="qx-sec-title">What people say about us</h2>
      <p class="qx-sec-sub">We asked our customers to rate Quotex on a five-point scale.</p>
    </div>

    <div class="qx-reviews-grid">
      <!-- Review 1 -->
      <div class="qx-rev-card">
        <div class="rev-header">
          <div>
            <div class="rev-name">Rahima</div>
            <div class="rev-date">29.01.2025</div>
          </div>
          <div class="rev-earned">Earned: <strong>$8400</strong></div>
        </div>
        <div class="rev-stars">★★★★★</div>
        <p class="rev-text">
          As a beginner, I really appreciated the demo account. It let me experiment with different indicators and strategies without the fear of losing money. When I finally switched to real trading, I already felt comfortable with the platform. That...
        </p>
        <a href="#" class="rev-read-more">Read More →</a>
      </div>

      <!-- Review 2 -->
      <div class="qx-rev-card">
        <div class="rev-header">
          <div>
            <div class="rev-name">Terwase</div>
            <div class="rev-date">03.12.2024</div>
          </div>
          <div class="rev-earned">Earned: <strong>$913</strong></div>
        </div>
        <div class="rev-stars">★★★★★</div>
        <p class="rev-text">
          The interface is easy and simple to understand and use. The proximity ratio for placing an up or down trade/buy or sell ratio is 1:1. The app works effectively
        </p>
        <a href="#" class="rev-read-more">Read More →</a>
      </div>

      <!-- Review 3 -->
      <div class="qx-rev-card">
        <div class="rev-header">
          <div>
            <div class="rev-name">Abhi</div>
            <div class="rev-date">28.11.2024</div>
          </div>
          <div class="rev-earned">Earned: <strong>$1041</strong></div>
        </div>
        <div class="rev-stars">★★★★★</div>
        <p class="rev-text">
          Quotex is a great trading platform. Previously, I tried some trading apps, but now that I have found Quotex, I am sticking to it. Moreover, their customer support service is also fast in replying. Quotex is an opportunity for people to...
        </p>
        <a href="#" class="rev-read-more">Read More →</a>
      </div>

      <!-- Review 4 -->
      <div class="qx-rev-card">
        <div class="rev-header">
          <div>
            <div class="rev-name">Sonal</div>
            <div class="rev-date">25.11.2024</div>
          </div>
          <div class="rev-earned">Earned: <strong>$398</strong></div>
        </div>
        <div class="rev-stars">★★★★★</div>
        <p class="rev-text">
          I've had nothing but positive experiences with Quotex over the years that I've been using it. The platform's intuitive and user-friendly design makes it easy to manage trades and monitor market movements. One thing that I truly appreciate is the...
        </p>
        <a href="#" class="rev-read-more">Read More →</a>
      </div>

      <!-- Review 5 -->
      <div class="qx-rev-card">
        <div class="rev-header">
          <div>
            <div class="rev-name">Md Imran</div>
            <div class="rev-date">24.11.2024</div>
          </div>
          <div class="rev-earned">Earned: <strong>$217</strong></div>
        </div>
        <div class="rev-stars">★★★★★</div>
        <p class="rev-text">
          In binary trading, Quotex is very good for me! The broker's deposit and withdrawal system is very good. Best regards, Quotex broker from me. 💜💜
        </p>
        <a href="#" class="rev-read-more">Read More →</a>
      </div>

      <!-- Review 6 -->
      <div class="qx-rev-card">
        <div class="rev-header">
          <div>
            <div class="rev-name">Shree Ganesh</div>
            <div class="rev-date">01.11.2024</div>
          </div>
          <div class="rev-earned">Earned: <strong>$379</strong></div>
        </div>
        <div class="rev-stars">★★★★★</div>
        <p class="rev-text">
          I've been using Quotex for some time now, and the experience has been excellent. The platform is straightforward to navigate, with a clean and intuitive interface that makes it easy to manage trades and monitor market trends....
        </p>
        <a href="#" class="rev-read-more">Read More →</a>
      </div>
    </div>

    <div class="qx-reviews-btn-row">
      <a href="#" class="qx-btn-green pill">View all reviews →</a>
    </div>
  </div>
</section>

<!-- MOBILE APP IS ALWAYS AT YOUR FINGERTIPS -->
<section class="qx-mobile-app-section">
  <div class="qx-section-container">
    <div class="qx-mobile-grid">
      
      <!-- Phone Mockup Container -->
      <div class="qx-phone-col">
        <div class="qx-phone-wrapper">
          <!-- Rating badge -->
          <div class="qx-app-rating-pill">
            <span class="rating-lbl">APP RATING</span>
            <span class="rating-score">4,8</span>
            <span class="rating-stars">★★★★★</span>
          </div>

          <!-- Smartphone Shell -->
          <div class="qx-phone-shell">
            <div class="qx-phone-screen">
              <div class="phone-top-bar">
                <span>6:47</span>
                <span>📶 🔋</span>
              </div>
              <div class="phone-acc-bar">
                <span>LIVE $1,000.00</span>
                <span class="btn-d">+</span>
              </div>
              <div class="phone-chart-area">
                <svg viewBox="0 0 200 120" class="phone-chart-svg">
                  <path d="M0,100 Q 40,80 80,90 T 140,50 T 200,60" fill="none" stroke="#2979ff" stroke-width="2"/>
                  <circle cx="140" cy="50" r="3" fill="#fff"/>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Text & Badges -->
      <div class="qx-mobile-info-col">
        <h2 class="qx-mobile-title">
          Mobile app is always at<br>
          your fingertips
        </h2>
        <p class="qx-mobile-sub">
          Download our user-friendly trading app to your mobile device and start trading.
        </p>

        <div class="qx-mobile-badges">
          <a href="{{ route('app.download') }}" class="qx-store-badge lg">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor"><path d="M3.609 1.814L13.793 12 3.61 22.186a2.44 2.44 0 0 1-.22-.968V2.782c0-.363.078-.7.22-.968zM15.207 13.414l2.457 2.457-11.83 6.76 9.373-9.217zm0-2.828L5.834 1.369l11.83 6.76-2.457 2.457zm1.414 1.414l3.197 1.827a1.44 1.44 0 0 0 0-2.494l-3.197-1.827 1.414 2.494z"/></svg>
            <div>
              <span class="badge-sub">GET IT ON</span>
              <span class="badge-main">Google Play</span>
            </div>
          </a>

          <a href="{{ route('app.download') }}" class="qx-store-badge lg">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.93-2.85-.9.04-1.99.6-2.64 1.35-.57.65-1.07 1.72-.94 2.74 1 .08 2.03-.49 2.65-1.24z"/></svg>
            <div>
              <span class="badge-sub">Progressive</span>
              <span class="badge-main">Web App</span>
            </div>
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- FREQUENTLY ASKED QUESTIONS -->
<section class="qx-faq-section">
  <div class="qx-section-container max-w-800">
    <div class="qx-sec-header">
      <h2 class="qx-sec-title">Frequently asked questions</h2>
      <p class="qx-sec-sub">See the the most common questions of new traders answered here.</p>
    </div>

    <div class="qx-faq-accordion">
      <!-- 1 -->
      <div class="qx-faq-item">
        <button class="qx-faq-q">
          <span>How do I learn how to trade?</span>
          <span class="qx-faq-icon">+</span>
        </button>
        <div class="qx-faq-a">
          <p>We provide a free demo account with $10,000 in virtual funds so you can master the interface and practice strategies without financial risk. Our platform also includes built-in technical indicators and analysis tools to help you succeed.</p>
        </div>
      </div>

      <!-- 2 -->
      <div class="qx-faq-item">
        <button class="qx-faq-q">
          <span>How long does it take to withdraw funds?</span>
          <span class="qx-faq-icon">+</span>
        </button>
        <div class="qx-faq-a">
          <p>On average, the withdrawal procedure takes from one to five business days from the date of receipt of the corresponding request of the Client and depends only on the volume of simultaneously processed requests.</p>
        </div>
      </div>

      <!-- 3 -->
      <div class="qx-faq-item">
        <button class="qx-faq-q">
          <span>What is trading platform and what it is for?</span>
          <span class="qx-faq-icon">+</span>
        </button>
        <div class="qx-faq-a">
          <p>A trading platform is a specialized software system that allows clients to conduct trades (operations) on financial markets using various financial instruments, real-time quotes, technical analysis tools, and account management.</p>
        </div>
      </div>

      <!-- 4 -->
      <div class="qx-faq-item">
        <button class="qx-faq-q">
          <span>Can I trade using a phone / mobile device?</span>
          <span class="qx-faq-icon">+</span>
        </button>
        <div class="qx-faq-a">
          <p>Yes, Quotex is fully optimized for all mobile smartphones and tablets. You can trade directly via our responsive web app or download our official Android application.</p>
        </div>
      </div>

      <!-- 5 -->
      <div class="qx-faq-item">
        <button class="qx-faq-q">
          <span>What is the minimum deposit amount?</span>
          <span class="qx-faq-icon">+</span>
        </button>
        <div class="qx-faq-a">
          <p>The advantage of the Company's trading platform is that you do not have to deposit large amounts to your account. You can start trading by investing a small amount of money. The minimum deposit is 10 US dollars.</p>
        </div>
      </div>

      <!-- 6 -->
      <div class="qx-faq-item">
        <button class="qx-faq-q">
          <span>Are there any deposit or withdrawal fees?</span>
          <span class="qx-faq-icon">+</span>
        </button>
        <div class="qx-faq-a">
          <p>No. The company does not charge any fees for either deposit or withdrawal of funds. However, payment systems may charge their own transfer fees depending on the selected currency and provider.</p>
        </div>
      </div>
    </div>

    <!-- FAQ footer callout -->
    <div class="qx-faq-help-box">
      <div class="help-text">
        Do you have other questions?<br>
        Go to all questions in the <a href="{{ route('faq') }}">FAQ section</a> or <a href="#">contact us</a>.
      </div>
      <div class="help-chair-icon">💺❓</div>
    </div>
  </div>
</section>

@endsection
