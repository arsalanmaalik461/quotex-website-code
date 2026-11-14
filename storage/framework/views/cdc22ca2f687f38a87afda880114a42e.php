<?php $__env->startSection('title', 'Admin Logs'); ?>
<?php $__env->startSection('page-title', '📝 Admin Activity Logs'); ?>

<?php $__env->startSection('content'); ?>
<div class="card mb-4">
    <form method="GET" class="flex gap-3 items-center">
        <input type="text" name="action" value="<?php echo e(request('action')); ?>" placeholder="Filter by action..." class="form-control" style="max-width:250px">
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if(request('action')): ?> <a href="<?php echo e(route('admin.logs')); ?>" class="btn btn-ghost">Clear</a> <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Admin</th><th>Action</th><th>Target</th><th>Description</th><th>IP</th><th>Time</th></tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="color:var(--text-muted)"><?php echo e($log->id); ?></td>
                <td><?php echo e($log->admin?->name ?? 'System'); ?></td>
                <td><code style="background:var(--bg-hover);padding:2px 8px;border-radius:4px;font-size:11px;color:var(--accent)"><?php echo e($log->action); ?></code></td>
                <td style="font-size:12px;color:var(--text-muted)"><?php echo e($log->target_type); ?>#<?php echo e($log->target_id); ?></td>
                <td style="font-size:12px"><?php echo e(Str::limit($log->description, 80)); ?></td>
                <td style="font-size:11px;color:var(--text-muted)"><?php echo e($log->ip_address); ?></td>
                <td style="font-size:12px"><?php echo e($log->created_at->format('M d, H:i:s')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px">No logs yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px"><?php echo e($logs->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/admin/logs.blade.php ENDPATH**/ ?>