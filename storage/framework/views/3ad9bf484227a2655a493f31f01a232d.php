<?php $__env->startSection('title', 'All Transactions'); ?>
<?php $__env->startSection('page-title', '📋 All Transactions'); ?>

<?php $__env->startSection('content'); ?>
<div class="card mb-4">
    <form method="GET" class="flex gap-3 items-center" style="flex-wrap:wrap">
        <select name="type" class="form-control" style="max-width:150px">
            <option value="">All Types</option>
            <option value="deposit" <?php echo e(request('type') === 'deposit' ? 'selected' : ''); ?>>Deposits</option>
            <option value="withdrawal" <?php echo e(request('type') === 'withdrawal' ? 'selected' : ''); ?>>Withdrawals</option>
        </select>
        <select name="status" class="form-control" style="max-width:150px">
            <option value="">All Status</option>
            <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
            <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Approved</option>
            <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejected</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="<?php echo e(route('admin.transactions.all')); ?>" class="btn btn-ghost">Clear</a>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>User</th><th>Type</th><th>Amount</th><th>Method</th><th>Status</th><th>Notes</th><th>Date</th></tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="color:var(--text-muted)"><?php echo e($tx->id); ?></td>
                <td><?php echo e($tx->user?->name ?? 'Deleted'); ?></td>
                <td>
                    <?php if($tx->type === 'deposit'): ?> <span class="badge-status green">Deposit</span>
                    <?php else: ?> <span class="badge-status red">Withdrawal</span> <?php endif; ?>
                </td>
                <td style="font-weight:700">$<?php echo e(number_format($tx->amount, 2)); ?></td>
                <td><?php echo e($tx->method); ?></td>
                <td>
                    <?php if($tx->status === 'approved'): ?> <span class="badge-status green">✓ Approved</span>
                    <?php elseif($tx->status === 'rejected'): ?> <span class="badge-status red">✕ Rejected</span>
                    <?php else: ?> <span class="badge-status orange">⏳ Pending</span> <?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted)"><?php echo e(Str::limit($tx->notes, 40)); ?></td>
                <td><?php echo e($tx->created_at->format('M d, H:i')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:32px">No transactions</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px"><?php echo e($transactions->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/transactions/all.blade.php ENDPATH**/ ?>