<?php $__env->startSection('title', 'Referral Commission Settings'); ?>
<?php $__env->startSection('page-title', 'Affiliate & Referral Commission Rates'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width: 900px; margin: 0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="font-size:20px; font-weight:700; color:var(--text-primary);">⚙️ Referral & Commission Settings</h2>
            <p style="font-size:13px; color:var(--text-secondary); margin-top:4px;">
                Configure the commission percentages for user invitations, trade wins, trade losses, and deposits.
            </p>
        </div>
        <a href="<?php echo e(route('admin.referrals.index')); ?>" class="btn btn-ghost" style="display:inline-flex; align-items:center; gap:6px;">
            ← Back to Referral Overview
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success" style="margin-bottom:20px;">
            ✓ <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-error" style="margin-bottom:20px;">
            <ul style="margin-left: 20px;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin.referrals.settings.update')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        
        <div class="card" style="margin-bottom: 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-size:15px; font-weight:700; color:var(--text-primary);">Enable Referral Program</div>
                    <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">
                        Master switch to activate or pause the affiliate referral reward engine globally.
                    </div>
                </div>
                <div>
                    <label style="position:relative; display:inline-block; width:52px; height:28px; cursor:pointer;">
                        <input type="checkbox" name="is_enabled" value="1" <?php echo e($settings->is_enabled ? 'checked' : ''); ?> style="opacity:0; width:0; height:0;" id="toggleEnabled">
                        <span class="slider-toggle" style="position:absolute; inset:0; background:<?php echo e($settings->is_enabled ? 'var(--green)' : 'var(--bg-hover)'); ?>; border:1px solid var(--border); border-radius:28px; transition:.3s;"></span>
                    </label>
                </div>
            </div>
        </div>

        
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <div class="card-title">💰 Core Commission Percentages & Rewards</div>
            </div>

            <div class="grid-2">
                
                <div class="form-group" style="background:rgba(0,209,122,0.04); border:1px solid rgba(0,209,122,0.2); border-radius:10px; padding:16px;">
                    <label class="form-label" style="color:var(--green); font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px;">
                        <span>🎯</span> Trade WIN Commission Rate (%)
                    </label>
                    <div style="position:relative; margin-top:8px;">
                        <input type="number" step="0.01" min="0" max="100" name="trade_win_commission_percent" class="form-control" style="font-size:16px; font-weight:700; padding-right:40px;" value="<?php echo e(old('trade_win_commission_percent', $settings->trade_win_commission_percent)); ?>" required>
                        <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%); font-weight:800; color:var(--green); font-size:16px;">%</span>
                    </div>
                    <small style="display:block; color:var(--text-muted); font-size:11px; margin-top:8px; line-height:1.4;">
                        When a referred trader <strong>WINS</strong> a live trade, the referrer receives this percentage of the trader's <strong>net profit</strong>.
                        <br><em>Example: Trader wins $100 profit @ 5% = Referrer earns $5.00.</em>
                    </small>
                </div>

                
                <div class="form-group" style="background:rgba(245,158,11,0.04); border:1px solid rgba(245,158,11,0.2); border-radius:10px; padding:16px;">
                    <label class="form-label" style="color:var(--orange); font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px;">
                        <span>📉</span> Trade LOSS Commission Rate (%) (RevShare)
                    </label>
                    <div style="position:relative; margin-top:8px;">
                        <input type="number" step="0.01" min="0" max="100" name="trade_loss_commission_percent" class="form-control" style="font-size:16px; font-weight:700; padding-right:40px;" value="<?php echo e(old('trade_loss_commission_percent', $settings->trade_loss_commission_percent)); ?>" required>
                        <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%); font-weight:800; color:var(--orange); font-size:16px;">%</span>
                    </div>
                    <small style="display:block; color:var(--text-muted); font-size:11px; margin-top:8px; line-height:1.4;">
                        When a referred trader <strong>LOSES</strong> a live trade, the referrer receives this percentage of the <strong>lost investment amount</strong> (Broker Revenue Share).
                        <br><em>Example: Trader loses $50 @ 10% = Referrer earns $5.00.</em>
                    </small>
                </div>

                
                <div class="form-group" style="background:rgba(79,142,247,0.04); border:1px solid rgba(79,142,247,0.2); border-radius:10px; padding:16px;">
                    <label class="form-label" style="color:var(--accent); font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px;">
                        <span>💳</span> Deposit Commission Rate (%)
                    </label>
                    <div style="position:relative; margin-top:8px;">
                        <input type="number" step="0.01" min="0" max="100" name="deposit_commission_percent" class="form-control" style="font-size:16px; font-weight:700; padding-right:40px;" value="<?php echo e(old('deposit_commission_percent', $settings->deposit_commission_percent)); ?>" required>
                        <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%); font-weight:800; color:var(--accent); font-size:16px;">%</span>
                    </div>
                    <small style="display:block; color:var(--text-muted); font-size:11px; margin-top:8px; line-height:1.4;">
                        When a referred trader deposits real funds and deposit is approved by admin, the referrer earns this percentage.
                        <br><em>Example: Trader deposits $200 @ 5% = Referrer earns $10.00.</em>
                    </small>
                </div>

                
                <div class="form-group" style="background:rgba(123,94,167,0.04); border:1px solid rgba(123,94,167,0.2); border-radius:10px; padding:16px;">
                    <label class="form-label" style="color:#b392f0; font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px;">
                        <span>🎁</span> Signup / Registration Bonus ($)
                    </label>
                    <div style="position:relative; margin-top:8px;">
                        <input type="number" step="0.01" min="0" name="join_commission_amount" class="form-control" style="font-size:16px; font-weight:700; padding-left:30px;" value="<?php echo e(old('join_commission_amount', $settings->join_commission_amount)); ?>" required>
                        <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); font-weight:800; color:#b392f0; font-size:16px;">$</span>
                    </div>
                    <small style="display:block; color:var(--text-muted); font-size:11px; margin-top:8px; line-height:1.4;">
                        Fixed dollar bonus rewarded to the referrer immediately when an invited user creates a new account. (Set to 0 to disable).
                    </small>
                </div>
            </div>
        </div>

        
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title">🛡️ Execution Rules & Payout Options</div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Minimum Live Trade Amount ($)</label>
                    <input type="number" step="0.1" min="0.1" name="min_trade_amount" class="form-control" value="<?php echo e(old('min_trade_amount', $settings->min_trade_amount)); ?>" required>
                    <small class="text-muted">Trades with investment lower than this threshold will not generate commission.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Auto-Credit to Referrer Live Balance</label>
                    <select name="auto_credit_to_balance" class="form-control">
                        <option value="1" <?php echo e($settings->auto_credit_to_balance ? 'selected' : ''); ?>>Yes — Instantly Add to Live Balance</option>
                        <option value="0" <?php echo e(!$settings->auto_credit_to_balance ? 'selected' : ''); ?>>No — Hold as Pending for Manual Review</option>
                    </select>
                    <small class="text-muted">Automatically adds earned affiliate commissions to referrer's live trading account.</small>
                </div>
            </div>

            <div class="form-group" style="margin-top:12px;">
                <label class="form-label">Affiliate Terms & Disclaimer</label>
                <textarea name="terms_and_conditions" class="form-control" rows="3" placeholder="Terms and conditions for affiliate participants..."><?php echo e(old('terms_and_conditions', $settings->terms_and_conditions)); ?></textarea>
            </div>
        </div>

        
        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <a href="<?php echo e(route('admin.referrals.index')); ?>" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 28px; font-size:14px;">
                💾 Save Commission Settings
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chk = document.getElementById('toggleEnabled');
    const slider = document.querySelector('.slider-toggle');
    if (chk && slider) {
        chk.addEventListener('change', function() {
            slider.style.background = this.checked ? 'var(--green)' : 'var(--bg-hover)';
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/referrals/settings.blade.php ENDPATH**/ ?>