<?php $__env->startSection('title', 'Add Promo Code'); ?>
<?php $__env->startSection('page-title', '➕ Create New Promo Code'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width:800px; margin:0 auto;">
    <div class="card">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <div class="card-title">Promo Code Information</div>
            <a href="<?php echo e(route('admin.promo-codes.index')); ?>" class="btn btn-sm btn-ghost">
                ← Back to List
            </a>
        </div>

        <?php if($errors->any()): ?>
            <div style="background:rgba(255,76,106,0.15); border:1px solid var(--red); color:var(--red); padding:12px 16px; border-radius:8px; margin-bottom:20px;">
                <ul style="margin:0; padding-left:20px;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($err); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.promo-codes.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                
                <div class="form-group">
                    <label class="form-label">Promo Code <span style="color:var(--red)">*</span></label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. DEPOSIT50" value="<?php echo e(old('code')); ?>" required style="text-transform:uppercase; font-family:monospace; font-weight:700;">
                    <small style="color:var(--text-muted); font-size:11px;">Unique uppercase promo identifier.</small>
                </div>

                
                <div class="form-group">
                    <label class="form-label">Category <span style="color:var(--red)">*</span></label>
                    <select name="category" class="form-control" required>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php echo e(old('category') === $key ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                
                <div class="form-group">
                    <label class="form-label">Reward Type <span style="color:var(--red)">*</span></label>
                    <select name="reward_type" id="rewardTypeSelect" class="form-control" required onchange="updateRewardTypeLabel()">
                        <option value="percentage" <?php echo e(old('reward_type') === 'percentage' ? 'selected' : ''); ?>>Percentage (%)</option>
                        <option value="fixed_amount" <?php echo e(old('reward_type') === 'fixed_amount' ? 'selected' : ''); ?>>Fixed Amount ($)</option>
                        <option value="points" <?php echo e(old('reward_type') === 'points' ? 'selected' : ''); ?>>Points / Pips</option>
                    </select>
                </div>

                
                <div class="form-group">
                    <label class="form-label" id="rewardValueLabel">Bonus Percentage / Value <span style="color:var(--red)">*</span></label>
                    <input type="number" step="0.01" name="reward_value" class="form-control" placeholder="e.g. 50" value="<?php echo e(old('reward_value', '50.00')); ?>" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                
                <div class="form-group">
                    <label class="form-label">Minimum Deposit Requirement ($)</label>
                    <input type="number" step="0.01" name="min_deposit" class="form-control" placeholder="e.g. 10.00 (optional)" value="<?php echo e(old('min_deposit')); ?>">
                    <small style="color:var(--text-muted); font-size:11px;">Leave empty or 0 if no minimum deposit.</small>
                </div>

                
                <div class="form-group">
                    <label class="form-label">Expiration Date & Time</label>
                    <input type="datetime-local" name="expires_at" id="expiresAtInput" class="form-control" value="<?php echo e(old('expires_at')); ?>">
                    <small style="color:var(--text-muted); font-size:11px;">Ignored if "Unlimited" is checked below.</small>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?php echo e(old('sort_order', 0)); ?>">
                </div>

                <div style="display:flex; flex-direction:column; gap:10px; justify-content:center; padding-top:14px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-primary); font-size:13px;">
                        <input type="checkbox" name="is_unlimited" id="isUnlimitedCheckbox" value="1" <?php echo e(old('is_unlimited') ? 'checked' : ''); ?> onchange="toggleUnlimited()">
                        <span>♾️ <strong>Unlimited Validity</strong> (never expires)</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-primary); font-size:13px;">
                        <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                        <span>🟢 <strong>Active immediately</strong></span>
                    </label>
                </div>
            </div>

            
            <div class="form-group">
                <label class="form-label">Description / Helper Text</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this promo code bonus terms..."><?php echo e(old('description')); ?></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px; border-top:1px solid var(--border); padding-top:20px;">
                <a href="<?php echo e(route('admin.promo-codes.index')); ?>" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-success" style="background:var(--green); color:#fff; font-weight:700; padding:10px 24px;">
                    Save Promo Code
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleUnlimited() {
        const checkbox = document.getElementById('isUnlimitedCheckbox');
        const input = document.getElementById('expiresAtInput');
        if (checkbox.checked) {
            input.disabled = true;
            input.style.opacity = '0.5';
        } else {
            input.disabled = false;
            input.style.opacity = '1';
        }
    }
    toggleUnlimited();

    function updateRewardTypeLabel() {
        const type = document.getElementById('rewardTypeSelect').value;
        const label = document.getElementById('rewardValueLabel');
        if (type === 'percentage') {
            label.innerHTML = 'Bonus Percentage (%) <span style="color:var(--red)">*</span>';
        } else if (type === 'fixed_amount') {
            label.innerHTML = 'Fixed Reward Amount ($) <span style="color:var(--red)">*</span>';
        } else {
            label.innerHTML = 'Points / Pips Amount <span style="color:var(--red)">*</span>';
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/promo_codes/create.blade.php ENDPATH**/ ?>