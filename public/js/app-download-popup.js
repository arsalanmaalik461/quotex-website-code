(function () {
  if (window.__appDownloadPopupInitialized) {
    return;
  }
  window.__appDownloadPopupInitialized = true;

  document.addEventListener('DOMContentLoaded', function () {
    var popup = document.querySelector('[data-app-download-popup]');
    if (!popup) {
      return;
    }

    var storageKey = popup.getAttribute('data-storage-key') || 'app_download_popup_dismissed_at';
    var suppressionMs = parseInt(popup.getAttribute('data-suppression-ms') || '86400000', 10);
    var now = Date.now();
    var shouldShow = true;

    try {
      var dismissedAt = parseInt(window.localStorage.getItem(storageKey) || '0', 10);
      shouldShow = !dismissedAt || (now - dismissedAt) >= suppressionMs;
    } catch (error) {
      shouldShow = true;
    }

    if (!shouldShow) {
      return;
    }

    popup.hidden = false;

    var closeButton = popup.querySelector('[data-app-download-close]');
    if (closeButton) {
      closeButton.addEventListener('click', function () {
        popup.hidden = true;
      });
    }

    var cta = popup.querySelector('[data-app-download-popup-cta]');
    if (cta) {
      cta.addEventListener('click', function () {
        try {
          window.localStorage.setItem(storageKey, String(Date.now()));
        } catch (error) {
          // Download must continue even if browser storage is blocked.
        }
      });
    }
  });
})();
