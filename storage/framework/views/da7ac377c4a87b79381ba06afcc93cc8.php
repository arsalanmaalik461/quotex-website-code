<?php $__env->startSection('title', 'Edit Payment Category: ' . $category->name); ?>
<?php $__env->startSection('page-title', 'Edit Payment Category'); ?>

<?php $__env->startSection('content'); ?>

<div class="card" style="max-width:980px;margin:0 auto;">
    <div class="card-header">
        <div class="card-title">Edit: <?php echo e($category->name); ?></div>
    </div>

    <form method="POST" action="<?php echo e(route('admin.payment-categories.update', $category)); ?>" enctype="multipart/form-data">
        <?php echo $__env->make('admin.payment-categories._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </form>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/payment-categories/edit.blade.php ENDPATH**/ ?>