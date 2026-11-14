<?php $__env->startSection('title', 'Trading Pairs'); ?>
<?php $__env->startSection('page-title', '📊 Trading Pairs & Assets'); ?>

<?php $__env->startSection('content'); ?>

<div class="flex justify-between items-center mb-4">
    <div class="flex gap-2">
        <?php $__currentLoopData = ['','forex','crypto','commodity','index','otc','signal']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('admin.assets.index', array_merge(request()->query(), $t ? ['type'=>$t] : []))); ?>"
           class="btn btn-sm <?php echo e(request('type') === $t || (!request('type') && $t === '') ? 'btn-primary' : 'btn-ghost'); ?>">
            <?php echo e($t ? ucfirst($t) : 'All'); ?>

        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <a href="<?php echo e(route('admin.assets.create')); ?>" class="btn btn-primary">+ Add New Pair</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Symbol</th><th>Name</th><th>Type</th><th>Payout %</th>
                    <th>OTC</th><th>Control Mode</th><th>Win Side</th><th>Trades</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="color:var(--text-muted)"><?php echo e($asset->id); ?></td>
                <td>
                    <strong style="color:var(--text-primary)">
                        <?php echo e($asset->flag); ?> <?php echo e($asset->symbol); ?>

                    </strong>
                </td>
                <td><?php echo e($asset->name); ?></td>
                <td><span class="badge-status blue"><?php echo e($asset->type); ?></span></td>
                <td><?php echo e($asset->payout_percent); ?>%</td>
                <td><?php echo e($asset->is_otc ? '✓' : '—'); ?></td>
                <td>
                    <?php if($asset->control_mode === 'pump'): ?>
                        <span class="badge-status green">▲ PUMP <?php echo e($asset->max_pump_percent); ?>%</span>
                    <?php elseif($asset->control_mode === 'crash'): ?>
                        <span class="badge-status red">▼ CRASH <?php echo e($asset->max_crash_percent); ?>%</span>
                    <?php else: ?>
                        <span class="badge-status gray">Auto</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($asset->win_side !== 'none'): ?>
                        <span class="badge-status orange"><?php echo e(str_replace('_',' ',ucfirst($asset->win_side))); ?></span>
                    <?php else: ?>
                        <span style="color:var(--text-muted)">—</span>
                    <?php endif; ?>
                </td>
                <td><?php echo e($asset->trades_count); ?></td>
                <td>
                    <button class="badge-status <?php echo e($asset->is_active ? 'green' : 'red'); ?>"
                            onclick="toggleAsset(<?php echo e($asset->id); ?>, this)"
                            style="cursor:pointer;border:none">
                        <?php echo e($asset->is_active ? '● Active' : '○ Inactive'); ?>

                    </button>
                </td>
                <td>
                    <div class="flex gap-2">
                        <a href="<?php echo e(route('admin.assets.control', $asset)); ?>" class="btn btn-warning btn-sm" title="OTC Control">⚙</a>
                        <a href="<?php echo e(route('admin.assets.edit', $asset)); ?>" class="btn btn-ghost btn-sm">Edit</a>
                        <form method="POST" action="<?php echo e(route('admin.assets.destroy', $asset)); ?>"
                              onsubmit="return confirm('Delete <?php echo e($asset->symbol); ?>?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="11" style="text-align:center;color:var(--text-muted);padding:32px">No assets found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px"><?php echo e($assets->links()); ?></div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleAsset(id, btn) {
    fetch(`/admin/assets/${id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            btn.className = `badge-status ${d.is_active ? 'green' : 'red'}`;
            btn.textContent = d.is_active ? '● Active' : '○ Inactive';
        }
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/assets/index.blade.php ENDPATH**/ ?>