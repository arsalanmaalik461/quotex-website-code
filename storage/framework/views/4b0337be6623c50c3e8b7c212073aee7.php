<?php $__env->startSection('title', 'Manage Users'); ?>
<?php $__env->startSection('page-title', '👥 User Management'); ?>

<?php $__env->startSection('content'); ?>


<div class="card mb-4">
    <form method="GET" class="flex gap-3 items-center" style="flex-wrap:wrap;">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search name, email, phone..." class="form-control" style="max-width:280px">
        <select name="status" class="form-control" style="max-width:150px">
            <option value="">All Status</option>
            <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
            <option value="blocked" <?php echo e(request('status') === 'blocked' ? 'selected' : ''); ?>>Blocked</option>
        </select>
        <select name="account_type" class="form-control" style="max-width:150px">
            <option value="">All Accounts</option>
            <option value="demo" <?php echo e(request('account_type') === 'demo' ? 'selected' : ''); ?>>Demo</option>
            <option value="live" <?php echo e(request('account_type') === 'live' ? 'selected' : ''); ?>>Live</option>
        </select>
        <button type="submit" class="btn btn-primary">🔍 Search</button>
        <?php if(request()->hasAny(['search','status','account_type'])): ?>
            <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value"><?php echo e($users->total()); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Page</div>
        <div class="stat-value"><?php echo e($users->currentPage()); ?>/<?php echo e($users->lastPage()); ?></div>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <div class="card-title">Users (<?php echo e($users->total()); ?>)</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Balance (Live)</th>
                    <th>Account</th>
                    <th>Trades</th>
                    <th>Referrals</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="color:var(--text-muted)"><?php echo e($user->id); ?></td>
                <td>
                    <a href="<?php echo e(route('admin.users.show', $user)); ?>" style="color:var(--text-primary);text-decoration:none;font-weight:500">
                        <?php echo e($user->name); ?>

                    </a>
                    <?php if($user->country): ?>
                        <div style="font-size:11px;color:var(--text-muted)"><?php echo e($user->country); ?></div>
                    <?php endif; ?>
                </td>
                <td><?php echo e($user->email); ?></td>
                <td style="color:<?php echo e($user->live_balance > 0 ? 'var(--green)' : 'inherit'); ?>">
                    $<?php echo e(number_format($user->live_balance, 2)); ?>

                    <div style="font-size:11px;color:var(--text-muted)">Demo: $<?php echo e(number_format($user->demo_balance, 2)); ?></div>
                </td>
                <td>
                    <span class="badge-status <?php echo e($user->account_type === 'live' ? 'green' : 'gray'); ?>">
                        <?php echo e(strtoupper($user->account_type)); ?>

                    </span>
                </td>
                <td><?php echo e($user->trades_count); ?></td>
                <td><?php echo e($user->referrals_count); ?></td>
                <td>
                    <?php if($user->is_blocked): ?>
                        <span class="badge-status red">🚫 Blocked</span>
                    <?php else: ?>
                        <span class="badge-status green">✓ Active</span>
                    <?php endif; ?>
                </td>
                <td><?php echo e($user->created_at->format('M d, Y')); ?></td>
                <td>
                    <div class="flex gap-2" style="flex-wrap:nowrap">
                        <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="btn btn-ghost btn-sm">View</a>
                        <form method="POST" action="<?php echo e(route('admin.users.login-as', $user)); ?>" style="display:inline">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-warning btn-sm" title="Login as user">🔑</button>
                        </form>
                        <?php if($user->is_blocked): ?>
                        <form method="POST" action="<?php echo e(route('admin.users.unblock', $user)); ?>" style="display:inline">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-success btn-sm">Unblock</button>
                        </form>
                        <?php else: ?>
                        <form method="POST" action="<?php echo e(route('admin.users.block', $user)); ?>" style="display:inline">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-danger btn-sm">Block</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="10" style="text-align:center;color:var(--text-muted);padding:32px">No users found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div style="margin-top:16px">
        <?php echo e($users->links('pagination::simple-default')); ?>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/users/index.blade.php ENDPATH**/ ?>