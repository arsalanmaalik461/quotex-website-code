<?php $__env->startSection('title', 'Deposit Requests'); ?>
<?php $__env->startSection('page-title', '💰 Deposit Requests'); ?>

<?php $__env->startSection('content'); ?>


<div class="card mb-4">
    <form method="GET" class="flex gap-3 items-center">
        <select name="status" class="form-control" style="max-width:160px">
            <option value="all" <?php echo e($status === 'all' ? 'selected' : ''); ?>>All</option>
            <option value="pending" <?php echo e($status === 'pending' ? 'selected' : ''); ?>>Pending</option>
            <option value="approved" <?php echo e($status === 'approved' ? 'selected' : ''); ?>>Approved</option>
            <option value="rejected" <?php echo e($status === 'rejected' ? 'selected' : ''); ?>>Rejected</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Deposits (<?php echo e($transactions->total()); ?>)</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>User</th><th>Amount</th><th>Method</th>
                    <th>Payment Proof / TRX ID</th>
                    <th>Status</th><th>Notes</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="color:var(--text-muted)"><?php echo e($tx->id); ?></td>
                <td>
                    <a href="<?php echo e(route('admin.users.show', $tx->user)); ?>" style="color:var(--text-primary);text-decoration:none;font-weight:500">
                        <?php echo e($tx->user?->name ?? 'Deleted'); ?>

                    </a>
                    <div style="font-size:11px;color:var(--text-muted)"><?php echo e($tx->user?->email); ?></div>
                </td>
                <td>
                    <span style="color:var(--green);font-weight:700;font-size:15px">$<?php echo e(number_format($tx->amount, 2)); ?></span>
                    <?php if($tx->converted_amount): ?>
                        <div style="font-size:11px;color:var(--accent);">≈ <?php echo e(number_format($tx->converted_amount, 2)); ?> <?php echo e($tx->currency); ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?php echo e($tx->method); ?></strong>
                    <?php if($tx->wallet_address): ?>
                        <div style="font-size:11px;color:var(--text-muted);font-family:monospace;"><?php echo e(Str::limit($tx->wallet_address, 20)); ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <?php if($tx->proof_image): ?>
                            <a href="<?php echo e($tx->proof_image_url); ?>" target="_blank" title="Click to view payment receipt">
                                <img src="<?php echo e($tx->proof_image_url); ?>" style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid var(--accent);">
                            </a>
                        <?php endif; ?>
                        <div>
                            <?php if($tx->transaction_id): ?>
                                <code style="font-size:11px; background:var(--bg-hover); padding:2px 6px; border-radius:4px; color:#fff;">
                                    <?php echo e($tx->transaction_id); ?>

                                </code>
                            <?php elseif(!$tx->proof_image): ?>
                                <span style="color:var(--text-muted); font-size:11px;">No proof attached</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td>
                    <?php if($tx->status === 'approved'): ?> <span class="badge-status green">✓ Approved</span>
                    <?php elseif($tx->status === 'rejected'): ?> <span class="badge-status red">✕ Rejected</span>
                    <?php else: ?> <span class="badge-status orange">⏳ Pending</span> <?php endif; ?>
                </td>
                <td style="color:var(--text-muted);font-size:12px"><?php echo e($tx->notes ?? '—'); ?></td>
                <td><?php echo e($tx->created_at->format('M d, Y H:i')); ?></td>
                <td>
                    <?php if($tx->status === 'pending'): ?>
                    <div class="flex gap-2">
                        <form method="POST" action="<?php echo e(route('admin.transactions.approve', $tx)); ?>" onsubmit="return confirm('Approve deposit of $<?php echo e(number_format($tx->amount, 2)); ?> for <?php echo e($tx->user?->name); ?>?')">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-success btn-sm">✓ Approve</button>
                        </form>
                        <button class="btn btn-danger btn-sm" onclick="rejectModal(<?php echo e($tx->id); ?>)">✕ Reject</button>
                    </div>
                    <?php else: ?>
                        <span style="color:var(--text-muted);font-size:12px">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:32px">No deposit requests</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px"><?php echo e($transactions->links()); ?></div>
</div>


<div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:28px;width:440px;max-width:95vw">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px">Reject Deposit</div>
        <form id="rejectForm" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label class="form-label">Reason (optional)</label>
                <input type="text" name="reason" class="form-control" placeholder="e.g. Unverified payment method">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-danger">Reject</button>
                <button type="button" onclick="closeModal()" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function rejectModal(id) {
    document.getElementById('rejectForm').action = `/admin/transactions/${id}/reject`;
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/transactions/deposits.blade.php ENDPATH**/ ?>