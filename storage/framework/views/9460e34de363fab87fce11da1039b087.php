<?php $__env->startSection('title', 'Payment Methods'); ?>
<?php $__env->startSection('page-title', '💳 Deposit & Withdrawal Methods'); ?>

<?php $__env->startSection('content'); ?>

<?php if($lgPayMethods->isNotEmpty()): ?>
<div class="card" style="margin-bottom: 18px;">
    <div class="card-header">
        <div>
            <div class="card-title">Online Auto Payment</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Gateway credentials are loaded from server environment variables.</div>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('admin.payment-methods.lgpay.update')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div style="margin-bottom: 18px;">
            <label class="flex items-center gap-2" style="cursor:pointer;">
                <input type="checkbox" name="lgpay_enabled" value="1" <?php echo e($lgPayEnabled ? 'checked' : ''); ?>>
                <span style="font-size:13px;font-weight:600;color:#fff;">Master Status: Enabled</span>
            </label>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
            <?php $__currentLoopData = $lgPayMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="border:1px solid var(--border);border-radius:8px;padding:16px;background:var(--bg-hover);">
                    <div class="flex items-center justify-between" style="margin-bottom:12px;">
                        <strong style="color:#fff;"><?php echo e($lg->provider_code === 'easypaisa' ? 'EasyPaisa' : 'JazzCash'); ?></strong>
                        <label class="flex items-center gap-2" style="cursor:pointer;font-size:12px;color:var(--text-muted);">
                            <input type="checkbox" name="methods[<?php echo e($lg->id); ?>][is_active]" value="1" <?php echo e($lg->is_active ? 'checked' : ''); ?>>
                            Enabled
                        </label>
                    </div>

                    <div class="form-group" style="margin-bottom:10px;">
                        <label class="form-label">Display Name</label>
                        <input class="form-control" name="methods[<?php echo e($lg->id); ?>][name]" value="<?php echo e(old("methods.{$lg->id}.name", $lg->name)); ?>" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                        <div class="form-group">
                            <label class="form-label">Logo / Key</label>
                            <input class="form-control" name="methods[<?php echo e($lg->id); ?>][icon]" value="<?php echo e(old("methods.{$lg->id}.icon", $lg->icon)); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Trade Type</label>
                            <input class="form-control" name="methods[<?php echo e($lg->id); ?>][trade_type]" value="<?php echo e(old("methods.{$lg->id}.trade_type", $lg->trade_type)); ?>" required>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                        <div class="form-group">
                            <label class="form-label">USD to PKR Rate</label>
                            <input type="number" step="0.0001" class="form-control" name="methods[<?php echo e($lg->id); ?>][conversion_rate]" value="<?php echo e(old("methods.{$lg->id}.conversion_rate", $lg->conversion_rate)); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gateway Currency</label>
                            <input class="form-control" value="PKR" disabled>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:10px;">
                        <label class="form-label">Logo Image</label>
                        <?php if($lg->logo_url): ?>
                            <div class="flex items-center gap-2" style="margin-bottom:8px;">
                                <img src="<?php echo e($lg->logo_url); ?>" alt="<?php echo e($lg->name); ?>" style="width:42px;height:42px;border-radius:6px;object-fit:contain;background:#fff;padding:3px;">
                                <label class="flex items-center gap-2" style="font-size:12px;color:var(--text-muted);cursor:pointer;">
                                    <input type="checkbox" name="methods[<?php echo e($lg->id); ?>][remove_logo]" value="1">
                                    Remove current logo
                                </label>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="methods[<?php echo e($lg->id); ?>][logo]" accept="image/jpeg,image/png,image/webp">
                        <span style="font-size:11px;color:var(--text-muted);">JPG, PNG, or WebP. Leave empty to keep current logo.</span>
                    </div>

                    <?php if($categories->isNotEmpty()): ?>
                        <div class="form-group" style="margin-bottom:10px;">
                            <label class="form-label">Deposit Categories</label>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:6px;">
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label style="font-size:12px;color:var(--text-muted);display:flex;gap:6px;align-items:center;">
                                        <input type="checkbox" name="methods[<?php echo e($lg->id); ?>][category_ids][]" value="<?php echo e($category->id); ?>" <?php echo e($lg->categories->contains($category->id) ? 'checked' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                        <div class="form-group">
                            <label class="form-label">Minimum Deposit</label>
                            <input type="number" step="1" class="form-control" name="methods[<?php echo e($lg->id); ?>][min_amount]" value="<?php echo e(old("methods.{$lg->id}.min_amount", $lg->min_amount)); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Maximum Deposit</label>
                            <input type="number" step="1" class="form-control" name="methods[<?php echo e($lg->id); ?>][max_amount]" value="<?php echo e(old("methods.{$lg->id}.max_amount", $lg->max_amount)); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="methods[<?php echo e($lg->id); ?>][sort_order]" value="<?php echo e(old("methods.{$lg->id}.sort_order", $lg->sort_order)); ?>">
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="flex justify-end" style="margin-top:16px;">
            <button type="submit" class="btn btn-primary">Save Online Payment Settings</button>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="flex-wrap:wrap; gap:16px;">
        <div class="flex items-center gap-3">
            <div class="card-title">All Payment Methods</div>
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.payment-methods.index')); ?>" class="btn btn-sm <?php echo e(!request('type') ? 'btn-primary' : 'btn-ghost'); ?>">
                    All
                </a>
                <a href="<?php echo e(route('admin.payment-methods.index', ['type' => 'deposit'])); ?>" class="btn btn-sm <?php echo e(request('type') === 'deposit' ? 'btn-primary' : 'btn-ghost'); ?>">
                    Deposit
                </a>
                <a href="<?php echo e(route('admin.payment-methods.index', ['type' => 'withdrawal'])); ?>" class="btn btn-sm <?php echo e(request('type') === 'withdrawal' ? 'btn-primary' : 'btn-ghost'); ?>">
                    Withdrawal
                </a>
            </div>
        </div>

        <div>
            <a href="<?php echo e(route('admin.payment-categories.index')); ?>" class="btn btn-ghost" style="margin-right:8px;">
                Categories
            </a>
            <a href="<?php echo e(route('admin.payment-methods.create')); ?>" class="btn btn-success">
                + Add Payment Method
            </a>
        </div>
    </div>

    <?php if($methods->isEmpty()): ?>
        <div style="text-align: center; padding: 48px; color: var(--text-muted);">
            <div style="font-size: 32px; margin-bottom: 8px;">💳</div>
            <p>No payment methods created yet. Click "+ Add Payment Method" to add your first deposit or withdrawal method.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Icon & Name</th>
                        <th>Type</th>
                        <th>Conversion Rate</th>
                        <th>Wallet / Account Address</th>
                        <th>Limits (USD)</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <span style="font-size: 24px;"><?php echo e($m->icon ?? '💳'); ?></span>
                                <div>
                                    <strong style="color: #fff;"><?php echo e($m->name); ?></strong><br>
                                    <span style="font-size: 11px; color: var(--text-muted);">Sort: #<?php echo e($m->sort_order); ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if($m->type === 'both'): ?>
                                <span class="badge-status blue">Deposit & Withdrawal</span>
                            <?php elseif($m->type === 'deposit'): ?>
                                <span class="badge-status green">Deposit Only</span>
                            <?php else: ?>
                                <span class="badge-status orange">Withdrawal Only</span>
                            <?php endif; ?>
                            <?php if($m->categories->isNotEmpty()): ?>
                                <div style="font-size:11px;color:var(--text-muted);margin-top:5px;">
                                    <?php echo e($m->categories->pluck('name')->join(', ')); ?>

                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="color: var(--accent);">1 USD = <?php echo e(number_format($m->conversion_rate, 2)); ?> <?php echo e($m->currency); ?></strong>
                        </td>
                        <td>
                            <?php if($m->wallet_address): ?>
                                <code style="background: var(--bg-hover); padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #fff;">
                                    <?php echo e(Str::limit($m->wallet_address, 30)); ?>

                                </code>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 12px;">User entered</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="font-size: 12px;">Min: $<?php echo e(number_format($m->min_amount, 2)); ?></span><br>
                            <span style="font-size: 11px; color: var(--text-muted);">Max: $<?php echo e(number_format($m->max_amount, 2)); ?></span>
                        </td>
                        <td>
                            <form method="POST" action="<?php echo e(route('admin.payment-methods.toggle', $m)); ?>" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm <?php echo e($m->is_active ? 'btn-success' : 'btn-ghost'); ?>" style="padding: 2px 10px; font-size: 11px;">
                                    <?php echo e($m->is_active ? '✓ Enabled' : '✕ Disabled'); ?>

                                </button>
                            </form>
                        </td>
                        <td style="text-align: right;">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?php echo e(route('admin.payment-methods.edit', $m)); ?>" class="btn btn-ghost btn-sm">
                                    ✏️ Edit
                                </a>
                                <form method="POST" action="<?php echo e(route('admin.payment-methods.destroy', $m)); ?>" onsubmit="return confirm('Delete payment method <?php echo e($m->name); ?>?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            <?php echo e($methods->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/payment-methods/index.blade.php ENDPATH**/ ?>