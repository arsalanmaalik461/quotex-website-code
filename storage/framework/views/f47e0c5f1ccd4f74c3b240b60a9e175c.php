<?php $__env->startSection('title', 'Email Settings'); ?>
<?php $__env->startSection('page-title', 'Email Settings'); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('admin.email-settings.update')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <div class="grid-2">
        <div class="card">
            <div class="card-header">
                <div class="card-title">SMTP</div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Host</label>
                    <input class="form-control" name="smtp_host" value="<?php echo e(old('smtp_host', $settings['email.smtp_host'] ?? '')); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Port</label>
                    <input class="form-control" type="number" name="smtp_port" min="1" max="65535" value="<?php echo e(old('smtp_port', $settings['email.smtp_port'] ?? '587')); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Username</label>
                <input class="form-control" name="smtp_username" value="<?php echo e(old('smtp_username', $settings['email.smtp_username'] ?? '')); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="smtp_password" placeholder="Leave blank to keep existing password" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">Encryption</label>
                <select class="form-control" name="smtp_encryption">
                    <option value="" <?php echo e(old('smtp_encryption', $settings['email.smtp_encryption'] ?? '') === '' ? 'selected' : ''); ?>>None</option>
                    <option value="tls" <?php echo e(old('smtp_encryption', $settings['email.smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''); ?>>TLS</option>
                    <option value="ssl" <?php echo e(old('smtp_encryption', $settings['email.smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''); ?>>SSL</option>
                </select>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Branding</div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">From Email</label>
                    <input class="form-control" type="email" name="from_address" value="<?php echo e(old('from_address', $settings['email.from_address'] ?? '')); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">From Name</label>
                    <input class="form-control" name="from_name" value="<?php echo e(old('from_name', $settings['email.from_name'] ?? '')); ?>" required>
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Site Name</label>
                    <input class="form-control" name="site_name" value="<?php echo e(old('site_name', $settings['email.site_name'] ?? '')); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Support Email</label>
                    <input class="form-control" type="email" name="support_email" value="<?php echo e(old('support_email', $settings['email.support_email'] ?? '')); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Logo URL</label>
                <input class="form-control" name="logo_url" value="<?php echo e(old('logo_url', $settings['email.logo_url'] ?? '')); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Upload Logo</label>
                <input class="form-control" type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg">
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Primary Color</label>
                    <input class="form-control" type="color" name="primary_color" value="<?php echo e(old('primary_color', $settings['email.primary_color'] ?? '#026FD3')); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Secondary Color</label>
                    <input class="form-control" type="color" name="secondary_color" value="<?php echo e(old('secondary_color', $settings['email.secondary_color'] ?? '#16a34a')); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Header Color</label>
                    <input class="form-control" type="color" name="header_color" value="<?php echo e(old('header_color', $settings['email.header_color'] ?? '#0f172a')); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Background Color</label>
                    <input class="form-control" type="color" name="background_color" value="<?php echo e(old('background_color', $settings['email.background_color'] ?? '#f4f7fb')); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Text Color</label>
                    <input class="form-control" type="color" name="text_color" value="<?php echo e(old('text_color', $settings['email.text_color'] ?? '#1f2937')); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Button Color</label>
                    <input class="form-control" type="color" name="button_color" value="<?php echo e(old('button_color', $settings['email.button_color'] ?? '#026FD3')); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Button Text Color</label>
                    <input class="form-control" type="color" name="button_text_color" value="<?php echo e(old('button_text_color', $settings['email.button_text_color'] ?? '#ffffff')); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <div class="card-title">App Download</div>
        </div>
        <div class="form-group">
            <label class="form-label" style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="app_download_popup_enabled" value="1" <?php echo e(in_array(old('app_download_popup_enabled', $settings['app_download.popup_enabled'] ?? '1'), ['1', 1, true], true) ? 'checked' : ''); ?>>
                Show app download popup on home page
            </label>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Popup Title</label>
                <input class="form-control" name="app_download_popup_title" value="<?php echo e(old('app_download_popup_title', $settings['app_download.popup_title'] ?? '')); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Download Button Text</label>
                <input class="form-control" name="app_download_button_text" value="<?php echo e(old('app_download_button_text', $settings['app_download.button_text'] ?? '')); ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Popup Description</label>
            <input class="form-control" name="app_download_popup_description" value="<?php echo e(old('app_download_popup_description', $settings['app_download.popup_description'] ?? '')); ?>" required>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">APK Path</label>
                <input class="form-control" name="app_download_apk_path" value="<?php echo e(old('app_download_apk_path', $settings['app_download.apk_path'] ?? 'app/qoutex.apk')); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Popup Logo URL</label>
                <input class="form-control" name="app_download_popup_logo_url" value="<?php echo e(old('app_download_popup_logo_url', $settings['app_download.popup_logo_url'] ?? 'app/logo.png')); ?>">
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <div class="card-title">Withdrawal Email Templates</div>
        </div>
        <div class="form-group">
            <label class="form-label" style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="withdrawal_approved_enabled" value="1" <?php echo e(in_array(old('withdrawal_approved_enabled', $settings['email.withdrawal_approved_enabled'] ?? '1'), ['1', 1, true], true) ? 'checked' : ''); ?>>
                Send Withdrawal Approval Emails
            </label>
        </div>
        <div class="form-group">
            <label class="form-label">Withdrawal Approval Email Subject</label>
            <input class="form-control" name="withdrawal_approved_subject" value="<?php echo e(old('withdrawal_approved_subject', $settings['email.withdrawal_approved_subject'] ?? '')); ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Withdrawal Approval Email Heading</label>
            <input class="form-control" name="withdrawal_approved_header" value="<?php echo e(old('withdrawal_approved_header', $settings['email.withdrawal_approved_header'] ?? '')); ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Withdrawal Approval Email Body</label>
            <textarea class="form-control" name="withdrawal_approved_body" rows="8" required><?php echo e(old('withdrawal_approved_body', $settings['email.withdrawal_approved_body'] ?? '')); ?></textarea>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Approval Email Button Text</label>
                <input class="form-control" name="withdrawal_approved_button_text" value="<?php echo e(old('withdrawal_approved_button_text', $settings['email.withdrawal_approved_button_text'] ?? '')); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Approval Email Button URL</label>
                <input class="form-control" name="withdrawal_approved_button_url" value="<?php echo e(old('withdrawal_approved_button_url', $settings['email.withdrawal_approved_button_url'] ?? '')); ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Withdrawal Approval Email Footer</label>
            <textarea class="form-control" name="withdrawal_approved_footer" rows="3" required><?php echo e(old('withdrawal_approved_footer', $settings['email.withdrawal_approved_footer'] ?? '')); ?></textarea>
        </div>
        <hr style="border:0;border-top:1px solid var(--border);margin:22px 0;">
        <div class="form-group">
            <label class="form-label">Withdrawal Rejection Email Subject</label>
            <input class="form-control" name="withdrawal_rejected_subject" value="<?php echo e(old('withdrawal_rejected_subject', $settings['email.withdrawal_rejected_subject'] ?? '')); ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Withdrawal Rejection Email Heading</label>
            <input class="form-control" name="withdrawal_rejected_header" value="<?php echo e(old('withdrawal_rejected_header', $settings['email.withdrawal_rejected_header'] ?? '')); ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Default Withdrawal Rejection Reason</label>
            <textarea class="form-control" name="withdrawal_rejected_default_reason" rows="4" required><?php echo e(old('withdrawal_rejected_default_reason', $settings['email.withdrawal_rejected_default_reason'] ?? '')); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Withdrawal Rejection Email Body</label>
            <textarea class="form-control" name="withdrawal_rejected_body" rows="10" required><?php echo e(old('withdrawal_rejected_body', $settings['email.withdrawal_rejected_body'] ?? '')); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Withdrawal Rejection Email Footer</label>
            <textarea class="form-control" name="withdrawal_rejected_footer" rows="3" required><?php echo e(old('withdrawal_rejected_footer', $settings['email.withdrawal_rejected_footer'] ?? '')); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Placeholders</label>
            <div class="flex gap-2" style="flex-wrap:wrap;">
                <?php $__currentLoopData = $placeholders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $placeholder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <code style="background:var(--bg-hover);border:1px solid var(--border);border-radius:6px;padding:5px 8px;color:var(--text-secondary);"><?php echo e($placeholder); ?></code>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Save Email Settings</button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/admin/email-settings/edit.blade.php ENDPATH**/ ?>