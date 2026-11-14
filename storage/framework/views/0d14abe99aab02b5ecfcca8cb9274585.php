<?php $__env->startSection('title', 'Admin Profile & Security'); ?>
<?php $__env->startSection('page-title', 'Profile & Account Settings'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width: 900px; margin: 0 auto;">

    <?php if($errors->any()): ?>
        <div class="alert alert-error mb-4">
            <ul style="padding-left: 20px; margin:0;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid-2">
        
        <div class="card">
            <div class="card-header">
                <div class="card-title">👤 Admin Details & Credentials</div>
            </div>

            <form method="POST" action="<?php echo e(route('admin.profile.update')); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="form-group">
                    <label class="form-label" for="name">Display Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        value="<?php echo e(old('name', $admin->name)); ?>" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Admin Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        value="<?php echo e(old('email', $admin->email)); ?>" 
                        required
                    >
                    <small style="color:var(--text-muted); display:block; margin-top:4px;">
                        This email is used to log in to the admin panel.
                    </small>
                </div>

                <hr style="border:none; border-top:1px solid var(--border); margin:24px 0 20px;">

                <div style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:14px;">
                    🔑 Change Password <span style="font-size:11px; font-weight:400; color:var(--text-secondary);">(Leave blank to keep unchanged)</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        class="form-control" 
                        placeholder="Enter current password if changing"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Minimum 6 characters"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirm New Password</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        class="form-control" 
                        placeholder="Re-type new password"
                    >
                </div>

                <div style="margin-top:24px;">
                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                        💾 Save Changes
                    </button>
                </div>
            </form>
        </div>

        
        <div>
            <div class="card mb-4">
                <div class="card-header">
                    <div class="card-title">🛡️ Security & Role Overview</div>
                </div>
                <div style="display:flex; flex-direction:column; gap:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid rgba(30,45,71,0.5);">
                        <span style="font-size:12px; color:var(--text-secondary);">Account Role</span>
                        <span class="badge-status blue">Super Administrator</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid rgba(30,45,71,0.5);">
                        <span style="font-size:12px; color:var(--text-secondary);">Account Status</span>
                        <span class="badge-status green">● Active</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid rgba(30,45,71,0.5);">
                        <span style="font-size:12px; color:var(--text-secondary);">Registered Since</span>
                        <span style="font-size:12px; color:var(--text-primary); font-weight:600;">
                            <?php echo e($admin->created_at ? $admin->created_at->format('M d, Y') : 'N/A'); ?>

                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:12px; color:var(--text-secondary);">Dedicated Login URL</span>
                        <a href="<?php echo e(route('admin.login')); ?>" target="_blank" style="font-size:12px; color:var(--accent); text-decoration:none;">
                            /admin/login ↗
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">💡 Security Recommendations</div>
                </div>
                <ul style="font-size:12.5px; color:var(--text-secondary); line-height:1.7; padding-left:18px;">
                    <li>Use a strong password with letters, numbers, and symbols.</li>
                    <li>Never share your admin login credentials with unverified persons.</li>
                    <li>Always log out when accessing the admin panel on public or shared devices.</li>
                    <li>All critical admin actions are recorded in the <a href="<?php echo e(route('admin.logs')); ?>" style="color:var(--accent);">Admin Activity Logs</a>.</li>
                </ul>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/profile.blade.php ENDPATH**/ ?>