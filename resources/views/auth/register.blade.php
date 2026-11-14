<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>Quotex registration | QxBroker sign up</title>
  <meta name="description" content="Sign up for Quotex. Get instant access to free 10,000 USD demo account, innovative trading platform and high payouts.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/auth.css">
</head>
<body class="auth-body">
  <bdi>
    <div id="offline-overlay">
      <div>
        <div>
          <img src="/profile/images/logo-dark.svg" alt="No connection">
          <h1>No connection</h1>
          <p>
            <span id="offline-message">
              Attempting to reconnect in <span id="offline-countdown">5</span> seconds...
            </span>
          </p>
          <button id="offline-retry-btn">Retry now</button>
        </div>
      </div>
    </div>

    <div>
      <div class="header__wrapper">
        <header class="header" id="top">
          <div class="header__menu">
            <a href="{{ route('home') }}" class="header__brand">
              <img src="/profile/images/logo-dark.svg" alt="Quotex" height="26" style="height:26px;width:auto;display:block;">
            </a>

            <nav class="header__links">
              <ul class="header__list">
                <li class="header__list--item ">
                  <a href="{{ route('about') }}">About us</a>
                </li>
                <li class="header__list--item ">
                  <a href="{{ route('faq') }}">FAQ</a>
                </li>
                <li class="header__list--item">
                  <a href="{{ route('blog') }}">Blog</a>
                </li>
              </ul>
            </nav>

            <div class="header__buttons">
              <a href="{{ route('login') }}" class="header__button-log-in">Log in</a>
              <a href="{{ route('register') }}" class="header__button" id="button-sign-up">Sign up</a>
              <a class="header__language language">
                <div class="header__language-text">en</div>
              </a>
            </div>
          </div>
        </header>
      </div>

      <div class="main__platform-content">
        <div class="main sign__container">
          <div class="modal-sign__container sign__form">
            <div class="sign__title">Sign Up</div>

            @if($errors->any())
              <div class="auth-alerts">
                @foreach($errors->all() as $error)
                  <div>{{ $error }}</div>
                @endforeach
              </div>
            @endif

            <div class="modal-sign__block">
              <div class="modal-sign__tabs form" dir="auto">
                <div class="modal-sign__tabs-block">
                  <a href="{{ route('login') }}" class="modal-sign__tab" data-value="1">Login</a>
                  <a href="{{ route('register') }}" class="modal-sign__tab active" data-value="2">Registration</a>
                </div>
              </div>

              <!-- Tab 2: Registration Form (Active) -->
              <div id="tab-2" class="tabs__item active">
                <form action="{{ route('register') }}" method="post" dir="auto">
                  @csrf
                  <input type="hidden" name="time_offset" value="300">

                  <!-- Country Selector -->
                  <div id="select-country-name" class="select-input-container">
                    <div class="modal-sign__input select select-icon">
                      <label class="modal-sign__input-label">Country / Region of residence</label>
                      <select name="country-code" class="select__element" id="realCountrySelect">
                        <option value="PK" selected>Pakistan</option>
                        <option value="US">United States</option>
                        <option value="GB">United Kingdom</option>
                        <option value="IN">India</option>
                        <option value="AE">United Arab Emirates</option>
                        <option value="SA">Saudi Arabia</option>
                        <option value="BD">Bangladesh</option>
                        <option value="BR">Brazil</option>
                        <option value="ID">Indonesia</option>
                        <option value="MY">Malaysia</option>
                        <option value="NG">Nigeria</option>
                        <option value="EG">Egypt</option>
                        <option value="TR">Turkey</option>
                        <option value="TH">Thailand</option>
                        <option value="VN">Vietnam</option>
                        <option value="PH">Philippines</option>
                        <option value="ZA">South Africa</option>
                        <option value="KE">Kenya</option>
                        <option value="DE">Germany</option>
                        <option value="FR">France</option>
                      </select>

                      <div class="modal-sign__input-select form__select select__wrapper" id="countryDropdownTrigger">
                        <div class="select__result">
                          <div>
                            <div data-value="PK" id="countryCurrentDisplay">
                              <span class="select__text">Pakistan</span>
                            </div>
                          </div>
                        </div>
                        <div class="select__options" id="countryOptionsList">
                          <div style="padding: 8px 10px; border-bottom: 1px solid #232b3e;">
                            <input type="text" id="filterCountryInput" placeholder="Search country..." style="width:100%; padding:6px 10px; background:#121622; border:1px solid #2d364c; color:#fff; border-radius:4px; font-size:12px;">
                          </div>
                          <div data-value="PK"><span class="select__text">Pakistan</span></div>
                          <div data-value="US"><span class="select__text">United States</span></div>
                          <div data-value="GB"><span class="select__text">United Kingdom</span></div>
                          <div data-value="IN"><span class="select__text">India</span></div>
                          <div data-value="AE"><span class="select__text">United Arab Emirates</span></div>
                          <div data-value="SA"><span class="select__text">Saudi Arabia</span></div>
                          <div data-value="BD"><span class="select__text">Bangladesh</span></div>
                          <div data-value="BR"><span class="select__text">Brazil</span></div>
                          <div data-value="ID"><span class="select__text">Indonesia</span></div>
                          <div data-value="MY"><span class="select__text">Malaysia</span></div>
                          <div data-value="NG"><span class="select__text">Nigeria</span></div>
                          <div data-value="EG"><span class="select__text">Egypt</span></div>
                          <div data-value="TR"><span class="select__text">Turkey</span></div>
                          <div data-value="TH"><span class="select__text">Thailand</span></div>
                          <div data-value="VN"><span class="select__text">Vietnam</span></div>
                          <div data-value="PH"><span class="select__text">Philippines</span></div>
                          <div data-value="ZA"><span class="select__text">South Africa</span></div>
                          <div data-value="KE"><span class="select__text">Kenya</span></div>
                          <div data-value="DE"><span class="select__text">Germany</span></div>
                          <div data-value="FR"><span class="select__text">France</span></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Currency Selector -->
                  <div class="modal-sign__input select">
                    <label class="modal-sign__input-label">Currency</label>
                    <select name="currency" class="select__element" id="realCurrencySelect">
                      <option value="GBP">GBP</option>
                      <option value="USD" selected="">USD</option>
                      <option value="BRL">BRL</option>
                      <option value="IDR">IDR</option>
                      <option value="MYR">MYR</option>
                      <option value="INR">INR</option>
                      <option value="KZT">KZT</option>
                      <option value="THB">THB</option>
                      <option value="UAH">UAH</option>
                      <option value="VND">VND</option>
                      <option value="NGN">NGN</option>
                      <option value="EGP">EGP</option>
                      <option value="MXN">MXN</option>
                      <option value="JPY">JPY</option>
                      <option value="BDT">BDT</option>
                      <option value="PKR">PKR</option>
                      <option value="PHP">PHP</option>
                      <option value="TRY">TRY</option>
                      <option value="KRW">KRW</option>
                    </select>

                    <div class="modal-sign__input-select form__select select__wrapper" id="currencyDropdownTrigger">
                      <div class="select__result">
                        <div>
                          <div data-value="USD" id="currencyCurrentDisplay">
                            <span class="select__text">USD</span>
                          </div>
                        </div>
                      </div>
                      <div class="select__options" id="currencyOptionsList">
                        <div data-value="USD"><span class="select__text">USD</span></div>
                        <div data-value="PKR"><span class="select__text">PKR</span></div>
                        <div data-value="GBP"><span class="select__text">GBP</span></div>
                        <div data-value="EUR"><span class="select__text">EUR</span></div>
                        <div data-value="BRL"><span class="select__text">BRL</span></div>
                        <div data-value="IDR"><span class="select__text">IDR</span></div>
                        <div data-value="MYR"><span class="select__text">MYR</span></div>
                        <div data-value="INR"><span class="select__text">INR</span></div>
                        <div data-value="KZT"><span class="select__text">KZT</span></div>
                        <div data-value="THB"><span class="select__text">THB</span></div>
                        <div data-value="UAH"><span class="select__text">UAH</span></div>
                        <div data-value="VND"><span class="select__text">VND</span></div>
                        <div data-value="NGN"><span class="select__text">NGN</span></div>
                        <div data-value="EGP"><span class="select__text">EGP</span></div>
                        <div data-value="MXN"><span class="select__text">MXN</span></div>
                        <div data-value="JPY"><span class="select__text">JPY</span></div>
                        <div data-value="BDT"><span class="select__text">BDT</span></div>
                        <div data-value="PHP"><span class="select__text">PHP</span></div>
                        <div data-value="TRY"><span class="select__text">TRY</span></div>
                        <div data-value="KRW"><span class="select__text">KRW</span></div>
                      </div>
                    </div>
                  </div>

                  <!-- Email Input -->
                  <div class="modal-sign__input">
                    <label class="modal-sign__input-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="modal-sign__input-value focus-tooltip" required="1" id="emailInput" placeholder="example@mail.com">
                    <div class="modal-sign__input-tooltip">
                      It is not possible to change email in the future. By specifying a non-existent or incorrect email, the withdrawal of funds from the account will be impossible.
                    </div>
                  </div>

                  <!-- Password Input -->
                  <div class="modal-sign__input">
                    <label class="modal-sign__input-label">Password</label>
                    <input type="password" name="password" value="" class="modal-sign__input-value" required="1" id="password-input-registration" placeholder="••••••••">
                  </div>

                  <!-- Referral Code / Promo Code Root -->
                  @php
                    $initialRef = $refCode ?? request('ref') ?? request('r') ?? session('referral_code') ?? old('referral_code');
                  @endphp
                  <div class="input input--with-action promocode" style="margin-bottom: 16px;">
                    <label for="referralInput" class="input__label" style="display:flex; justify-content:space-between;">
                      <span>Referral Code (optional)</span>
                      @if($initialRef)
                        <span style="color:#00e676; font-size:11px; font-weight:700;">✓ Partner Code Active</span>
                      @endif
                    </label>
                    <div class="input__control">
                      <input type="text" name="referral_code" value="{{ $initialRef }}" class="input__value focus-tooltip" id="referralInput" placeholder="e.g. QX89A2K9" style="text-transform:uppercase;">
                    </div>
                  </div>

                  <!-- Promo Code Root -->
                  <div class="input input--with-action promocode" data-promocode-root="">
                    <label for="promocodeInput" class="input__label">Promo code (optional)</label>
                    <div class="input__control">
                      <input type="text" name="promocode" value="" class="input__value focus-tooltip" id="promocodeInput" placeholder="Promo code">
                      <button type="button" class="input__action" id="promoApplyBtn">Apply</button>
                    </div>
                    <p id="promocodeError" class="input__error" style="display:none;">
                      <span>Promo code not found. You can continue without it.</span>
                    </p>
                  </div>

                  <!-- Checkbox 1: 18+ and Service Agreement -->
                  <div class="modal-sign__input">
                    <div class="modal-sign__block-checked">
                      <label class="modal-sign__checked-container">
                        <input type="checkbox" name="rules" value="1" required checked>
                        <div>
                          I confirm that I am 18 years old or older and accept
                          <a href="{{ route('faq') }}#service" class="modal-sign__block-checked-forgot">
                            Service Agreement
                          </a>
                        </div>
                      </label>
                    </div>
                  </div>

                  <!-- Checkbox 2: Non-US citizen declaration -->
                  <div class="modal-sign__input">
                    <div class="modal-sign__block-checked">
                      <label class="modal-sign__checked-container">
                        <input type="checkbox" name="not-us-citizen" value="1" required checked>
                        <div>
                          I declare and confirm that I am not a citizen or resident of the US for tax purposes
                        </div>
                      </label>
                    </div>
                  </div>

                  <!-- Submit Button -->
                  <button class="modal-sign__block-button" type="submit">
                    <div>Registration</div>
                  </button>
                </form>

                <!-- Social Footer -->
                <div class="modal-sign__footer">
                  <div class="modal-sign__footer-header">
                    <div class="modal-sign__footer-header-text">Sign in via</div>
                  </div>
                  <div class="modal-sign__socials">
                    <span class="modal-sign__social-btn" aria-disabled="true" style="opacity:.55; cursor:not-allowed;">
                      <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.3 9 5 12 5z"/><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/><path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3 0-.8.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15s.7 5.3 1.9 7.7l3.7-2.9z"/><path fill="#34A853" d="M12 24c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.3-6.4-5.2L1.9 17C3.7 20.7 7.5 24 12 24z"/></svg>
                      Google
                    </span>
                    <span class="modal-sign__social-btn" aria-disabled="true" style="opacity:.55; cursor:not-allowed;">
                      <svg width="18" height="18" fill="#1877F2" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                      Facebook
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <footer class="footer" dir="auto">
          <div class="footer__block">
            <a class="footer__logo" href="{{ route('home') }}">
              <img src="/profile/images/logo-dark.svg" alt="Quotex" height="26" style="height:26px;width:auto;display:block;">
            </a>
            <div class="footer__links">
              <nav>
                <ul>
                  <li><a class="footer__links-section " href="{{ route('faq') }}">FAQ</a></li>
                  <li><a class="footer__links-item" href="{{ route('faq') }}">General questions</a></li>
                  <li><a class="footer__links-item" href="{{ route('faq') }}#financial">Financial questions</a></li>
                  <li><a class="footer__links-item" href="{{ route('faq') }}#verification">Verification</a></li>
                </ul>
              </nav>
              <nav>
                <ul>
                  <li><a class="footer__links-section " href="{{ route('about') }}">About us</a></li>
                  <li><a class="footer__links-item" href="{{ route('about') }}#reviews">Reviews</a></li>
                  <li><a class="footer__links-item" href="{{ route('about') }}#contacts">Contacts</a></li>
                </ul>
              </nav>
              <nav>
                <ul>
                  <li><div class="footer__links-section__title">More</div></li>
                  <li><a class="footer__links-item" href="{{ route('register') }}">Affiliate program</a></li>
                </ul>
              </nav>
            </div>
            <div>
              <div class="footer__social-title">Download the app</div>
              <a class="footer__playmarket" href="{{ route('app.download') }}">
                <img src="/profile/images/logo-dark.svg" alt="app">
              </a>
              <a class="footer__pwamarket" href="{{ route('app.download') }}">
                <img src="/profile/images/logo-dark.svg" alt="pwa">
              </a>
              <div class="footer__social-title">Follow us on social media</div>
              <a class="footer__social-item" href="{{ route('home') }}"><div>32K+</div></a>
              <a class="footer__social-item" href="{{ route('home') }}"><div>110K+</div></a>
              <a class="footer__social-item" href="{{ route('home') }}"><div>390K+</div></a>
            </div>
          </div>
          <div class="footer__container">
            <nav>
              <ul>
                <li><p class="footer__section-title">Regulations</p></li>
                <li><a class="footer__links-item" href="{{ route('faq') }}#privacy">Privacy policy</a></li>
                <li><a class="footer__links-item" href="{{ route('faq') }}#service">Service agreement</a></li>
                <li><a class="footer__links-item" href="{{ route('faq') }}#risk">Risk disclosure</a></li>
                <li><a class="footer__links-item" href="{{ route('faq') }}#trading-rules">Rules of trading operations</a></li>
                <li><a class="footer__links-item" href="{{ route('faq') }}#financial">Non-trading operations regulations</a></li>
                <li><a class="footer__links-item" href="{{ route('faq') }}#payment">Payment policy</a></li>
              </ul>
            </nav>
            <div class="footer__text">
              <p>ON SPOT GROUP LLC. Address: Main Street, P.O. Box 625, Charlestown, St. Kitts and Nevis.</p>
              <p>The website services are not available in a number of countries, including USA, Canada, Hong Kong, EEA countries, Israel, Russia as well as for persons under 18 years of age.</p>
              <p>Risk Warning: Trading Forex and Leveraged Financial Instruments involves significant risk and can result in the loss of your invested capital. You should not invest more than you can afford to lose and should ensure that you fully understand the risks involved. Trading leveraged products may not be suitable for all investors. Trading non-leveraged products such as stocks also involves risk as the value of a stock can fall as well as rise, which could mean getting back less than you originally put in. Past performance is no guarantee of future results. Before trading, please take into consideration your level of experience, investment objectives and seek independent financial advice if necessary. It is the responsibility of the Client to ascertain whether he/she is permitted to use the services of the Quotex brand based on the legal requirements in his/her country of residence.</p>
              <br>
              <p>{{ config('app.name', 'The platform') }} is the owner of this website.</p>
              <p>Copyright © {{ date('Y') }} Quotex. All rights reserved</p>
            </div>
          </div>
        </footer>

        <div class="modal js-account-closed-modal">
          <div class="modal-account-closed modal__dialog">
            <div>
              <div class="modal-account-closed__header">
                <div>Account closed - why and what to do?</div>
              </div>
              <div class="modal-account-closed__body">
                <span>There are several reasons why an account may become closed...</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </bdi>

  <script>
    // Dropdown toggles & search
    document.addEventListener('DOMContentLoaded', function() {
      // Country dropdown
      const countryTrigger = document.getElementById('countryDropdownTrigger');
      const countryOptions = document.getElementById('countryOptionsList');
      const countryDisplay = document.getElementById('countryCurrentDisplay');
      const realCountry = document.getElementById('realCountrySelect');
      const filterCountry = document.getElementById('filterCountryInput');

      if (countryTrigger && countryOptions) {
        countryTrigger.addEventListener('click', function(e) {
          if (e.target !== filterCountry) {
            countryOptions.classList.toggle('open');
            if (currencyOptions) currencyOptions.classList.remove('open');
          }
        });

        countryOptions.querySelectorAll('[data-value]').forEach(el => {
          el.addEventListener('click', function(e) {
            e.stopPropagation();
            const val = this.getAttribute('data-value');
            const text = this.querySelector('.select__text').innerText;
            countryDisplay.querySelector('.select__text').innerText = text;
            countryDisplay.setAttribute('data-value', val);
            if (realCountry) realCountry.value = val;
            countryOptions.classList.remove('open');
          });
        });

        if (filterCountry) {
          filterCountry.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            countryOptions.querySelectorAll('[data-value]').forEach(el => {
              const txt = el.querySelector('.select__text').innerText.toLowerCase();
              el.style.display = txt.includes(q) ? 'block' : 'none';
            });
          });
        }
      }

      // Currency dropdown
      const currencyTrigger = document.getElementById('currencyDropdownTrigger');
      const currencyOptions = document.getElementById('currencyOptionsList');
      const currencyDisplay = document.getElementById('currencyCurrentDisplay');
      const realCurrency = document.getElementById('realCurrencySelect');

      if (currencyTrigger && currencyOptions) {
        currencyTrigger.addEventListener('click', function(e) {
          currencyOptions.classList.toggle('open');
          if (countryOptions) countryOptions.classList.remove('open');
        });

        currencyOptions.querySelectorAll('[data-value]').forEach(el => {
          el.addEventListener('click', function(e) {
            e.stopPropagation();
            const val = this.getAttribute('data-value');
            currencyDisplay.querySelector('.select__text').innerText = val;
            currencyDisplay.setAttribute('data-value', val);
            if (realCurrency) realCurrency.value = val;
            currencyOptions.classList.remove('open');
          });
        });
      }

      // Close dropdowns when clicking outside
      document.addEventListener('click', function(e) {
        if (countryTrigger && !countryTrigger.contains(e.target) && countryOptions) {
          countryOptions.classList.remove('open');
        }
        if (currencyTrigger && !currencyTrigger.contains(e.target) && currencyOptions) {
          currencyOptions.classList.remove('open');
        }
      });

      // Promo apply button
      const promoBtn = document.getElementById('promoApplyBtn');
      const promoInput = document.getElementById('promocodeInput');
      const promoError = document.getElementById('promocodeError');
      if (promoBtn && promoInput && promoError) {
        promoBtn.addEventListener('click', function() {
          if (promoInput.value.trim() !== '') {
            promoError.style.display = 'block';
          }
        });
      }
    });
  </script>
</body>
</html>
