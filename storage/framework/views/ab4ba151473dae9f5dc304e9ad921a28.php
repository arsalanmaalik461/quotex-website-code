<?php $__env->startSection('title', 'Frequently Asked Questions — QUOTEX'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
  <div class="page-container text-center">
    <span class="section-tag">HELP & SUPPORT</span>
    <h1 class="page-title">Frequently Asked Questions</h1>
    <p class="page-subtitle">Everything you need to know about trading, accounts, deposits, and platform features.</p>
  </div>
</div>

<section class="section-container max-w-800 my-5">
  <div class="faq-accordion">
    <div class="faq-item open">
      <button class="faq-question">
        <span>How do I open an account with QUOTEX?</span>
        <svg class="faq-arrow" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-answer">
        <p>Registration takes less than a minute. Click on the "Sign Up" button, enter your email address and preferred password, choose your currency, and confirm that you are at least 18 years old. You will immediately be redirected to the trading terminal with a $10,000 demo balance.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-question">
        <span>What is the difference between Demo and Live accounts?</span>
        <svg class="faq-arrow" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-answer">
        <p>The demo account uses virtual funds ($10,000) for risk-free practice. The live account uses real money deposited by you, and profits earned on the live account can be withdrawn at any time to your bank card, e-wallet, or crypto wallet.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-question">
        <span>What is the minimum trade amount?</span>
        <svg class="faq-arrow" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-answer">
        <p>The minimum investment per individual deal is just $1 (or equivalent currency).</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-question">
        <span>How do I deposit funds into my account?</span>
        <svg class="faq-arrow" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-answer">
        <p>Log in and navigate to the Deposit section. Select your payment method (such as Visa/Mastercard, USDT TRC20, Bitcoin, Ethereum, or Binance Pay), enter the deposit amount (minimum $10), and complete the payment. Funds will be credited instantly.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-question">
        <span>How long do withdrawals take?</span>
        <svg class="faq-arrow" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-answer">
        <p>Withdrawal requests are processed rapidly, usually within 1 to 5 business hours. QUOTEX does not charge any withdrawal fees.</p>
      </div>
    </div>
  </div>
</section>

<script>
  document.querySelectorAll('.faq-question').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.faq-item').classList.toggle('open');
    });
  });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/pages/faq.blade.php ENDPATH**/ ?>