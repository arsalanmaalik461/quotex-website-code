<?php
  $appDownload = \App\Services\AppDownloadService::settings();
  $popupEnabled = \App\Services\AppDownloadService::popupEnabled();
  $popupLogo = trim((string) ($appDownload['app_download.popup_logo_url'] ?? 'app/logo.png'));
?>

<?php if($popupEnabled && request()->routeIs('home')): ?>
  <div
    class="app-download-popup"
    data-app-download-popup
    data-storage-key="<?php echo e(\App\Services\AppDownloadService::POPUP_STORAGE_KEY); ?>"
    data-suppression-ms="<?php echo e(\App\Services\AppDownloadService::POPUP_SUPPRESSION_MS); ?>"
    hidden
  >
    <div class="app-download-popup__panel" role="dialog" aria-modal="false" aria-labelledby="app-download-popup-title">
      <button type="button" class="app-download-popup__close" data-app-download-close aria-label="Close app download popup">&times;</button>
      <div class="app-download-popup__content">
        <img class="app-download-popup__logo" src="<?php echo e(url($popupLogo ?: 'app/logo.png')); ?>" alt="" aria-hidden="true">
        <div>
          <h2 id="app-download-popup-title"><?php echo e($appDownload['app_download.popup_title'] ?? 'Download Our App'); ?></h2>
          <p><?php echo e($appDownload['app_download.popup_description'] ?? 'Get a faster trading experience with our Android app.'); ?></p>
        </div>
      </div>
      <a class="app-download-popup__button" href="<?php echo e(route('app.download')); ?>" data-app-download-popup-cta>
        <?php echo e($appDownload['app_download.button_text'] ?? 'Download App'); ?>

      </a>
    </div>
  </div>
<?php endif; ?>
<?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/partials/app-download-popup.blade.php ENDPATH**/ ?>