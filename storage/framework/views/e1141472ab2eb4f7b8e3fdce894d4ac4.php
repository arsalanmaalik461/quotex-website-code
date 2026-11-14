<?php $__env->startSection('title', 'User: ' . $user->name); ?>
<?php $__env->startSection('page-title', 'User Detail'); ?>

<?php $__env->startSection('content'); ?>

<?php
    $accountTabs = [
        'overall' => 'Overall',
        'demo' => 'Demo Account',
        'live' => 'Live Account',
    ];
?>

<div style="margin-bottom:16px">
    <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-ghost btn-sm">Back to Users</a>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="flex justify-between items-center" style="gap:16px;flex-wrap:wrap;">
        <div>
            <div class="card-title" style="margin-bottom:4px;">Account View</div>
            <div style="font-size:12px;color:var(--text-muted);">
                Showing <?php echo e($accountTabs[$accountView] ?? 'Overall'); ?> data for <?php echo e($user->email); ?>.
                Trade and transaction rows are scoped to this user<?php echo e($accountView !== 'overall' ? ' and account mode' : ''); ?>.
            </div>
        </div>
        <div class="flex gap-2" style="flex-wrap:wrap;">
            <?php $__currentLoopData = $accountTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $accountKey => $accountLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('admin.users.show', ['user' => $user, 'account' => $accountKey])); ?>"
                   class="btn btn-sm <?php echo e($accountView === $accountKey ? 'btn-primary' : 'btn-ghost'); ?>">
                    <?php echo e($accountLabel); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>

<div class="grid-2" style="gap:20px; margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">User Profile</div>
            <div class="flex gap-2">
                <form method="POST" action="<?php echo e(route('admin.users.login-as', $user)); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-warning btn-sm">Login As</button>
                </form>
                <?php if($user->is_blocked): ?>
                <form method="POST" action="<?php echo e(route('admin.users.unblock', $user)); ?>">
                    <?php echo csrf_field(); ?> <button class="btn btn-success btn-sm">Unblock</button>
                </form>
                <?php else: ?>
                <form method="POST" action="<?php echo e(route('admin.users.block', $user)); ?>">
                    <?php echo csrf_field(); ?> <button class="btn btn-danger btn-sm">Block</button>
                </form>
                <?php endif; ?>
                <form method="POST" action="<?php echo e(route('admin.users.destroy', $user)); ?>" onsubmit="return confirm('Delete user <?php echo e($user->name); ?>? This cannot be undone.')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="btn btn-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>

        <div style="display:grid;gap:12px;font-size:13px;">
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">ID</span>
                <span>#<?php echo e($user->id); ?></span>
            </div>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Status</span>
                <?php if($user->is_blocked): ?> <span class="badge-status red">Blocked</span>
                <?php else: ?> <span class="badge-status green">Active</span> <?php endif; ?>
            </div>
            <div class="flex justify-between items-center" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">KYC Verification</span>
                <?php $userKyc = \Illuminate\Support\Facades\Schema::hasTable('kyc_verifications') ? $user->kyc : null; ?>
                <?php if($userKyc): ?>
                    <div class="flex items-center gap-2">
                        <?php if($userKyc->status === 'approved'): ?>
                            <span class="badge-status green">Verified</span>
                        <?php elseif($userKyc->status === 'pending'): ?>
                            <span class="badge-status orange">Pending</span>
                        <?php else: ?>
                            <span class="badge-status red">Rejected</span>
                        <?php endif; ?>
                        <a href="<?php echo e(route('admin.kyc.show', $userKyc)); ?>" class="btn btn-ghost btn-sm" style="padding: 2px 8px; font-size: 11px;">Review KYC</a>
                    </div>
                <?php else: ?>
                    <span style="color:var(--text-muted);">Not Submitted</span>
                <?php endif; ?>
            </div>
            <?php if($accountView !== 'live'): ?>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Demo Balance</span>
                <span>$<?php echo e(number_format($user->demo_balance, 2)); ?></span>
            </div>
            <?php endif; ?>
            <?php if($accountView !== 'demo'): ?>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Live Balance</span>
                <strong style="color:var(--green)">$<?php echo e(number_format($user->live_balance, 2)); ?></strong>
            </div>
            <?php endif; ?>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Account Age</span>
                <span><?php echo e($tradeStats['account_age']); ?> days</span>
            </div>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Referrals</span>
                <span><?php echo e($tradeStats['referral_count']); ?></span>
            </div>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Referral Code</span>
                <span><?php echo e($user->referral_code ?? 'N/A'); ?></span>
            </div>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Joined</span>
                <span><?php echo e($user->created_at->format('M d, Y H:i')); ?></span>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-title" style="margin-bottom:12px;">
                <?php echo e(strtoupper($accountTabs[$accountView] ?? 'Overall')); ?> Overview
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label"><?php echo e($accountView === 'overall' ? 'Combined Balance' : 'Balance'); ?></div>
                    <div class="stat-value" style="font-size:18px">$<?php echo e(number_format($tradeStats['selected_balance'], 2)); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Trades</div>
                    <div class="stat-value"><?php echo e($tradeStats['total_trades']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Win / Loss</div>
                    <div class="stat-value">
                        <span style="color:var(--green)"><?php echo e($tradeStats['win_trades']); ?></span>
                        / <span style="color:var(--red)"><?php echo e($tradeStats['loss_trades']); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Completed / Active</div>
                    <div class="stat-value">
                        <?php echo e($tradeStats['completed_trades']); ?>

                        / <span style="color:var(--text-muted)"><?php echo e($tradeStats['active_trades']); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Win Rate</div>
                    <div class="stat-value" style="font-size:18px"><?php echo e(number_format($tradeStats['win_rate'], 2)); ?>%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Invested</div>
                    <div class="stat-value" style="font-size:18px">$<?php echo e(number_format($tradeStats['total_invested'], 2)); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Winning Payout</div>
                    <div class="stat-value green" style="font-size:18px">$<?php echo e(number_format($tradeStats['total_payout'], 2)); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Loss Amount</div>
                    <div class="stat-value red" style="font-size:18px">$<?php echo e(number_format($tradeStats['loss_amount'], 2)); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Net Profit</div>
                    <div class="stat-value <?php echo e($tradeStats['net_profit'] >= 0 ? 'green' : 'red'); ?>" style="font-size:18px">
                        $<?php echo e(number_format($tradeStats['net_profit'], 2)); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title" style="margin-bottom:16px">Adjust Balance</div>
            <form method="POST" action="<?php echo e(route('admin.users.add-funds', $user)); ?>">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label">Amount (negative to deduct)</label>
                    <input type="number" name="amount" step="0.01" class="form-control" placeholder="e.g. 100 or -50" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Account Type</label>
                    <select name="account_type" class="form-control">
                        <option value="live">Live Account</option>
                        <option value="demo">Demo Account</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Note (optional)</label>
                    <input type="text" name="note" class="form-control" placeholder="Reason for adjustment">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Apply Balance Change</button>
            </form>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-title" style="margin-bottom:16px">Edit User Details</div>
    <form method="POST" action="<?php echo e(route('admin.users.update', $user)); ?>">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo e($user->name); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo e($user->email); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo e($user->phone); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control" value="<?php echo e($user->country); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Account Type</label>
                <select name="account_type" class="form-control">
                    <option value="demo" <?php echo e($user->account_type === 'demo' ? 'selected' : ''); ?>>Demo</option>
                    <option value="live" <?php echo e($user->account_type === 'live' ? 'selected' : ''); ?>>Live</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">New Password (leave blank to keep)</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-title" style="margin-bottom:16px">Recent Trades</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <?php if($accountView === 'overall'): ?><th>Account</th><?php endif; ?>
                    <th>Pair</th>
                    <th>Direction</th>
                    <th>Investment</th>
                    <th>Payout</th>
                    <th>Duration</th>
                    <th>Result</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $user->trades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="color:var(--text-muted)"><?php echo e($trade->id); ?></td>
                <?php if($accountView === 'overall'): ?>
                <td>
                    <span class="badge-status <?php echo e($trade->account_type === 'live' ? 'green' : 'gray'); ?>">
                        <?php echo e(ucfirst($trade->account_type ?? 'demo')); ?>

                    </span>
                </td>
                <?php endif; ?>
                <td><?php echo e($trade->asset?->symbol ?? 'N/A'); ?></td>
                <td>
                    <?php if($trade->direction === 'up'): ?> <span class="badge-status green">UP</span>
                    <?php else: ?> <span class="badge-status red">DOWN</span> <?php endif; ?>
                </td>
                <td>$<?php echo e(number_format($trade->investment, 2)); ?></td>
                <td>$<?php echo e(number_format($trade->payout, 2)); ?></td>
                <td><?php echo e(round($trade->duration_seconds / 60, 1)); ?>m</td>
                <td>
                    <?php if($trade->result === 'win'): ?> <span class="badge-status green">WIN</span>
                    <?php elseif($trade->result === 'loss'): ?> <span class="badge-status red">LOSS</span>
                    <?php elseif($trade->result === 'pending'): ?> <span class="badge-status orange">LIVE</span>
                    <?php else: ?> <span class="badge-status gray"><?php echo e($trade->result); ?></span> <?php endif; ?>
                </td>
                <td><?php echo e($trade->created_at->format('M d, H:i')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="<?php echo e($accountView === 'overall' ? 9 : 8); ?>" style="text-align:center;color:var(--text-muted);padding:24px">No trades yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-title" style="margin-bottom:16px">Recent Transactions</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <?php if($accountView === 'overall'): ?><th>Account</th><?php endif; ?>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $user->transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="color:var(--text-muted)"><?php echo e($tx->id); ?></td>
                <?php if($accountView === 'overall'): ?>
                <td>
                    <?php if($tx->account_type === 'live'): ?>
                        <span class="badge-status green">Live</span>
                    <?php elseif($tx->account_type === 'demo'): ?>
                        <span class="badge-status gray">Demo</span>
                    <?php else: ?>
                        <span class="badge-status orange">Legacy</span>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
                <td><?php echo e(ucfirst($tx->type)); ?></td>
                <td>$<?php echo e(number_format($tx->amount, 2)); ?></td>
                <td><?php echo e($tx->method); ?></td>
                <td>
                    <?php if($tx->status === 'approved'): ?> <span class="badge-status green">Approved</span>
                    <?php elseif($tx->status === 'rejected'): ?> <span class="badge-status red">Rejected</span>
                    <?php else: ?> <span class="badge-status orange">Pending</span> <?php endif; ?>
                </td>
                <td><?php echo e($tx->created_at->format('M d, H:i')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="<?php echo e($accountView === 'overall' ? 7 : 6); ?>" style="text-align:center;color:var(--text-muted);padding:24px">No transactions</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/admin/users/show.blade.php ENDPATH**/ ?>