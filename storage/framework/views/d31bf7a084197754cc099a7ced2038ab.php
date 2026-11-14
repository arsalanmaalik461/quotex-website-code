<?php $__env->startSection('title', $asset ? 'Edit ' . $asset->symbol : 'Add Trading Pair'); ?>
<?php $__env->startSection('page-title', $asset ? '✏️ Edit ' . $asset->symbol : '➕ Add Trading Pair'); ?>

<?php $__env->startSection('content'); ?>

<div style="margin-bottom:16px">
    <a href="<?php echo e(route('admin.assets.index')); ?>" class="btn btn-ghost btn-sm">← Back to Assets</a>
</div>

<div class="card" style="max-width:700px">
    <form method="POST" action="<?php echo e($asset ? route('admin.assets.update', $asset) : route('admin.assets.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($asset): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Symbol *</label>
                <input type="text" name="symbol" class="form-control" value="<?php echo e(old('symbol', $asset?->symbol)); ?>"
                       placeholder="e.g. AZA/USDT" required <?php echo e($asset ? 'readonly' : ''); ?>>
                <?php $__errorArgs = ['symbol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="color:var(--red);font-size:12px;margin-top:4px"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $asset?->name)); ?>"
                       placeholder="e.g. Aza Coin / USDT" required>
            </div>
            <div class="form-group">
                <label class="form-label">Type *</label>
                <select name="type" class="form-control" required>
                    <?php $__currentLoopData = ['forex','crypto','commodity','index','otc','signal']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t); ?>" <?php echo e(old('type', $asset?->type) === $t ? 'selected' : ''); ?>><?php echo e(ucfirst($t)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Category (optional)</label>
                <input type="text" name="category" class="form-control" value="<?php echo e(old('category', $asset?->category)); ?>" placeholder="e.g. Majors, DeFi, Metals">
            </div>
            <div class="form-group">
                <label class="form-label">Payout % (e.g. 85) *</label>
                <input type="number" name="payout_percent" class="form-control" value="<?php echo e(old('payout_percent', $asset?->payout_percent ?? 85)); ?>" min="1" max="99" required>
            </div>
            <div class="form-group">
                <label class="form-label">Starting / Base Market Price (optional)</label>
                <input type="number" step="0.00001" name="base_price_override" class="form-control" value="<?php echo e(old('base_price_override', $asset?->base_price_override)); ?>" placeholder="e.g. 1.08500, 64250.00, 152.30">
                <span class="text-muted" style="font-size:11px">Leave blank for auto-detected realistic price.</span>
            </div>
            <div class="form-group">
                <label class="form-label">Flag / Emoji (optional)</label>
                <input type="text" name="flag" class="form-control" value="<?php echo e(old('flag', $asset?->flag)); ?>" placeholder="e.g. 🇺🇸 or 🟡">
            </div>
            <div class="form-group">
                <label class="form-label">Trade Outcome / Control Mode *</label>
                <select name="control_mode" class="form-control" required>
                    <option value="auto" <?php echo e(old('control_mode', $asset?->control_mode ?? 'auto') === 'auto' ? 'selected' : ''); ?>>
                        ⟳ Auto (Market Follow - User Sahi Prediction Kare To Win)
                    </option>
                    <option value="force_win" <?php echo e(old('control_mode', $asset?->control_mode) === 'force_win' ? 'selected' : ''); ?>>
                        🏆 Always Win (Force Win - Har User Win Kare)
                    </option>
                    <option value="force_loss" <?php echo e(old('control_mode', $asset?->control_mode) === 'force_loss' ? 'selected' : ''); ?>>
                        📉 Always Loss (Force Loss - Har User Loss Kare)
                    </option>
                    <option value="pump" <?php echo e(old('control_mode', $asset?->control_mode) === 'pump' ? 'selected' : ''); ?>>
                        🚀 Pump Mode (UP Trades Win, DOWN Trades Lose)
                    </option>
                    <option value="crash" <?php echo e(old('control_mode', $asset?->control_mode) === 'crash' ? 'selected' : ''); ?>>
                        💥 Crash Mode (DOWN Trades Win, UP Trades Lose)
                    </option>
                </select>
                <span class="text-muted" style="font-size:11px">Auto mode mein user market ki real direction ke mutabiq win/loss karega (UP par price barhay to win, kam ho to loss).</span>
            </div>
        </div>

        <div class="flex gap-3 mt-4" style="padding:16px;background:var(--bg-hover);border-radius:8px;">
            <label class="flex items-center gap-2" style="cursor:pointer;font-size:13px">
                <input type="checkbox" name="is_otc" value="1" <?php echo e(old('is_otc', $asset?->is_otc) ? 'checked' : ''); ?>>
                OTC Pair
            </label>
            <label class="flex items-center gap-2" style="cursor:pointer;font-size:13px">
                <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $asset?->is_active ?? true) ? 'checked' : ''); ?>>
                Active (visible to users)
            </label>
        </div>

        <div class="flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <?php echo e($asset ? 'Update Asset' : 'Create Asset'); ?>

            </button>
            <?php if($asset): ?>
            <a href="<?php echo e(route('admin.assets.control', $asset)); ?>" class="btn btn-warning">⚙ OTC Control</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/assets/create.blade.php ENDPATH**/ ?>