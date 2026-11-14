<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>Quotex web login | QxBroker login</title>
  <meta name="description" content="Log in to your Quotex account. Fast access to real-time quotes, demo trading and live binary options trading.">
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
            <div class="sign__title">Sign In To Your Account</div>

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
                  <a href="{{ route('login') }}" class="modal-sign__tab active" data-value="1">Login</a>
                  <a href="{{ route('register') }}" class="modal-sign__tab" data-value="2">Registration</a>
                </div>
              </div>

              <!-- Tab 1: Login Form (Active) -->
              <div id="tab-1" class="tabs__item active">
                @if($showTwoFactor ?? false)
                {{-- Two-step verification (email code): the password was
                     already verified; sign-in only completes with the
                     correct 6-digit code sent to the user email. --}}
                @if($twoFactorResent ?? false)
                  <div class="auth-alerts"><div>A new verification code has been sent to your email.</div></div>
                @endif
                <form action="{{ route('login') }}" method="post" dir="auto">
                  @csrf
                  <input type="hidden" name="email" value="{{ $twoFactorEmail ?? old('email') }}">
                  <div class="modal-sign__input">
                    <label class="modal-sign__input-label">Verification code</label>
                    <input type="text" name="verification_code" value="" class="modal-sign__input-value" required="1" autofocus inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit code">
                    <div class="modal-sign__input-tooltip">
                      We sent a 6-digit verification code to {{ $twoFactorEmail ?? 'your email' }}. Enter it to finish signing in. The code expires in 10 minutes.
                    </div>
                  </div>

                  <button class="modal-sign__block-button" type="submit">
                    <div>Verify and sign in</div>
                  </button>
                </form>

                <form action="{{ route('login') }}" method="post" dir="auto" style="margin-top:12px;">
                  @csrf
                  <input type="hidden" name="email" value="{{ $twoFactorEmail ?? old('email') }}">
                  <input type="hidden" name="resend_code" value="1">
                  <button class="modal-sign__block-button" type="submit" style="background:transparent; border:1px solid #2b3a4a;">
                    <div>Resend code</div>
                  </button>
                </form>
                @else
                <form action="{{ route('login') }}" method="post" dir="auto">
                  @csrf
                  <div class="modal-sign__input">
                    <label class="modal-sign__input-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="modal-sign__input-value" required="1" autofocus placeholder="example@mail.com">
                    <div class="modal-sign__input-tooltip">
                      It is not possible to change email in the future. By specifying a non-existent or incorrect email, the withdrawal of funds from the account will be impossible.
                    </div>
                  </div>

                  <div class="modal-sign__input password">
                    <label class="modal-sign__input-label">Password</label>
                    <input type="password" name="password" value="" class="modal-sign__input-value" required="1" id="password-input" placeholder="••••••••">
                  </div>

                  <div class="modal-sign__input">
                    <div class="modal-sign__block-checked">
                      <label class="modal-sign__checked-container">
                        <input type="checkbox" name="remember" value="1" checked="checked">
                        Remember me
                      </label>
                      <a href="#" class="modal-sign__block-checked-forgot">Forgot your password?</a>
                    </div>
                  </div>

                  <button class="modal-sign__block-button" type="submit">
                    <div>Sign in</div>
                  </button>
                </form>

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
                @endif
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
</body>
</html>
