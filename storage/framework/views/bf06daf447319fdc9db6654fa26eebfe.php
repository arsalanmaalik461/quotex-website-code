<?php $__env->startSection('title', 'Trading Signals'); ?>
<?php $__env->startSection('page-title', '📡 Trading Signals'); ?>

<?php $__env->startSection('content'); ?>


<div class="stats-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:20px;">
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Total Signals</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#fff; margin-top:4px;"><?php echo e($totalCount); ?></div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Active Signals (Live on Terminal)</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:var(--green); margin-top:4px;"><?php echo e($activeCount); ?></div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Past / Expired Signals</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:var(--text-muted); margin-top:4px;"><?php echo e($expiredCount); ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap:wrap; gap:16px; display:flex; align-items:center; justify-content:space-between;">
        <div class="flex items-center gap-3">
            <div class="card-title">Signals List</div>
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.signals.index')); ?>" class="btn btn-sm <?php echo e(!request('status') ? 'btn-primary' : 'btn-ghost'); ?>">
                    All
                </a>
                <a href="<?php echo e(route('admin.signals.index', ['status' => 'active'])); ?>" class="btn btn-sm <?php echo e(request('status') === 'active' ? 'btn-primary' : 'btn-ghost'); ?>">
                    Active Only
                </a>
                <a href="<?php echo e(route('admin.signals.index', ['status' => 'expired'])); ?>" class="btn btn-sm <?php echo e(request('status') === 'expired' ? 'btn-primary' : 'btn-ghost'); ?>">
                    Expired Only
                </a>
            </div>
        </div>

        <div>
            <a href="<?php echo e(route('admin.signals.create')); ?>" class="btn btn-success" style="background:var(--green); color:#fff; padding:8px 16px; border-radius:6px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <span>+ Create Signal</span>
            </a>
        </div>
    </div>

    <?php if($signals->isEmpty()): ?>
        <div style="text-align: center; padding: 48px; color: var(--text-muted);">
            <div style="font-size: 36px; margin-bottom: 8px;">📡</div>
            <p>No trading signals found. Click "+ Create Signal" to launch a live trading signal for your users.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border); text-align:left; color:var(--text-muted); font-size:12px;">
                        <th style="padding:12px 14px;">Pair / Asset</th>
                        <th style="padding:12px 14px;">Direction</th>
                        <th style="padding:12px 14px;">Duration</th>
                        <th style="padding:12px 14px;">Start Time</th>
                        <th style="padding:12px 14px;">Expires At</th>
                        <th style="padding:12px 14px;">Status</th>
                        <th style="padding:12px 14px; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $signals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isLiveActive = $s->is_active && ($s->expires_at ? $s->expires_at->isFuture() : true);
                    ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05); font-size:13px;">
                        <td style="padding:12px 14px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <strong style="color:#fff; font-size:14px;"><?php echo e($s->pair_name); ?></strong>
                                <?php if($s->payout_percent): ?>
                                    <span style="font-size:11px; background:rgba(0,209,122,0.15); color:var(--green); padding:2px 6px; border-radius:4px; font-weight:700;"><?php echo e($s->payout_percent); ?>%</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="padding:12px 14px;">
                            <?php if(strtolower($s->direction) === 'call'): ?>
                                <span style="display:inline-flex; align-items:center; gap:4px; color:#00d17a; font-weight:800; background:rgba(0,209,122,0.12); padding:4px 10px; border-radius:20px; font-size:12px;">
                                    ▲ CALL / UP
                                </span>
                            <?php else: ?>
                                <span style="display:inline-flex; align-items:center; gap:4px; color:#ff4c6a; font-weight:800; background:rgba(255,76,106,0.12); padding:4px 10px; border-radius:20px; font-size:12px;">
                                    ▼ PUT / DOWN
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px 14px; font-weight:600; color:#cbd5e1;">
                            ⏱ <?php echo e($s->duration); ?>

                        </td>
                        <td style="padding:12px 14px; color:var(--text-muted); font-size:12px;">
                            <?php echo e($s->start_time ? $s->start_time->format('d M, H:i:s') : '-'); ?>

                        </td>
                        <td style="padding:12px 14px; font-size:12px;">
                            <?php if($s->expires_at): ?>
                                <span style="color:<?php echo e($isLiveActive ? '#00d17a' : 'var(--text-muted)'); ?>;">
                                    <?php echo e($s->expires_at->format('d M, H:i:s')); ?>

                                </span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">No Expiry</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px 14px;">
                            <?php if($isLiveActive): ?>
                                <span class="badge" style="background:rgba(0,209,122,0.15); color:#00d17a; border:1px solid rgba(0,209,122,0.3); padding:4px 8px; border-radius:4px; font-weight:700;">
                                    ● ACTIVE (Tradeable)
                                </span>
                            <?php else: ?>
                                <span class="badge" style="background:rgba(120,134,156,0.15); color:#8b96a8; border:1px solid rgba(120,134,156,0.2); padding:4px 8px; border-radius:4px; font-weight:600;">
                                    ✕ PAST / EXPIRED
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px 14px; text-align:right;">
                            <div style="display:inline-flex; align-items:center; gap:8px;">
                                <form action="<?php echo e(route('admin.signals.toggle', $s->id)); ?>" method="POST" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm" style="font-size:11px; padding:4px 8px; border:1px solid var(--border); background:var(--bg-primary); color:var(--text-secondary); border-radius:4px; cursor:pointer;" title="<?php echo e($s->is_active ? 'Expire Signal Now' : 'Reactivate Signal'); ?>">
                                        <?php echo e($s->is_active ? 'Expire' : 'Reactivate'); ?>

                                    </button>
                                </form>

                                <form action="<?php echo e(route('admin.signals.destroy', $s->id)); ?>" method="POST" onsubmit="return confirm('Delete signal for <?php echo e($s->pair_name); ?>?')" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm" style="font-size:11px; padding:4px 8px; border:1px solid rgba(255,76,106,0.3); background:rgba(255,76,106,0.1); color:#ff4c6a; border-radius:4px; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            <?php echo e($signals->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/admin/signals/index.blade.php ENDPATH**/ ?>