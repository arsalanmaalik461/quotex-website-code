@extends('layouts.platform')

@section('title', 'Account | Quotex')

@section('content')
<main class="YUM_X" dir="ltr">
  <div class="njASH">
    <div class="balance__container">
      <div class="balance">
        <div class="balance__label">My current currency</div>
        <div class="balance__currency">
          <span class="balance__currency-icon">$</span>
          <b>USD</b>
          <button type="button" class="KtjVk rw1pS GSOMi balance__button" onclick="alert('Currency change is locked to USD for your region.')">
            <span class="oQ4Z4">Change</span>
          </button>
        </div>
      </div>
      <div class="balance">
        <div class="balance__label">Available for withdrawal</div>
        <div class="balance__value">{{ number_format($user->live_balance ?? 0, 2) }}$</div>
      </div>
      <div class="balance">
        <div class="balance__label">In the account</div>
        <div class="balance__value">{{ number_format($user->live_balance ?? 0, 2) }}$</div>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="qx-alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="qx-alert-danger">
      @foreach($errors->all() as $err)
        <p>{{ $err }}</p>
      @endforeach
    </div>
  @endif

  <div class="DckO3">
    <div class="profile-page">
      <div class="account-page">
        <!-- Personal Data Form -->
        <form class="profile-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="content-section__head">Personal data:</div>

          <div class="form__control">
            <div class="form-avatar">
              <div class="form-avatar__image-block">
                <div class="form-avatar__photo">
                  @if($user->avatar)
                    <img src="{{ asset($user->avatar) }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                  @else
                    <span class="avatar-initials">{{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}</span>
                  @endif
                  <input class="upload-control__input" type="file" name="file" title="Upload Avatar">
                </div>
              </div>
              <div class="form-avatar__block">
                <div class="form-avatar__block-email">{{ $user->email }}</div>
                <div class="form-avatar__block-text">ID: {{ $user->id ?? '93090578' }}</div>
                @php $userKyc = \Illuminate\Support\Facades\Schema::hasTable('kyc_verifications') ? $user->kyc : null; @endphp
                @if($userKyc && $userKyc->status === 'approved')
                  <div class="form-avatar__block-label" style="background:rgba(0,200,83,0.2);color:#00e676;border:1px solid #00c853;">✓ Verified</div>
                @elseif($userKyc && $userKyc->status === 'pending')
                  <div class="form-avatar__block-label" style="background:rgba(255,171,0,0.2);color:#ffab00;border:1px solid #ffab00;">⏳ Under Review</div>
                @elseif($userKyc && $userKyc->status === 'rejected')
                  <div class="form-avatar__block-label" style="background:rgba(255,82,82,0.2);color:#ff5252;border:1px solid #ff5252;">❌ Rejected</div>
                @else
                  <div class="form-avatar__block-label warning">Not verified</div>
                @endif
              </div>
            </div>
          </div>

          <div class="content-section__onboarding">
            <div class="form-control-input__control">
              <label class="input-control-cabinet input-control-cabinet--text">
                <span class="input-control-cabinet__label">Nickname</span>
                <input name="nickname" class="input-control-cabinet__input" placeholder="#{{ $user->id }}" value="{{ $user->nickname ?? '#'.$user->id }}">
              </label>
            </div>

            <div class="form-control-input__control">
              <label class="input-control-cabinet input-control-cabinet--text">
                <span class="input-control-cabinet__label">First Name</span>
                <input name="name" class="input-control-cabinet__input" placeholder="Empty" value="{{ $user->name ?? '' }}">
              </label>
            </div>

            <div class="form-control-input__control">
              <label class="input-control-cabinet input-control-cabinet--text">
                <span class="input-control-cabinet__label">Last Name</span>
                <input name="last_name" class="input-control-cabinet__input" placeholder="Empty" value="{{ $user->last_name ?? '' }}">
              </label>
            </div>

            <div class="form-control-input__control">
              <label class="input-control-cabinet input-control-cabinet--text">
                <span class="input-control-cabinet__label">Date of birth</span>
                <input name="birthday" type="date" class="input-control-cabinet__input" value="{{ $user->birthday ?? '' }}">
              </label>
            </div>

            <div class="form__control">
              <label class="input-control-cabinet input-control-cabinet--text input-control-cabinet__border disabled">
                <span class="input-control-cabinet__label disabled">Email</span>
                <input class="input-control-cabinet__input" disabled="" type="email" value="{{ $user->email }}">
                <div class="input-control-cabinet__badge active">
                  @if($user->email_verified || $user->email_verified_at)
                    <div class="input-control-cabinet__status" style="color:#00e676;">Verified</div>
                  @else
                    <div class="input-control-cabinet__status input-control-cabinet__status--danger">Unverified</div>
                    <button type="button" class="input-control-cabinet__action" id="btn-resend-verification" onclick="resendVerificationEmail(this)">Resend</button>
                  @endif
                </div>
              </label>
            </div>

            <div class="form__control">
              <label class="input-control-cabinet input-control-cabinet--select disabled">
                <span class="input-control-cabinet__label">Country</span>
                <div class="input-control-cabinet__value">{{ $user->country ?? 'Pakistan' }}</div>
              </label>
            </div>

            <div class="form-control-input__control">
              <label class="input-control-cabinet input-control-cabinet--text">
                <span class="input-control-cabinet__label">Address</span>
                <input name="address" class="input-control-cabinet__input" placeholder="Empty" value="{{ $user->address ?? '' }}">
              </label>
            </div>

            <button type="submit" class="KtjVk rw1pS GSOMi PUCjQ">
              <span class="oQ4Z4">Save</span>
            </button>
          </div>
        </form>

        <!-- Document Verification -->
        <div class="verification-documents">
          <div class="content-section__head" style="display:flex; justify-content:space-between; align-items:center;">
            <span>Documents verification (KYC):</span>
            @if($userKyc && $userKyc->status === 'approved')
              <a href="{{ route('kyc.index') }}" style="font-size:13px; color:#00e676; text-decoration:none; font-weight:600;">View Documents →</a>
            @endif
          </div>
          <div>
            @if($userKyc && $userKyc->status === 'approved')
              <div class="alert" style="background:rgba(0,200,83,0.12); border:1px solid rgba(0,200,83,0.3); color:#b9f6ca; border-radius:10px; padding:14px;">
                <strong>✓ Identity Verified:</strong> Your ID documents and live front-camera selfie have been approved by admin.
              </div>
            @elseif($userKyc && $userKyc->status === 'pending')
              <div class="alert" style="background:rgba(255,171,0,0.12); border:1px solid rgba(255,171,0,0.3); color:#ffe57f; border-radius:10px; padding:14px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <strong>⏳ Under Review:</strong> Your KYC documents and selfie are currently being reviewed by admin.
                </div>
                <a href="{{ route('kyc.index') }}" style="color:#ffab00; font-weight:600; text-decoration:underline; font-size:13px;">Check Status</a>
              </div>
            @elseif($userKyc && $userKyc->status === 'rejected')
              <div class="alert" style="background:rgba(255,82,82,0.12); border:1px solid rgba(255,82,82,0.3); color:#ff8a80; border-radius:10px; padding:14px;">
                <div style="font-weight:700; margin-bottom:4px;">❌ KYC Verification Rejected</div>
                <div style="font-size:13px; margin-bottom:8px;"><strong>Reason:</strong> {{ $userKyc->rejection_reason }}</div>
                <a href="{{ route('kyc.index') }}" style="display:inline-block; padding:6px 14px; text-decoration:none; font-size:12px; background:#ff5252; color:#fff; border-radius:6px; font-weight:700;">Resubmit KYC Documents</a>
              </div>
            @else
              <div class="alert alert-quotex-warn" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <p style="margin:0;">Submit your ID card (front & back) and live front-camera selfie to verify your account.</p>
                <a href="{{ route('kyc.index') }}" style="text-decoration:none; padding:8px 16px; background:#00c853; color:#000; font-weight:700; border-radius:6px; font-size:12px;">Start Verification</a>
              </div>
            @endif
          </div>
        </div>

        <!-- Profile Security -->
        <div class="profile-security">
          <div class="content-section__head">Security:</div>
          <div class="wmraR">
            <h3 class="eMFBy">
              <span>Two-step verification</span>
            </h3>
            <div class="L3NIo">
              <div class="profile-security__verification-text">
                <div>Receiving codes via Email</div>
              </div>
            </div>
          </div>

          <div class="form__control">
            <div class="form__control form__control--distant">
              <label class="input-control-cabinet input-control-cabinet--toggle">
                <input name="login_2fa" class="input-control-cabinet__checkbox" type="checkbox" {{ ($user->login_2fa ?? false) ? 'checked' : '' }}>
                <span class="toggle-slider-round"></span>
                <span class="input-control-cabinet__label">To enter the platform</span>
              </label>
            </div>
          </div>

          <div class="form__control">
            <div class="form__control form__control--distant">
              <label class="input-control-cabinet input-control-cabinet--toggle">
                <input name="withdrawal_2fa" class="input-control-cabinet__checkbox" type="checkbox" {{ ($user->withdrawal_2fa ?? false) ? 'checked' : '' }}>
                <span class="toggle-slider-round"></span>
                <span class="input-control-cabinet__label">To withdraw funds</span>
              </label>
            </div>
          </div>

          <div class="wmraR L0Z05">
            <h3 class="eMFBy">
              <span>Password</span>
            </h3>
            <div class="L3NIo">Change your account password</div>
          </div>

          <button id="change-trigger" type="button" class="u6xVf" aria-expanded="false" onclick="togglePasswordForm()">Change</button>

          <div id="change-region" class="Q7yMV" style="display: none;">
            <div class="fOEhu">
              <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                <div class="form-control-input__control">
                  <label class="input-control-cabinet input-control-cabinet--text">
                    <span class="input-control-cabinet__label">Old password</span>
                    <input name="old_password" type="password" class="input-control-cabinet__input" placeholder="">
                  </label>
                </div>
                <div class="form-control-input__control">
                  <label class="input-control-cabinet input-control-cabinet--text">
                    <span class="input-control-cabinet__label">New password</span>
                    <input name="password" type="password" class="input-control-cabinet__input" placeholder="">
                  </label>
                </div>
                <div class="form-control-input__control">
                  <label class="input-control-cabinet input-control-cabinet--text">
                    <span class="input-control-cabinet__label">Confirm new password</span>
                    <input name="password_confirmation" type="password" class="input-control-cabinet__input" placeholder="">
                  </label>
                </div>
                <button type="submit" class="KtjVk rw1pS GSOMi PUCjQ">
                  <span class="oQ4Z4">Change Password</span>
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Language, Timezone, Delete Account -->
        <div class="delete-account">
          <div class="form__control">
            <label class="input-control-cabinet input-control-cabinet--select">
              <span class="input-control-cabinet__label">Language</span>
              <div class="input-control-cabinet__value">
                <span>English</span>
              </div>
            </label>
          </div>

          <div class="form__control">
            <label class="input-control-cabinet input-control-cabinet--select">
              <span class="input-control-cabinet__label">Timezone</span>
              <div class="input-control-cabinet__value">
                <span>(UTC+05:00)</span>
              </div>
            </label>
          </div>

            <form action="{{ route('profile.delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.');">
              @csrf
              @method('DELETE')
              <button type="submit" class="button--delete">
                Delete My account
              </button>
            </form>
        </div>
      </div>

      <!-- Card Verification Section -->
      <div class="content-section__row" id="cards-section">
        <div class="content-section grow-full">
          <div class="content-section__head">
            Credit / debit card verification:
            <button type="button" class="KtjVk rw1pS _5qIw profile-page__cards-new" onclick="alert('Card verification will open upon first bank card deposit.')">
              <span class="oQ4Z4">Add New Card</span>
            </button>
          </div>
          <div class="profile-page__cards empty">
            <p class="profile-page__cards-placeholder">
              You don’t have any credit / debit cards for verification
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<script>
  function togglePasswordForm() {
    const region = document.getElementById('change-region');
    const trigger = document.getElementById('change-trigger');
    if (region) {
      const isHidden = region.style.display === 'none';
      region.style.display = isHidden ? 'block' : 'none';
      trigger.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    }
  }

  // 2FA toggles: persist immediately when flipped.
  document.querySelectorAll('input[name="login_2fa"], input[name="withdrawal_2fa"]').forEach(function (cb) {
    cb.addEventListener('change', function () {
      const token = document.querySelector('meta[name="csrf-token"]')?.content;
      const body = {
        login_2fa: document.querySelector('input[name="login_2fa"]')?.checked || false,
        withdrawal_2fa: document.querySelector('input[name="withdrawal_2fa"]')?.checked || false,
      };
      fetch('{{ route('profile.two-factor') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify(body),
      }).catch(() => { cb.checked = !cb.checked; });
    });
  });

  // Resend email verification: sends a real link, no fake alert.
  function resendVerificationEmail(btn) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    btn.disabled = true;
    btn.textContent = 'Sending...';
    fetch('{{ route('profile.resend-verification') }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify({}),
    })
      .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed to send.');
        btn.textContent = 'Sent ✓';
      })
      .catch((err) => {
        btn.disabled = false;
        btn.textContent = 'Try again';
        btn.title = err.message;
      });
  }
</script>
@endsection
