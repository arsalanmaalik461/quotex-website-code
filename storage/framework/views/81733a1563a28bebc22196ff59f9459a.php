<?php $__env->startSection('title', 'Review KYC: ' . $kyc->full_name); ?>
<?php $__env->startSection('page-title', '🪪 KYC Document Inspection'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.doc-inspect-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
}
.doc-inspect-img {
    width: 100%;
    height: 280px;
    object-fit: contain;
    background: #000;
    border-radius: 8px;
    border: 1px solid var(--border);
    cursor: zoom-in;
    transition: transform 0.2s;
}
.doc-inspect-img:hover {
    transform: scale(1.01);
}
.selfie-inspect-wrap {
    width: 220px;
    height: 220px;
    margin: 0 auto 12px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--accent);
    box-shadow: 0 0 20px rgba(79, 142, 247, 0.3);
}
.selfie-inspect-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: zoom-in;
}

/* Lightbox */
.lightbox-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    padding: 24px;
}
.lightbox-overlay img {
    max-width: 90vw;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.8);
}
.lightbox-close {
    position: absolute;
    top: 20px;
    right: 28px;
    font-size: 32px;
    color: #fff;
    cursor: pointer;
    background: none;
    border: none;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div style="margin-bottom: 16px; display:flex; justify-content:space-between; align-items:center;">
    <a href="<?php echo e(route('admin.kyc.index')); ?>" class="btn btn-ghost btn-sm">← Back to KYC List</a>
    <div class="flex gap-2">
        <?php if($kyc->user): ?>
            <a href="<?php echo e(route('admin.users.show', $kyc->user_id)); ?>" class="btn btn-ghost btn-sm">
                👤 View User Profile #<?php echo e($kyc->user_id); ?>

            </a>
        <?php endif; ?>
    </div>
</div>


<?php if($kyc->status === 'approved'): ?>
    <div class="alert alert-success" style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); color: var(--green); padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <strong>✓ KYC Approved</strong> — This user is fully verified. Reviewed by <?php echo e($kyc->reviewer->name ?? 'Admin'); ?> on <?php echo e($kyc->reviewed_at ? $kyc->reviewed_at->format('M d, Y H:i') : 'N/A'); ?>.
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="openRejectModal()">Revoke & Reject</button>
    </div>
<?php elseif($kyc->status === 'rejected'): ?>
    <div class="alert alert-danger" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: var(--red); padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <strong>❌ KYC Rejected</strong> — Reason: <em>"<?php echo e($kyc->rejection_reason); ?>"</em> (Reviewed on <?php echo e($kyc->reviewed_at ? $kyc->reviewed_at->format('M d, Y H:i') : 'N/A'); ?>).
        </div>
        <form method="POST" action="<?php echo e(route('admin.kyc.approve', $kyc)); ?>" onsubmit="return confirm('Change status to Approved?')">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-success btn-sm">Change to Approved</button>
        </form>
    </div>
<?php else: ?>
    <div class="alert alert-warning" style="background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.4); color: var(--orange); padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <strong>⏳ Awaiting Review</strong> — Submitted on <?php echo e($kyc->created_at->format('M d, Y H:i')); ?>. Please inspect the applicant's ID and selfie below.
        </div>
        <div class="flex gap-2">
            <form method="POST" action="<?php echo e(route('admin.kyc.approve', $kyc)); ?>" onsubmit="return confirm('Approve KYC verification for <?php echo e($kyc->full_name); ?>?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-success">✓ Approve KYC</button>
            </form>
            <button type="button" class="btn btn-danger" onclick="openRejectModal()">✕ Reject with Reason</button>
        </div>
    </div>
<?php endif; ?>


<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <div class="card-title">Applicant Identity Information</div>
        <div>
            <span class="badge-status <?php echo e($kyc->status === 'approved' ? 'green' : ($kyc->status === 'rejected' ? 'red' : 'orange')); ?>">
                Status: <?php echo e(ucfirst($kyc->status)); ?>

            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; font-size: 13px;">
        <div style="padding: 12px; background: var(--bg-hover); border-radius: 8px;">
            <div style="color: var(--text-muted); margin-bottom: 4px;">Submitted Full Name</div>
            <strong style="font-size: 15px; color: #fff;"><?php echo e($kyc->full_name); ?></strong>
        </div>
        <div style="padding: 12px; background: var(--bg-hover); border-radius: 8px;">
            <div style="color: var(--text-muted); margin-bottom: 4px;">Phone Number</div>
            <strong style="font-size: 15px; color: var(--accent);"><?php echo e($kyc->phone); ?></strong>
        </div>
        <div style="padding: 12px; background: var(--bg-hover); border-radius: 8px;">
            <div style="color: var(--text-muted); margin-bottom: 4px;">Platform User Account</div>
            <strong><?php echo e($kyc->user->name ?? 'N/A'); ?></strong> (ID #<?php echo e($kyc->user_id); ?>)
        </div>
        <div style="padding: 12px; background: var(--bg-hover); border-radius: 8px;">
            <div style="color: var(--text-muted); margin-bottom: 4px;">Registered Email</div>
            <strong><?php echo e($kyc->user->email ?? 'N/A'); ?></strong>
        </div>
        <div style="padding: 12px; background: var(--bg-hover); border-radius: 8px;">
            <div style="color: var(--text-muted); margin-bottom: 4px;">Live Balance</div>
            <strong style="color: var(--green);">$<?php echo e(number_format($kyc->user->live_balance ?? 0, 2)); ?></strong>
        </div>
        <div style="padding: 12px; background: var(--bg-hover); border-radius: 8px;">
            <div style="color: var(--text-muted); margin-bottom: 4px;">Submission Timestamp</div>
            <strong><?php echo e($kyc->created_at->format('M d, Y · H:i:s')); ?></strong>
        </div>
    </div>
</div>


<h3 style="font-size: 16px; color: #fff; margin-bottom: 16px;">Submitted Document Proofs</h3>

<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px;">
    <?php if(isset($isMobile) || false): ?>
    <?php endif; ?>

    
    <div class="doc-inspect-card">
        <div style="font-weight: 600; color: #fff; margin-bottom: 10px; display:flex; justify-content:space-between; align-items:center;">
            <span>ID Card (Front)</span>
            <span style="font-size: 11px; color: var(--text-muted);">Click to zoom</span>
        </div>
        <img src="<?php echo e($kyc->id_card_front_url); ?>" class="doc-inspect-img" alt="ID Card Front" onclick="openLightbox(this.src)">
        <div style="margin-top: 10px;">
            <a href="<?php echo e($kyc->id_card_front_url); ?>" target="_blank" class="btn btn-ghost btn-sm" style="width: 100%;">Open Full Size ↗</a>
        </div>
    </div>

    
    <div class="doc-inspect-card">
        <div style="font-weight: 600; color: #fff; margin-bottom: 10px; display:flex; justify-content:space-between; align-items:center;">
            <span>ID Card (Back)</span>
            <span style="font-size: 11px; color: var(--text-muted);">Click to zoom</span>
        </div>
        <img src="<?php echo e($kyc->id_card_back_url); ?>" class="doc-inspect-img" alt="ID Card Back" onclick="openLightbox(this.src)">
        <div style="margin-top: 10px;">
            <a href="<?php echo e($kyc->id_card_back_url); ?>" target="_blank" class="btn btn-ghost btn-sm" style="width: 100%;">Open Full Size ↗</a>
        </div>
    </div>

    
    <div class="doc-inspect-card">
        <div style="font-weight: 600; color: #fff; margin-bottom: 10px; display:flex; justify-content:space-between; align-items:center;">
            <span>Live Front-Camera Selfie</span>
            <span style="font-size: 11px; color: var(--accent);">Front Cam Proof</span>
        </div>
        <div class="selfie-inspect-wrap">
            <img src="<?php echo e($kyc->live_selfie_url); ?>" alt="Live Selfie" onclick="openLightbox(this.src)">
        </div>
        <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">
            Verify face against ID card photos
        </div>
        <a href="<?php echo e($kyc->live_selfie_url); ?>" target="_blank" class="btn btn-ghost btn-sm" style="width: 100%;">Open Full Size ↗</a>
    </div>
</div>


<div class="card" style="padding: 24px; text-align: center; background: var(--bg-secondary);">
    <h4 style="font-size: 16px; color: #fff; margin-bottom: 8px;">Review Decision</h4>
    <p style="font-size: 13px; color: var(--text-secondary); max-width: 540px; margin: 0 auto 20px;">
        Compare the applicant's live front-camera selfie with their government ID card photo. Ensure all details and numbers are clearly legible.
    </p>

    <div class="flex justify-center gap-4">
        <form method="POST" action="<?php echo e(route('admin.kyc.approve', $kyc)); ?>" onsubmit="return confirm('Confirm approval of this KYC?')">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-success" style="padding: 12px 28px; font-size: 15px; font-weight: 700;">
                ✓ Approve KYC
            </button>
        </form>

        <button type="button" class="btn btn-danger" style="padding: 12px 28px; font-size: 15px; font-weight: 700;" onclick="openRejectModal()">
            ✕ Reject with Reason
        </button>
    </div>
</div>


<div id="rejectModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.8);">
        <div class="card-header">
            <div class="card-title" style="color:var(--red);">Reject KYC Verification</div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeRejectModal()">✕</button>
        </div>

        <form method="POST" action="<?php echo e(route('admin.kyc.reject', $kyc)); ?>">
            <?php echo csrf_field(); ?>
            <div style="padding: 10px 0 20px 0;">
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">
                    Rejecting KYC for: <strong style="color:#fff;"><?php echo e($kyc->full_name); ?></strong>.<br>
                    Please specify the rejection reason. The user will be notified of this reason so they can resubmit with corrected documents.
                </p>

                <div class="form-group">
                    <label class="form-label" for="rejection_reason">Rejection Reason *</label>
                    <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" placeholder="e.g. ID card photo is blurry. Live selfie face does not clearly match the ID card photograph. Please retake front-camera selfie in good lighting." required><?php echo e(old('rejection_reason', $kyc->rejection_reason)); ?></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>


<div class="lightbox-overlay" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    <img id="lightbox-img" src="" alt="Zoomed view">
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function openRejectModal() {
    document.getElementById('rejectModal').style.display = 'flex';
    document.getElementById('rejection_reason').focus();
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').style.display = 'flex';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/admin/kyc/show.blade.php ENDPATH**/ ?>