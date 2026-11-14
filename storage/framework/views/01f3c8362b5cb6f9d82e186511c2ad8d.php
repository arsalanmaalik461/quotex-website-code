<?php $__env->startSection('title', 'Market Promo Codes'); ?>
<?php $__env->startSection('page-title', '🎁 Market Promo Codes Management'); ?>

<?php $__env->startSection('content'); ?>


<div class="stats-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:20px;">
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Total Promo Codes</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#fff; margin-top:4px;"><?php echo e($stats['total']); ?></div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Active Promo Codes</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:var(--green); margin-top:4px;"><?php echo e($stats['active']); ?></div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Deposit Bonuses</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#f59e0b; margin-top:4px;"><?php echo e($stats['deposit']); ?></div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Risk Free Codes</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#3b82f6; margin-top:4px;"><?php echo e($stats['risk_free']); ?></div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Cashback Codes</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#9c27b0; margin-top:4px;"><?php echo e($stats['cashback']); ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap:wrap; gap:16px; display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <div class="card-title">Promo Code Directory</div>
            
            
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <a href="<?php echo e(route('admin.promo-codes.index')); ?>" class="btn btn-sm <?php echo e(!request('category') || request('category') === 'all' ? 'btn-primary' : 'btn-ghost'); ?>">
                    All
                </a>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('admin.promo-codes.index', ['category' => $key])); ?>" class="btn btn-sm <?php echo e(request('category') === $key ? 'btn-primary' : 'btn-ghost'); ?>">
                        <?php echo e($label); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            <a href="<?php echo e(route('admin.promo-codes.create')); ?>" class="btn btn-success" style="background:var(--green); color:#fff; padding:8px 16px; border-radius:6px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <span>+ Add Promo Code</span>
            </a>
        </div>
    </div>

    <?php if($promoCodes->isEmpty()): ?>
        <div style="text-align: center; padding: 48px; color: var(--text-muted);">
            <div style="font-size: 36px; margin-bottom: 8px;">🎁</div>
            <p>No promo codes found matching your criteria. Click "+ Add Promo Code" to create one.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border); text-align:left; color:var(--text-muted); font-size:12px;">
                        <th style="padding:12px 16px;">CODE</th>
                        <th style="padding:12px 16px;">CATEGORY</th>
                        <th style="padding:12px 16px;">REWARD / VALUE</th>
                        <th style="padding:12px 16px;">VALIDITY</th>
                        <th style="padding:12px 16px;">STATUS</th>
                        <th style="padding:12px 16px; text-align:right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $promoCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $badgeColors = [
                                'risk_free'        => ['bg' => 'rgba(59,130,246,0.15)', 'color' => '#60a5fa'],
                                'cashback'         => ['bg' => 'rgba(156,39,176,0.15)', 'color' => '#ce93d8'],
                                'deposit_bonus'    => ['bg' => 'rgba(245,158,11,0.15)', 'color' => '#fbbf24'],
                                'turnover_percent' => ['bg' => 'rgba(233,30,99,0.15)', 'color' => '#f48fb1'],
                                'balance_bonus'    => ['bg' => 'rgba(6,182,212,0.15)', 'color' => '#67e8f9'],
                                'cancel_points'    => ['bg' => 'rgba(20,184,166,0.15)', 'color' => '#5eead4'],
                            ];
                            $badge = $badgeColors[$promo->category] ?? ['bg' => 'rgba(255,255,255,0.1)', 'color' => '#fff'];
                        ?>
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:12px 16px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-family:monospace; font-weight:800; font-size:14px; background:#131722; padding:4px 8px; border-radius:6px; border:1px solid #232d3f; color:#fff;">
                                        <?php echo e($promo->code); ?>

                                    </span>
                                </div>
                                <?php if($promo->description): ?>
                                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px;"><?php echo e($promo->description); ?></div>
                                <?php endif; ?>
                            </td>

                            <td style="padding:12px 16px;">
                                <span style="background:<?php echo e($badge['bg']); ?>; color:<?php echo e($badge['color']); ?>; font-size:11px; font-weight:700; padding:4px 10px; border-radius:12px;">
                                    <?php echo e($categories[$promo->category] ?? ucfirst($promo->category)); ?>

                                </span>
                            </td>

                            <td style="padding:12px 16px;">
                                <div style="font-weight:700; color:#fff; font-size:14px;">
                                    <?php if($promo->reward_type === 'percentage'): ?>
                                        <?php echo e($promo->reward_value); ?>%
                                    <?php elseif($promo->reward_type === 'fixed_amount'): ?>
                                        $<?php echo e(number_format($promo->reward_value, 2)); ?>

                                    <?php else: ?>
                                        <?php echo e($promo->reward_value); ?> points
                                    <?php endif; ?>
                                </div>
                                <?php if($promo->min_deposit): ?>
                                    <div style="font-size:11px; color:var(--text-muted);">Min. Deposit: $<?php echo e(number_format($promo->min_deposit, 2)); ?></div>
                                <?php endif; ?>
                            </td>

                            <td style="padding:12px 16px;">
                                <?php if($promo->is_unlimited || !$promo->expires_at): ?>
                                    <span style="color:#00e676; font-weight:600; font-size:13px;">♾️ Unlimited</span>
                                <?php else: ?>
                                    <span style="color:var(--text-secondary); font-size:13px;">
                                        <?php echo e($promo->expires_at->format('d/m/Y H:i')); ?>

                                        <?php if($promo->expires_at < now()): ?>
                                            <span style="color:var(--red); font-size:11px; font-weight:700; margin-left:4px;">(Expired)</span>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td style="padding:12px 16px;">
                                <?php if($promo->is_active): ?>
                                    <span class="badge badge-success" style="background:rgba(0,209,122,0.15); color:var(--green); padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700;">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger" style="background:rgba(255,76,106,0.15); color:var(--red); padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700;">Inactive</span>
                                <?php endif; ?>
                            </td>

                            <td style="padding:12px 16px; text-align:right;">
                                <div style="display:inline-flex; align-items:center; gap:8px;">
                                    <form action="<?php echo e(route('admin.promo-codes.toggle', $promo)); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-ghost" title="<?php echo e($promo->is_active ? 'Deactivate' : 'Activate'); ?>">
                                            <?php echo e($promo->is_active ? '⏸️ Disable' : '▶️ Enable'); ?>

                                        </button>
                                    </form>

                                    <a href="<?php echo e(route('admin.promo-codes.edit', $promo)); ?>" class="btn btn-sm btn-primary" title="Edit">
                                        ✏️ Edit
                                    </a>

                                    <form action="<?php echo e(route('admin.promo-codes.destroy', $promo)); ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete promo code \'<?php echo e($promo->code); ?>\'?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div style="padding:16px 20px;">
            <?php echo e($promoCodes->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/admin/promo_codes/index.blade.php ENDPATH**/ ?>