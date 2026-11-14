<?php $__env->startSection('title', 'Affiliate Tree: ' . $user->name); ?>
<?php $__env->startSection('page-title', 'User Affiliate Details'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-size:20px; font-weight:700; color:var(--text-primary);">Affiliate Details: <?php echo e($user->name); ?></h2>
        <p style="font-size:13px; color:var(--text-secondary); margin-top:4px;">
            Email: <strong style="color:#fff;"><?php echo e($user->email); ?></strong> | Ref Code: <code style="color:var(--accent);"><?php echo e($user->referral_code); ?></code>
        </p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="<?php echo e(route('admin.users.show', $user->id)); ?>" class="btn btn-ghost">View User Profile</a>
        <a href="<?php echo e(route('admin.referrals.index')); ?>" class="btn btn-ghost">← All Affiliates</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Commission Earned</div>
        <div class="stat-value green">$<?php echo e(number_format($user->getTotalCommissionEarned(), 2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Win Commissions</div>
        <div class="stat-value green">$<?php echo e(number_format($user->getWinCommissionEarned(), 2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Loss RevShare</div>
        <div class="stat-value orange">$<?php echo e(number_format($user->getLossCommissionEarned(), 2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Deposit Commissions</div>
        <div class="stat-value" style="color:var(--accent);">$<?php echo e(number_format($user->getDepositCommissionEarned(), 2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Referral Deposits</div>
        <div class="stat-value" style="color:var(--accent);">$<?php echo e(number_format($totalReferralDeposits, 2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Invited Traders</div>
        <div class="stat-value"><?php echo e($user->referrals->count()); ?></div>
    </div>
</div>


<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <div class="card-title">👥 Invited Traders (<?php echo e($user->referrals->count()); ?>)</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Trader Name</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Account Type</th>
                    <th>Total Trades</th>
                    <th>Live Balance</th>
                    <th>Joined At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $user->referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referred): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>#<?php echo e($referred->id); ?></td>
                        <td><strong><?php echo e($referred->name); ?></strong></td>
                        <td><?php echo e($referred->email); ?></td>
                        <td><?php echo e($referred->country ?? '—'); ?></td>
                        <td>
                            <span class="badge-status <?php echo e($referred->account_type === 'live' ? 'green' : 'orange'); ?>">
                                <?php echo e(strtoupper($referred->account_type)); ?>

                            </span>
                        </td>
                        <td>
                            <?php echo e($referred->live_trades_count ?? 0); ?> live
                            <div style="font-size:11px;color:var(--text-muted);"><?php echo e($referred->trades_count ?? 0); ?> total</div>
                        </td>
                        <td>$<?php echo e(number_format($referred->live_balance ?? 0, 2)); ?></td>
                        <td><?php echo e($referred->created_at->format('M d, Y')); ?></td>
                        <td>
                            <a href="<?php echo e(route('admin.users.show', $referred->id)); ?>" class="btn btn-ghost btn-sm">Inspect</a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:24px; color:var(--text-muted);">
                            This user has not invited any traders yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <div class="card-title">📜 Commission Payouts History</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>From Trader</th>
                    <th>Type</th>
                    <th>Base Amount</th>
                    <th>Rate</th>
                    <th>Commission Earned</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>#<?php echo e($comm->id); ?></td>
                        <td><?php echo e($comm->referredUser->name ?? $comm->referredUser->email ?? 'User #' . $comm->referred_user_id); ?></td>
                        <td>
                            <?php if($comm->type === 'trade_win'): ?>
                                <span class="badge-status green">🎯 Trade Win</span>
                            <?php elseif($comm->type === 'trade_loss'): ?>
                                <span class="badge-status orange">📉 Trade Loss RevShare</span>
                            <?php elseif($comm->type === 'deposit'): ?>
                                <span class="badge-status blue">💰 Deposit</span>
                            <?php else: ?>
                                <span class="badge-status gray">🎁 Signup Bonus</span>
                            <?php endif; ?>
                        </td>
                        <td>$<?php echo e(number_format($comm->base_amount, 2)); ?></td>
                        <td><?php echo e($comm->commission_percent); ?>%</td>
                        <td><strong style="color:var(--green);">+$<?php echo e(number_format($comm->commission_amount, 2)); ?></strong></td>
                        <td><span class="badge-status green">✓ <?php echo e(ucfirst($comm->status)); ?></span></td>
                        <td style="font-size:11px; color:var(--text-muted);"><?php echo e($comm->notes); ?></td>
                        <td style="font-size:11px; color:var(--text-muted);"><?php echo e($comm->created_at->format('M d, Y H:i')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:24px; color:var(--text-muted);">
                            No commission records for this user yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/admin/referrals/user.blade.php ENDPATH**/ ?>