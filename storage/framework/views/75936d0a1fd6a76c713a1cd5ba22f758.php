<div id="qx-global-preloader" class="qx-preloader">
  <div class="qx-preloader-content">
    <div class="qx-preloader-logo-wrap">
      <img src="/profile/images/logo-dark.svg" alt="Quotex" height="30" class="qx-preloader-logo">
    </div>
    <div class="qx-preloader-spinner-bar">
      <div class="qx-preloader-spinner-fill"></div>
    </div>
  </div>
</div>

<style>
  .qx-preloader {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    background: #0e131d;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999999;
    opacity: 1;
    visibility: visible;
    transition: opacity 0.35s ease, visibility 0.35s ease;
  }
  .qx-preloader.qx-loaded {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
  }
  .qx-preloader-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
  }
  .qx-preloader-logo-wrap {
    animation: qxLogoPulse 1.6s ease-in-out infinite;
  }
  .qx-preloader-logo {
    height: 30px;
    width: auto;
    display: block;
  }
  .qx-preloader-spinner-bar {
    width: 130px;
    height: 3px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 3px;
    overflow: hidden;
    position: relative;
  }
  .qx-preloader-spinner-fill {
    width: 45%;
    height: 100%;
    background: linear-gradient(90deg, #00c853, #00e676);
    border-radius: 3px;
    position: absolute;
    animation: qxBarSlide 1.2s infinite ease-in-out;
  }
  @keyframes qxLogoPulse {
    0%, 100% { transform: scale(1); opacity: 0.92; }
    50% { transform: scale(1.03); opacity: 1; filter: drop-shadow(0 0 10px rgba(0, 200, 83, 0.25)); }
  }
  @keyframes qxBarSlide {
    0% { left: -45%; }
    50% { left: 35%; width: 55%; }
    100% { left: 100%; width: 45%; }
  }
</style>

<script>
  (function() {
    function hidePreloader() {
      var p = document.getElementById('qx-global-preloader');
      if (p && !p.classList.contains('qx-loaded')) {
        p.classList.add('qx-loaded');
        setTimeout(function() {
          if (p && p.parentNode) { p.parentNode.removeChild(p); }
        }, 400);
      }
    }
    if (document.readyState === 'complete') {
      setTimeout(hidePreloader, 150);
    } else {
      window.addEventListener('load', function() { setTimeout(hidePreloader, 150); });
      setTimeout(hidePreloader, 900); // Safety fallback
    }
  })();
</script>
<?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/layouts/preloader.blade.php ENDPATH**/ ?>