<?php $__env->startSection('title', 'Payment Categories'); ?>
<?php $__env->startSection('page-title', 'Payment Categories'); ?>

<?php $__env->startSection('content'); ?>

<div style="margin-bottom:16px;display:flex;gap:10px;justify-content:space-between;align-items:center;flex-wrap:wrap;">
    <a href="<?php echo e(route('admin.payment-methods.index')); ?>" class="btn btn-ghost btn-sm">Back to Payment Methods</a>
    <a href="<?php echo e(route('admin.payment-categories.create')); ?>" class="btn btn-success">+ Add Category</a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Deposit Payment Categories</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Categories organize existing payment methods on the deposit page.</div>
        </div>
    </div>

    <?php if($categories->isEmpty()): ?>
        <div style="padding:48px;text-align:center;color:var(--text-muted);">No categories created yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Slug</th>
                        <th>Methods</th>
                        <th>Deposit</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <strong style="color:#fff;"><?php echo e($category->icon ?: 'card'); ?> <?php echo e($category->name); ?></strong><br>
                                <span style="font-size:11px;color:var(--text-muted);">Sort: #<?php echo e($category->sort_order); ?></span>
                            </td>
                            <td><code style="font-size:12px;"><?php echo e($category->slug); ?></code></td>
                            <td><?php echo e($category->payment_methods_count); ?></td>
                            <td><?php echo e($category->show_on_deposit ? 'Shown' : 'Hidden'); ?></td>
                            <td>
                                <form method="POST" action="<?php echo e(route('admin.payment-categories.toggle', $category)); ?>" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <button class="btn btn-sm <?php echo e($category->is_active ? 'btn-success' : 'btn-ghost'); ?>" style="padding:2px 10px;font-size:11px;">
                                        <?php echo e($category->is_active ? 'Enabled' : 'Disabled'); ?>

                                    </button>
                                </form>
                            </td>
                            <td style="text-align:right;">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo e(route('admin.payment-categories.edit', $category)); ?>" class="btn btn-ghost btn-sm">Edit</a>
                                    <form method="POST" action="<?php echo e(route('admin.payment-categories.destroy', $category)); ?>" onsubmit="return confirm('Delete category <?php echo e($category->name); ?>? Payment methods will stay safe.')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;"><?php echo e($categories->links()); ?></div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/admin/payment-categories/index.blade.php ENDPATH**/ ?>