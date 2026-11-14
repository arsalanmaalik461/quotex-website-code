<?php echo csrf_field(); ?>

<?php if($category->exists): ?>
    <?php echo method_field('PUT'); ?>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
    <div class="form-group">
        <label class="form-label">Category Name *</label>
        <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $category->name)); ?>" required>
    </div>
    <div class="form-group">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="<?php echo e(old('slug', $category->slug)); ?>" placeholder="auto-generated if empty">
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
    <div class="form-group">
        <label class="form-label">Icon</label>
        <input type="text" name="icon" class="form-control" value="<?php echo e(old('icon', $category->icon)); ?>" placeholder="fire, wallet, card, coin, or emoji">
    </div>
    <div class="form-group">
        <label class="form-label">Short Label</label>
        <input type="text" name="short_label" class="form-control" value="<?php echo e(old('short_label', $category->short_label)); ?>" placeholder="Popular in your region">
    </div>
    <div class="form-group">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="<?php echo e(old('sort_order', $category->sort_order ?? 0)); ?>">
    </div>
</div>

<div class="form-group" style="margin-bottom:16px;">
    <label class="form-label">Category Image</label>
    <?php if($category->image_url): ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
            <img src="<?php echo e($category->image_url); ?>" alt="<?php echo e($category->name); ?>" style="width:42px;height:42px;border-radius:6px;object-fit:contain;background:#fff;padding:3px;">
            <label style="font-size:12px;color:var(--text-muted);">
                <input type="checkbox" name="remove_image" value="1">
                Remove current image
            </label>
        </div>
    <?php endif; ?>
    <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
    <span style="font-size:11px;color:var(--text-muted);">Optional JPG, PNG, or WebP. SVG is not accepted.</span>
</div>

<div class="form-group" style="margin-bottom:16px;">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3"><?php echo e(old('description', $category->description)); ?></textarea>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:22px;">
    <label class="flex items-center gap-2" style="cursor:pointer;">
        <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $category->is_active ?? true) ? 'checked' : ''); ?>>
        <span style="font-size:13px;font-weight:600;color:#fff;">Active</span>
    </label>
    <label class="flex items-center gap-2" style="cursor:pointer;">
        <input type="checkbox" name="show_on_deposit" value="1" <?php echo e(old('show_on_deposit', $category->show_on_deposit ?? true) ? 'checked' : ''); ?>>
        <span style="font-size:13px;font-weight:600;color:#fff;">Show on Deposit</span>
    </label>
    <div class="form-group">
        <label class="form-label">Country / Region</label>
        <input type="text" name="country" class="form-control" value="<?php echo e(old('country', $category->country)); ?>" placeholder="optional">
    </div>
</div>

<div class="card" style="background:var(--bg-hover);margin-bottom:22px;">
    <div class="card-header">
        <div>
            <div class="card-title">Assigned Payment Methods</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">A method can be assigned to more than one category. Use order for this category only.</div>
        </div>
    </div>

    <?php if($methods->isEmpty()): ?>
        <div style="padding:18px;color:var(--text-muted);">No payment methods exist yet.</div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:10px;">
            <?php $__currentLoopData = $methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isChecked = old('methods')
                        ? in_array($method->id, old('methods', []))
                        : $assigned->has($method->id);
                    $sortValue = old("method_sort.{$method->id}", $assigned->get($method->id)?->pivot?->sort_order ?? $method->sort_order);
                ?>
                <label style="display:grid;grid-template-columns:auto 1fr 74px;gap:10px;align-items:center;border:1px solid var(--border);border-radius:8px;padding:10px;background:var(--bg-card);cursor:pointer;">
                    <input type="checkbox" name="methods[]" value="<?php echo e($method->id); ?>" <?php echo e($isChecked ? 'checked' : ''); ?>>
                    <span>
                        <strong style="display:block;color:#fff;font-size:13px;"><?php echo e($method->name); ?></strong>
                        <span style="font-size:11px;color:var(--text-muted);"><?php echo e(ucfirst($method->type)); ?> | <?php echo e($method->is_active ? 'Active' : 'Disabled'); ?></span>
                    </span>
                    <input type="number" name="method_sort[<?php echo e($method->id); ?>]" class="form-control" value="<?php echo e($sortValue); ?>" title="Order" style="height:32px;padding:4px 8px;">
                </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<div class="flex justify-end gap-3">
    <a href="<?php echo e(route('admin.payment-categories.index')); ?>" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-success"><?php echo e($category->exists ? 'Save Category' : 'Create Category'); ?></button>
</div>
<?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/payment-categories/_form.blade.php ENDPATH**/ ?>