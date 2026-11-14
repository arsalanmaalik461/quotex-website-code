
<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', '⚡ Dashboard'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.chart-bar-wrap { display:flex; align-items:flex-end; gap:6px; height:80px; padding-top:8px; }
.chart-bar { flex:1; border-radius:4px 4px 0 0; background:linear-gradient(to top, var(--accent), rgba(79,142,247,0.3)); min-width:20px; position:relative; transition:.3s; }
.chart-bar:hover { opacity:.8; }
.chart-labels { display:flex; gap:6px; margin-top:4px; }
.chart-label { flex:1; text-align:center; font-size:10px; color:var(--text-muted); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value"><?php echo e(number_format($stats['total_users'])); ?></div>
        <div class="stat-sub"><?php echo e($stats['active_users']); ?> active · <?php echo e($stats['blocked_users']); ?> blocked</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Trades Now</div>
        <div class="stat-value orange"><?php echo e(number_format($stats['active_trades'])); ?></div>
        <div class="stat-sub"><?php echo e(number_format($stats['total_trades_today'])); ?> trades today</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Volume Today</div>
        <div class="stat-value green">$<?php echo e(number_format($stats['total_volume_today'], 2)); ?></div>
        <div class="stat-sub">Total: $<?php echo e(number_format($stats['total_volume'], 2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending Deposits</div>
        <div class="stat-value <?php echo e($stats['pending_deposits'] > 0 ? 'orange' : ''); ?>"><?php echo e($stats['pending_deposits']); ?></div>
        <div class="stat-sub">Approved: $<?php echo e(number_format($stats['total_deposit_amount'], 2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Today's Deposits</div>
        <div class="stat-value green">$<?php echo e(number_format($stats['today_deposit_amount'], 2)); ?></div>
        <div class="stat-sub">Approved deposits today</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending Withdrawals</div>
        <div class="stat-value <?php echo e($stats['pending_withdrawals'] > 0 ? 'red' : ''); ?>"><?php echo e($stats['pending_withdrawals']); ?></div>
        <div class="stat-sub">Users balance: $<?php echo e(number_format($stats['total_users_balance'], 2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Today's Approved Withdrawals</div>
        <div class="stat-value red">$<?php echo e(number_format($stats['today_approved_withdrawal_amount'], 2)); ?></div>
        <div class="stat-sub">Approved withdrawals today</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Assets</div>
        <div class="stat-value blue"><?php echo e($stats['active_assets']); ?></div>
        <div class="stat-sub"><?php echo e($stats['total_assets']); ?> total pairs</div>
    </div>
    <div class="stat-card" style="cursor: pointer;" onclick="window.location='<?php echo e(route('admin.kyc.index', ['status' => 'pending'])); ?>'">
        <div class="stat-label">Pending KYC Verifications</div>
        <div class="stat-value <?php echo e(($stats['pending_kyc'] ?? 0) > 0 ? 'orange' : ''); ?>"><?php echo e($stats['pending_kyc'] ?? 0); ?></div>
        <div class="stat-sub">Click to review applications</div>
    </div>
</div>


<div class="grid-2" style="gap:20px; margin-bottom:20px;">

    
    <div class="card">
        <div class="card-header">
            <div class="card-title">📈 Trade Volume (Last 7 Days)</div>
        </div>
        <?php $volumes = array_column($tradeVolumeChart, 'volume'); $maxVol = count($volumes) ? max(max($volumes), 1) : 1; ?>
        <div class="chart-bar-wrap">
            <?php $__currentLoopData = $tradeVolumeChart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $h = max(4, round(($day['volume'] / $maxVol) * 80)); ?>
                <div class="chart-bar" style="height:<?php echo e($h); ?>px" title="<?php echo e($day['date']); ?>: $<?php echo e(number_format($day['volume'],2)); ?> (<?php echo e($day['count']); ?> trades)"></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="chart-labels">
            <?php $__currentLoopData = $tradeVolumeChart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="chart-label"><?php echo e(date('d', strtotime($day['date']))); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header">
            <div class="card-title">👥 New Users</div>
            <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Balance</th><th>Joined</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $recentUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><a href="<?php echo e(route('admin.users.show', $u)); ?>" style="color:var(--text-primary);text-decoration:none;"><?php echo e($u->name); ?></a></td>
                    <td><?php echo e($u->email); ?></td>
                    <td class="<?php echo e($u->live_balance > 0 ? 'green' : ''); ?>" style="color:<?php echo e($u->live_balance > 0 ? 'var(--green)' : 'inherit'); ?>">$<?php echo e(number_format($u->live_balance, 2)); ?></td>
                    <td><?php echo e($u->created_at->diffForHumans()); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" style="text-align:center;color:var(--text-muted)">No users yet</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="card mb-4">
    <div class="card-header">
        <div class="card-title">🔄 Recent Trades</div>
        <a href="<?php echo e(route('admin.live-trades.index')); ?>" class="btn btn-ghost btn-sm">Live Monitor →</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>User</th><th>Pair</th><th>Direction</th>
                    <th>Investment</th><th>Payout</th><th>Result</th><th>Time</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $recentTrades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="color:var(--text-muted)"><?php echo e($trade->id); ?></td>
                <td><?php echo e($trade->user?->name ?? 'N/A'); ?></td>
                <td><?php echo e($trade->asset?->symbol ?? 'N/A'); ?></td>
                <td>
                    <?php if($trade->direction === 'up'): ?>
                        <span class="badge-status green">▲ UP</span>
                    <?php else: ?>
                        <span class="badge-status red">▼ DOWN</span>
                    <?php endif; ?>
                </td>
                <td>$<?php echo e(number_format($trade->investment, 2)); ?></td>
                <td>$<?php echo e(number_format($trade->payout, 2)); ?></td>
                <td>
                    <?php if($trade->result === 'win'): ?> <span class="badge-status green">WIN</span>
                    <?php elseif($trade->result === 'loss'): ?> <span class="badge-status red">LOSS</span>
                    <?php elseif($trade->result === 'pending'): ?> <span class="badge-status orange">PENDING</span>
                    <?php else: ?> <span class="badge-status gray"><?php echo e($trade->result); ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo e($trade->created_at->diffForHumans()); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--text-muted)">No trades yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <div class="card-title">📝 Recent Admin Actions</div>
        <a href="<?php echo e(route('admin.logs')); ?>" class="btn btn-ghost btn-sm">Full Log</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Admin</th><th>Action</th><th>Description</th><th>Time</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($log->admin?->name ?? 'System'); ?></td>
                <td><code style="background:var(--bg-hover);padding:2px 6px;border-radius:4px;font-size:11px"><?php echo e($log->action); ?></code></td>
                <td><?php echo e(Str::limit($log->description, 60)); ?></td>
                <td><?php echo e($log->created_at->diffForHumans()); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--text-muted)">No actions logged</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>