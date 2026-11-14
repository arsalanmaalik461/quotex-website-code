<?php $__env->startSection('title', 'Affiliate Program | Quotex'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  .ref-container {
    max-width: 1160px;
    margin: 0 auto;
    padding: 24px 20px 60px;
  }
  .ref-hero {
    background: linear-gradient(135deg, #131b2e 0%, #172138 60%, #101626 100%);
    border: 1px solid #28334e;
    border-radius: 12px;
    padding: 28px 32px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 24px;
    position: relative;
    overflow: hidden;
  }
  .ref-hero::after {
    content: '';
    position: absolute;
    right: -40px;
    top: -40px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(2, 111, 211, 0.22) 0%, transparent 70%);
    pointer-events: none;
  }
  .ref-hero-title {
    font-size: 22px;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 6px;
    letter-spacing: -0.3px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .ref-hero-subtitle {
    font-size: 13px;
    color: #94a3b8;
    max-width: 520px;
    line-height: 1.5;
  }
  .ref-link-card {
    background: #0f1422;
    border: 1px solid #232d44;
    border-radius: 10px;
    padding: 16px 20px;
    min-width: 320px;
    flex: 1;
    max-width: 480px;
  }
  .ref-link-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .ref-link-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #181f33;
    border: 1px solid #2a3754;
    border-radius: 6px;
    padding: 6px 8px 6px 12px;
  }
  .ref-link-input {
    background: transparent;
    border: none;
    color: #38bdf8;
    font-size: 13px;
    font-family: inherit;
    font-weight: 600;
    width: 100%;
    outline: none;
  }
  .btn-copy-ref {
    background: #026FD3;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    padding: 7px 14px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.15s ease;
  }
  .btn-copy-ref:hover {
    background: #0284c7;
  }

  .ref-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }
  .ref-stat-card {
    background: #151a29;
    border: 1px solid #242c40;
    border-radius: 10px;
    padding: 20px;
    transition: transform 0.15s, border-color 0.15s;
  }
  .ref-stat-card:hover {
    border-color: #3b4765;
    transform: translateY(-2px);
  }
  .ref-stat-label {
    font-size: 12px;
    font-weight: 600;
    color: #7b879d;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .ref-stat-val {
    font-size: 24px;
    font-weight: 800;
    color: #ffffff;
    font-variant-numeric: tabular-nums;
  }
  .ref-stat-val.green { color: #00e676; }
  .ref-stat-val.blue { color: #38bdf8; }
  .ref-stat-sub {
    font-size: 11px;
    color: #64748b;
    margin-top: 6px;
  }

  .ref-overview-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }
  .ref-overview-card {
    background: #121726;
    border: 1px solid #232c42;
    border-radius: 10px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .ref-overview-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .ref-overview-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #182033;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }
  .ref-overview-name {
    font-size: 13px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 2px;
  }
  .ref-overview-desc {
    font-size: 11px;
    color: #64748b;
  }
  .ref-overview-val {
    font-size: 16px;
    font-weight: 800;
    color: #00e676;
    font-variant-numeric: tabular-nums;
  }

  .ref-steps-card {
    background: #121726;
    border: 1px solid #232c42;
    border-radius: 10px;
    padding: 24px;
    margin-bottom: 28px;
  }
  .ref-steps-title {
    font-size: 14px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .ref-steps-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
  }
  .ref-step-item {
    display: flex;
    gap: 12px;
  }
  .ref-step-num {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #026FD3;
    color: #ffffff;
    font-weight: 800;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .ref-step-text-title {
    font-size: 13px;
    font-weight: 700;
    color: #e2e8f0;
    margin-bottom: 2px;
  }
  .ref-step-text-desc {
    font-size: 12px;
    color: #7b879d;
    line-height: 1.4;
  }

  .ref-table-card {
    background: #151a29;
    border: 1px solid #242c40;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 24px;
  }
  .ref-table-header {
    padding: 16px 20px;
    border-bottom: 1px solid #242c40;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
  }
  .ref-table-title {
    font-size: 14px;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .ref-table-wrap {
    overflow-x: auto;
  }
  .ref-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
  }
  .ref-table th {
    background: #111624;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 18px;
    border-bottom: 1px solid #242c40;
  }
  .ref-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #1c2438;
    color: #cbd5e1;
  }
  .ref-table tr:last-child td {
    border-bottom: none;
  }
  .ref-table tr:hover td {
    background: rgba(255, 255, 255, 0.02);
  }
  .ref-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
  }
  .ref-badge.green { background: rgba(0, 230, 118, 0.12); color: #00e676; }
  .ref-badge.blue { background: rgba(56, 189, 248, 0.12); color: #38bdf8; }
  .ref-badge.purple { background: rgba(179, 146, 240, 0.12); color: #b392f0; }

  .copy-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #00c853;
    color: #000000;
    font-weight: 800;
    font-size: 13px;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
    z-index: 10000;
    display: none;
    align-items: center;
    gap: 8px;
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="ref-container">
  
  
  <div class="ref-hero">
    <div>
      <div class="ref-hero-title">
        <span>🤝</span> Quotex Affiliate Program
      </div>
      <div class="ref-hero-subtitle">
        Invite traders to the platform with your unique partner link and earn continuous lifetime rewards on trading volume and deposit activity.
      </div>
    </div>

    <div class="ref-link-card">
      <div class="ref-link-label">
        <span>Your Partner Link</span>
        <span>Partner ID: <b style="color:#38bdf8;"><?php echo e($user->referral_code ?? $user->ensureReferralCode()); ?></b></span>
      </div>
      <div class="ref-link-box">
        <input type="text" id="refLinkInput" class="ref-link-input" readonly value="<?php echo e($user->referral_link); ?>">
        <button type="button" class="btn-copy-ref" onclick="copyReferralLink()">Copy Link</button>
      </div>
    </div>
  </div>

  
  <div class="ref-stats-grid">
    <div class="ref-stat-card">
      <div class="ref-stat-label">
        <span>💰</span> Total Earnings
      </div>
      <div class="ref-stat-val green">$<?php echo e(number_format($stats['total_earned'], 2)); ?></div>
      <div class="ref-stat-sub">Credited directly to your Live balance</div>
    </div>

    <div class="ref-stat-card">
      <div class="ref-stat-label">
        <span>👥</span> Total Referred Traders
      </div>
      <div class="ref-stat-val"><?php echo e(number_format($stats['total_referrals'])); ?></div>
      <div class="ref-stat-sub">Traders registered through your link</div>
    </div>

    <div class="ref-stat-card">
      <div class="ref-stat-label">
        <span>⚡</span> Active Traders
      </div>
      <div class="ref-stat-val blue"><?php echo e(number_format($stats['active_traders'])); ?></div>
      <div class="ref-stat-sub">Traders actively trading on Live account</div>
    </div>

    <div class="ref-stat-card">
      <div class="ref-stat-label">
        <span>🎖️</span> Partner Level
      </div>
      <div class="ref-stat-val" style="font-size:18px; color:#ffffff; padding-top:4px;">Standard Partner</div>
      <div class="ref-stat-sub">Tier 1 Affiliate Status</div>
    </div>
  </div>

  
  <div class="ref-overview-row">
    <?php
      $tradingRewards = round($stats['win_earned'] + $stats['loss_earned'], 2);
    ?>
    <div class="ref-overview-card">
      <div class="ref-overview-left">
        <div class="ref-overview-icon" style="color:#00e676;">📊</div>
        <div>
          <div class="ref-overview-name">Trading Activity Rewards</div>
          <div class="ref-overview-desc">Commissions earned from trading turnover</div>
        </div>
      </div>
      <div class="ref-overview-val">+$<?php echo e(number_format($tradingRewards, 2)); ?></div>
    </div>

    <div class="ref-overview-card">
      <div class="ref-overview-left">
        <div class="ref-overview-icon" style="color:#38bdf8;">💳</div>
        <div>
          <div class="ref-overview-name">Deposit Commissions</div>
          <div class="ref-overview-desc">Commissions from client deposits</div>
        </div>
      </div>
      <div class="ref-overview-val">+$<?php echo e(number_format($stats['deposit_earned'], 2)); ?></div>
    </div>
  </div>

  
  <div class="ref-steps-card">
    <div class="ref-steps-title">
      <span>ℹ️</span> How the Affiliate Program Works
    </div>
    <div class="ref-steps-row">
      <div class="ref-step-item">
        <div class="ref-step-num">1</div>
        <div>
          <div class="ref-step-text-title">Share Your Partner Link</div>
          <div class="ref-step-text-desc">Invite traders, friends, or your community with your unique link.</div>
        </div>
      </div>
      <div class="ref-step-item">
        <div class="ref-step-num">2</div>
        <div>
          <div class="ref-step-text-title">Traders Register & Trade</div>
          <div class="ref-step-text-desc">Your referred partners make deposits and execute live market trades.</div>
        </div>
      </div>
      <div class="ref-step-item">
        <div class="ref-step-num">3</div>
        <div>
          <div class="ref-step-text-title">Automatic Lifetime Payouts</div>
          <div class="ref-step-text-desc">Earnings are automatically calculated and credited directly to your live balance.</div>
        </div>
      </div>
    </div>
  </div>

  
  <div class="ref-table-card">
    <div class="ref-table-header">
      <div class="ref-table-title">
        <span>📜</span> Payout History
      </div>
      <span style="font-size:12px; color:#64748b;">Recent affiliate earnings</span>
    </div>
    <div class="ref-table-wrap">
      <table class="ref-table">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Activity Type</th>
            <th>Trader ID</th>
            <th>Amount Earned</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td>#<?php echo e($comm->id); ?></td>
              <td>
                <?php if($comm->type === 'trade_win' || $comm->type === 'trade_loss'): ?>
                  <span class="ref-badge green">Trading Activity Reward</span>
                <?php elseif($comm->type === 'deposit'): ?>
                  <span class="ref-badge blue">Deposit Commission</span>
                <?php else: ?>
                  <span class="ref-badge purple">Partner Signup Bonus</span>
                <?php endif; ?>
              </td>
              <td>
                <span style="color:#ffffff; font-weight:600;">Trader #<?php echo e(93090570 + $comm->referred_user_id); ?></span>
              </td>
              <td><strong style="color:#00e676; font-size:14px;">+$<?php echo e(number_format($comm->commission_amount, 2)); ?></strong></td>
              <td><span class="ref-badge green">✓ Credited</span></td>
              <td style="font-size:12px; color:#64748b;"><?php echo e($comm->created_at->format('M d, Y H:i')); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:36px 20px; color:#64748b;">
                No commission history yet. Share your partner link above to start earning!
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if($commissions->hasPages()): ?>
      <div style="padding:16px 20px;">
        <?php echo e($commissions->links()); ?>

      </div>
    <?php endif; ?>
  </div>

  
  <div class="ref-table-card">
    <div class="ref-table-header">
      <div class="ref-table-title">
        <span>👥</span> Referred Traders Network (<?php echo e($referrals->count()); ?>)
      </div>
    </div>
    <div class="ref-table-wrap">
      <table class="ref-table">
        <thead>
          <tr>
            <th>Trader ID</th>
            <th>Country</th>
            <th>Total Trades</th>
            <th>Registration Date</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td>
                <div style="font-weight:700; color:#ffffff;">Trader #<?php echo e(93090570 + $refUser->id); ?></div>
              </td>
              <td><?php echo e($refUser->country ?? '—'); ?></td>
              <td><strong><?php echo e($refUser->trades_count); ?></strong> trades</td>
              <td style="font-size:12px; color:#64748b;"><?php echo e($refUser->created_at->format('M d, Y')); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="4" style="text-align:center; padding:32px 20px; color:#64748b;">
                You haven't invited any traders yet. Share your link to start building your network!
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<div id="copyToast" class="copy-toast">
  <span>✓</span> Partner link copied to clipboard!
</div>

<script>
function copyReferralLink() {
  const input = document.getElementById('refLinkInput');
  if (input) {
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
      showToast('Partner link copied to clipboard!');
    }).catch(() => {
      document.execCommand('copy');
      showToast('Partner link copied to clipboard!');
    });
  }
}

function showToast(msg) {
  const toast = document.getElementById('copyToast');
  if (toast) {
    toast.innerHTML = '<span>✓</span> ' + msg;
    toast.style.display = 'flex';
    setTimeout(() => {
      toast.style.display = 'none';
    }, 2500);
  }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/account/referral.blade.php ENDPATH**/ ?>