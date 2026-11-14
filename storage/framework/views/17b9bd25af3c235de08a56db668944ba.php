<?php $__env->startSection('title', 'Add Payment Method'); ?>
<?php $__env->startSection('page-title', '➕ Add New Payment Method'); ?>

<?php $__env->startSection('content'); ?>

<div style="margin-bottom: 16px;">
    <a href="<?php echo e(route('admin.payment-methods.index')); ?>" class="btn btn-ghost btn-sm">← Back to Payment Methods</a>
</div>

<div class="card" style="max-width: 760px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title">New Payment Method Details</div>
    </div>

    <form method="POST" action="<?php echo e(route('admin.payment-methods.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label">Method Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Easypaisa, JazzCash, USDT TRC20" value="<?php echo e(old('name')); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Type *</label>
                <select name="type" class="form-control" required>
                    <option value="both" <?php echo e(old('type') === 'both' ? 'selected' : ''); ?>>Deposit & Withdrawal</option>
                    <option value="deposit" <?php echo e(old('type') === 'deposit' ? 'selected' : ''); ?>>Deposit Only</option>
                    <option value="withdrawal" <?php echo e(old('type') === 'withdrawal' ? 'selected' : ''); ?>>Withdrawal Only</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label">Icon / Emoji</label>
                <input type="text" name="icon" class="form-control" placeholder="e.g. 📱, 🟢, 🔴, 🪙, 🏦" value="<?php echo e(old('icon', '💳')); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Currency Symbol / Code *</label>
                <input type="text" name="currency" class="form-control" placeholder="e.g. PKR, USDT, USD" value="<?php echo e(old('currency', 'PKR')); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Conversion Rate (1 USD = ?) *</label>
                <input type="number" step="0.0001" name="conversion_rate" class="form-control" placeholder="e.g. 280.00" value="<?php echo e(old('conversion_rate', '280.00')); ?>" required>
                <span style="font-size: 11px; color: var(--text-muted);">Amount in local currency per 1 USD</span>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Logo Upload</label>
            <input type="file" name="logo" class="form-control" accept="image/*">
            <span style="font-size: 11px; color: var(--text-muted);">Optional image logo shown on the deposit page.</span>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Receiving Wallet Address / Account Number</label>
            <input type="text" name="wallet_address" class="form-control" placeholder="e.g. 03001234567 or TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t" value="<?php echo e(old('wallet_address')); ?>">
            <span style="font-size: 11px; color: var(--text-muted);">This is the address/number shown to customers for depositing, with a Copy button</span>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Payment Instructions / Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="e.g. Account Title: John Doe&#10;Account No: 0300-1234567&#10;Please send exact amount and submit Transaction ID or receipt screenshot below."><?php echo e(old('description')); ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div class="form-group">
                <label class="form-label">Min Amount (USD) *</label>
                <input type="number" step="1" name="min_amount" class="form-control" value="<?php echo e(old('min_amount', '10')); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Max Amount (USD) *</label>
                <input type="number" step="1" name="max_amount" class="form-control" value="<?php echo e(old('max_amount', '50000')); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="<?php echo e(old('sort_order', '0')); ?>">
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <label class="flex items-center gap-2" style="cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', '1') ? 'checked' : ''); ?>>
                <span style="font-size: 13px; font-weight: 600; color: #fff;">Enable this payment method immediately</span>
            </label>
        </div>

        <?php if($categories->isNotEmpty()): ?>
            <div class="card" style="background:var(--bg-hover);margin-bottom:24px;">
                <div class="card-header">
                    <div>
                        <div class="card-title">Deposit Categories</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Assign this method to one or more categories.</div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;">
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label style="display:flex;gap:8px;align-items:center;color:#fff;font-size:13px;">
                            <input type="checkbox" name="category_ids[]" value="<?php echo e($category->id); ?>" <?php echo e(in_array($category->id, old('category_ids', [])) ? 'checked' : ''); ?>>
                            <?php echo e($category->name); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex justify-end gap-3">
            <a href="<?php echo e(route('admin.payment-methods.index')); ?>" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-success">Create Payment Method</button>
        </div>
    </form>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/admin/payment-methods/create.blade.php ENDPATH**/ ?>