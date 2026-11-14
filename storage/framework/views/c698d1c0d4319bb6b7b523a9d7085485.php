<?php $__env->startSection('title', 'KYC Verifications'); ?>
<?php $__env->startSection('page-title', '🪪 KYC Verifications'); ?>

<?php $__env->startSection('content'); ?>


<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-label">Pending Reviews</div>
        <div class="stat-value orange"><?php echo e($stats['pending']); ?></div>
        <div class="stat-sub">Awaiting admin action</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Approved Users</div>
        <div class="stat-value green"><?php echo e($stats['approved']); ?></div>
        <div class="stat-sub">Verified accounts</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Rejected KYCs</div>
        <div class="stat-value red"><?php echo e($stats['rejected']); ?></div>
        <div class="stat-sub">Requires user re-submission</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Submissions</div>
        <div class="stat-value blue"><?php echo e($stats['total']); ?></div>
        <div class="stat-sub">Lifetime submissions</div>
    </div>
</div>

<div class="card">
    
    <div class="card-header" style="flex-wrap:wrap; gap:16px;">
        <div class="flex items-center gap-3">
            <div class="card-title">All KYC Applications</div>
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.kyc.index', ['status' => 'pending'])); ?>" class="btn btn-sm <?php echo e(request('status') === 'pending' || !request('status') ? 'btn-primary' : 'btn-ghost'); ?>">
                    Pending (<?php echo e($stats['pending']); ?>)
                </a>
                <a href="<?php echo e(route('admin.kyc.index', ['status' => 'approved'])); ?>" class="btn btn-sm <?php echo e(request('status') === 'approved' ? 'btn-primary' : 'btn-ghost'); ?>">
                    Approved (<?php echo e($stats['approved']); ?>)
                </a>
                <a href="<?php echo e(route('admin.kyc.index', ['status' => 'rejected'])); ?>" class="btn btn-sm <?php echo e(request('status') === 'rejected' ? 'btn-primary' : 'btn-ghost'); ?>">
                    Rejected (<?php echo e($stats['rejected']); ?>)
                </a>
                <a href="<?php echo e(route('admin.kyc.index', ['status' => 'all'])); ?>" class="btn btn-sm <?php echo e(request('status') === 'all' ? 'btn-primary' : 'btn-ghost'); ?>">
                    All (<?php echo e($stats['total']); ?>)
                </a>
            </div>
        </div>

        <form method="GET" action="<?php echo e(route('admin.kyc.index')); ?>" class="flex gap-2">
            <?php if(request('status')): ?>
                <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
            <?php endif; ?>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, phone, email..." value="<?php echo e(request('search')); ?>" style="min-width: 220px;">
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
            <?php if(request('search')): ?>
                <a href="<?php echo e(route('admin.kyc.index', ['status' => request('status')])); ?>" class="btn btn-ghost btn-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    
    <?php if($verifications->isEmpty()): ?>
        <div style="text-align: center; padding: 48px; color: var(--text-muted);">
            <div style="font-size: 32px; margin-bottom: 8px;">📭</div>
            <p>No KYC submissions found matching this filter.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID / Date</th>
                        <th>User Account</th>
                        <th>Applicant Details</th>
                        <th>Document Proofs</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $verifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <strong>#<?php echo e($v->id); ?></strong><br>
                            <span style="font-size: 11px; color: var(--text-muted);"><?php echo e($v->created_at->format('M d, Y H:i')); ?></span>
                        </td>
                        <td>
                            <?php if($v->user): ?>
                                <a href="<?php echo e(route('admin.users.show', $v->user_id)); ?>" style="color: var(--accent); font-weight: 600; text-decoration: none;">
                                    <?php echo e($v->user->name); ?>

                                </a><br>
                                <span style="font-size: 11px; color: var(--text-muted);"><?php echo e($v->user->email); ?></span>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">User deleted (#<?php echo e($v->user_id); ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo e($v->full_name); ?></strong><br>
                            <span style="font-size: 12px; color: var(--text-secondary);">📞 <?php echo e($v->phone); ?></span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <div title="ID Front" style="text-align:center;">
                                    <img src="<?php echo e($v->id_card_front_url); ?>" style="width: 44px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border); cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                    <div style="font-size: 9px; color: var(--text-muted);">Front</div>
                                </div>
                                <div title="ID Back" style="text-align:center;">
                                    <img src="<?php echo e($v->id_card_back_url); ?>" style="width: 44px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border); cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                    <div style="font-size: 9px; color: var(--text-muted);">Back</div>
                                </div>
                                <div title="Live Selfie" style="text-align:center;">
                                    <img src="<?php echo e($v->live_selfie_url); ?>" style="width: 32px; height: 32px; object-fit: cover; border-radius: 50%; border: 2px solid var(--accent); cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                    <div style="font-size: 9px; color: var(--accent);">Selfie</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if($v->status === 'approved'): ?>
                                <span class="badge-status green">✓ Approved</span>
                            <?php elseif($v->status === 'rejected'): ?>
                                <span class="badge-status red">❌ Rejected</span>
                                <?php if($v->rejection_reason): ?>
                                    <div style="font-size: 11px; color: var(--red); max-width: 180px; margin-top: 4px;" title="<?php echo e($v->rejection_reason); ?>">
                                        <?php echo e(Str::limit($v->rejection_reason, 45)); ?>

                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge-status orange">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?php echo e(route('admin.kyc.show', $v)); ?>" class="btn btn-ghost btn-sm" title="Inspect Full KYC">
                                    🔍 Review
                                </a>

                                <?php if($v->status !== 'approved'): ?>
                                <form method="POST" action="<?php echo e(route('admin.kyc.approve', $v)); ?>" onsubmit="return confirm('Approve KYC verification for <?php echo e($v->full_name); ?>?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <?php endif; ?>

                                <?php if($v->status !== 'rejected'): ?>
                                <button type="button" class="btn btn-danger btn-sm" onclick="openRejectModal('<?php echo e($v->id); ?>', '<?php echo e(addslashes($v->full_name)); ?>')">
                                    Reject
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            <?php echo e($verifications->links()); ?>

        </div>
    <?php endif; ?>
</div>


<div id="rejectModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width: 100%; max-width: 480px; margin: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.8);">
        <div class="card-header">
            <div class="card-title" style="color:var(--red);">Reject KYC Verification</div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeRejectModal()">✕</button>
        </div>

        <form id="rejectForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <div style="padding: 10px 0 20px 0;">
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">
                    Rejecting KYC for: <strong id="rejectUserName" style="color:#fff;"></strong>.
                    Please provide a clear rejection reason. This reason will be shown directly to the user so they can correct it and resubmit.
                </p>

                <div class="form-group">
                    <label class="form-label" for="rejection_reason">Rejection Reason *</label>
                    <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" placeholder="e.g. ID card photo is blurry or unreadable. Live selfie face does not clearly match the ID card photo. Please retake photos in good lighting." required></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function openRejectModal(kycId, userName) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    const nameSpan = document.getElementById('rejectUserName');

    form.action = `/admin/kyc/${kycId}/reject`;
    nameSpan.innerText = userName;
    modal.style.display = 'flex';
    document.getElementById('rejection_reason').focus();
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.getElementById('rejection_reason').value = '';
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/admin/kyc/index.blade.php ENDPATH**/ ?>