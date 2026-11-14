<?php $__env->startSection('title', 'Account | Quotex'); ?>

<?php $__env->startSection('content'); ?>
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
        <div class="balance__value"><?php echo e(number_format($user->live_balance ?? 0, 2)); ?>$</div>
      </div>
      <div class="balance">
        <div class="balance__label">In the account</div>
        <div class="balance__value"><?php echo e(number_format($user->live_balance ?? 0, 2)); ?>$</div>
      </div>
    </div>
  </div>

  <?php if(session('success')): ?>
    <div class="qx-alert-success"><?php echo e(session('success')); ?></div>
  <?php endif; ?>
  <?php if($errors->any()): ?>
    <div class="qx-alert-danger">
      <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <p><?php echo e($err); ?></p>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  <?php endif; ?>

  <div class="DckO3">
    <div class="profile-page">
      <div class="account-page">
        <!-- Personal Data Form -->
        <form class="profile-form" action="<?php echo e(route('profile.update')); ?>" method="POST">
          <?php echo csrf_field(); ?>
          <div class="content-section__head">Personal data:</div>

          <div class="form__control">
            <div class="form-avatar">
              <div class="form-avatar__image-block">
                <div class="form-avatar__photo">
                  <span class="avatar-initials"><?php echo e(strtoupper(substr($user->name ?? 'A', 0, 1))); ?></span>
                  <input class="upload-control__input" type="file" name="file" title="Upload Avatar">
                </div>
              </div>
              <div class="form-avatar__block">
                <div class="form-avatar__block-email"><?php echo e($user->email); ?></div>
                <div class="form-avatar__block-text">ID: <?php echo e($user->id ?? '93090578'); ?></div>
                <?php $userKyc = \Illuminate\Support\Facades\Schema::hasTable('kyc_verifications') ? $user->kyc : null; ?>
                <?php if($userKyc && $userKyc->status === 'approved'): ?>
                  <div class="form-avatar__block-label" style="background:rgba(0,200,83,0.2);color:#00e676;border:1px solid #00c853;">✓ Verified</div>
                <?php elseif($userKyc && $userKyc->status === 'pending'): ?>
                  <div class="form-avatar__block-label" style="background:rgba(255,171,0,0.2);color:#ffab00;border:1px solid #ffab00;">⏳ Under Review</div>
                <?php elseif($userKyc && $userKyc->status === 'rejected'): ?>
                  <div class="form-avatar__block-label" style="background:rgba(255,82,82,0.2);color:#ff5252;border:1px solid #ff5252;">❌ Rejected</div>
                <?php else: ?>
                  <div class="form-avatar__block-label warning">Not verified</div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="content-section__onboarding">
            <div class="form-control-input__control">
              <label class="input-control-cabinet input-control-cabinet--text">
                <span class="input-control-cabinet__label">Nickname</span>
                <input name="nickname" class="input-control-cabinet__input" placeholder="#93090578" value="#93090578">
              </label>
            </div>

            <div class="form-control-input__control">
              <label class="input-control-cabinet input-control-cabinet--text">
                <span class="input-control-cabinet__label">First Name</span>
                <input name="name" class="input-control-cabinet__input" placeholder="Empty" value="<?php echo e($user->name ?? ''); ?>">
              </label>
            </div>

            <div class="form-control-input__control">
              <label class="input-control-cabinet input-control-cabinet--text">
                <span class="input-control-cabinet__label">Last Name</span>
                <input name="last_name" class="input-control-cabinet__input" placeholder="Empty" value="">
              </label>
            </div>

            <div class="form-control-input__control">
              <label class="input-control-cabinet input-control-cabinet--text">
                <span class="input-control-cabinet__label">Date of birth</span>
                <input name="birthday" type="date" class="input-control-cabinet__input" value="">
              </label>
            </div>

            <div class="form__control">
              <label class="input-control-cabinet input-control-cabinet--text input-control-cabinet__border disabled">
                <span class="input-control-cabinet__label disabled">Email</span>
                <input class="input-control-cabinet__input" disabled="" type="email" value="<?php echo e($user->email); ?>">
                <div class="input-control-cabinet__badge active">
                  <div class="input-control-cabinet__status input-control-cabinet__status--danger">Unverified</div>
                  <button type="button" class="input-control-cabinet__action" onclick="alert('Verification link sent to your email!')">Resend</button>
                </div>
              </label>
            </div>

            <div class="form__control">
              <label class="input-control-cabinet input-control-cabinet--select disabled">
                <span class="input-control-cabinet__label">Country</span>
                <div class="input-control-cabinet__value"><?php echo e($user->country ?? 'Pakistan'); ?></div>
              </label>
            </div>

            <div class="form-control-input__control">
              <label class="input-control-cabinet input-control-cabinet--text">
                <span class="input-control-cabinet__label">Address</span>
                <input name="address" class="input-control-cabinet__input" placeholder="Empty" value="">
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
            <a href="<?php echo e(route('kyc.index')); ?>" style="font-size:13px; color:#00e676; text-decoration:none; font-weight:600;">
              <?php echo e(($userKyc && $userKyc->status === 'approved') ? 'View Documents →' : 'Complete Verification →'); ?>

            </a>
          </div>
          <div>
            <?php if($userKyc && $userKyc->status === 'approved'): ?>
              <div class="alert" style="background:rgba(0,200,83,0.12); border:1px solid rgba(0,200,83,0.3); color:#b9f6ca; border-radius:10px; padding:14px;">
                <strong>✓ Identity Verified:</strong> Your ID documents and live front-camera selfie have been approved by admin.
              </div>
            <?php elseif($userKyc && $userKyc->status === 'pending'): ?>
              <div class="alert" style="background:rgba(255,171,0,0.12); border:1px solid rgba(255,171,0,0.3); color:#ffe57f; border-radius:10px; padding:14px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <strong>⏳ Under Review:</strong> Your KYC documents and selfie are currently being reviewed by admin.
                </div>
                <a href="<?php echo e(route('kyc.index')); ?>" style="color:#ffab00; font-weight:600; text-decoration:underline; font-size:13px;">Check Status</a>
              </div>
            <?php elseif($userKyc && $userKyc->status === 'rejected'): ?>
              <div class="alert" style="background:rgba(255,82,82,0.12); border:1px solid rgba(255,82,82,0.3); color:#ff8a80; border-radius:10px; padding:14px;">
                <div style="font-weight:700; margin-bottom:4px;">❌ KYC Verification Rejected</div>
                <div style="font-size:13px; margin-bottom:8px;"><strong>Reason:</strong> <?php echo e($userKyc->rejection_reason); ?></div>
                <a href="<?php echo e(route('kyc.index')); ?>" style="display:inline-block; padding:6px 14px; text-decoration:none; font-size:12px; background:#ff5252; color:#fff; border-radius:6px; font-weight:700;">Resubmit KYC Documents</a>
              </div>
            <?php else: ?>
              <div class="alert alert-quotex-warn" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <p style="margin:0;">Submit your ID card (front & back) and live front-camera selfie to verify your account.</p>
                <a href="<?php echo e(route('kyc.index')); ?>" style="text-decoration:none; padding:8px 16px; background:#00c853; color:#000; font-weight:700; border-radius:6px; font-size:12px;">Start Verification</a>
              </div>
            <?php endif; ?>
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
                <input name="login_2fa" class="input-control-cabinet__checkbox" type="checkbox" checked>
                <span class="toggle-slider-round"></span>
                <span class="input-control-cabinet__label">To enter the platform</span>
              </label>
            </div>
          </div>

          <div class="form__control">
            <div class="form__control form__control--distant">
              <label class="input-control-cabinet input-control-cabinet--toggle">
                <input name="withdrawal_2fa" class="input-control-cabinet__checkbox" type="checkbox" checked>
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
              <form action="<?php echo e(route('profile.update')); ?>" method="POST">
                <?php echo csrf_field(); ?>
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

          <button type="button" class="button--delete" onclick="confirm('Are you sure you want to delete your account?')">
            Delete My account
          </button>
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
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/account/profile.blade.php ENDPATH**/ ?>