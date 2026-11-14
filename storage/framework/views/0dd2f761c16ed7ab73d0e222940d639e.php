<?php $__env->startSection('title', 'Withdrawal | Quotex: An innovative platform for online investment'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Exact Quotex Withdrawal Styles (Matching Screenshot & DOM) */
.withdrawal-page-container {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 24px 20px 60px;
  box-sizing: border-box;
  color: #c9d1d9;
}

.page__sections-row {
  display: flex;
  gap: 36px;
  width: 100%;
  box-sizing: border-box;
}

.page__sections-column {
  display: flex;
  flex-direction: column;
  gap: 28px;
}

.page__sections-column.col-left {
  flex: 0 0 56%;
  max-width: 56%;
}

.page__sections-column.col-right {
  flex: 0 0 44%;
  max-width: 44%;
}

@media (max-width: 1100px) {
  .page__sections-row {
    flex-direction: column;
    gap: 24px;
  }
  .page__sections-column.col-left,
  .page__sections-column.col-right {
    flex: 1 1 100%;
    max-width: 100%;
  }
}

/* Section Header Titles */
.content-section__head {
  font-size: 15px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.content-section__head-action {
  font-size: 13px;
  color: #026fd3;
  text-decoration: none;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: color 0.15s;
}

.content-section__head-action:hover {
  color: #2b99ff;
  text-decoration: underline;
}

/* Account Balances (Left-Top 1/3) */
.top-form-split {
  display: flex;
  gap: 24px;
}

@media (max-width: 768px) {
  .top-form-split {
    flex-direction: column;
  }
}

.content-section.grow-1of3 {
  flex: 0 0 32%;
  max-width: 32%;
}

.content-section.grow-2of3 {
  flex: 0 0 calc(68% - 24px);
  max-width: calc(68% - 24px);
}

@media (max-width: 768px) {
  .content-section.grow-1of3,
  .content-section.grow-2of3 {
    flex: 1 1 100%;
    max-width: 100%;
  }
}

.balance-list {
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.balance-list__label {
  font-size: 12px;
  color: #8b96a8;
  margin: 0 0 4px;
  font-weight: 500;
}

.balance-list__value {
  font-size: 22px;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
  font-variant-numeric: tabular-nums;
}

/* Quotex Cabinet Floating Inputs */
.form-content {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.form__row {
  display: flex;
  gap: 14px;
  width: 100%;
}

.form__control {
  flex: 1;
  position: relative;
}

.form__control.grow-1of2 {
  flex: 0 0 calc(50% - 7px);
}

.input-control-cabinet {
  display: flex;
  flex-direction: column;
  background: #181d28;
  border: 1px solid #283348;
  border-radius: 6px;
  padding: 8px 12px 6px;
  position: relative;
  transition: border-color 0.15s, box-shadow 0.15s;
  cursor: text;
  box-sizing: border-box;
  min-height: 52px;
}

.input-control-cabinet:focus-within,
.input-control-cabinet.active {
  border-color: #026fd3;
  box-shadow: 0 0 0 1px #026fd3;
}

.input-control-cabinet__label {
  font-size: 11px;
  color: #78869c;
  font-weight: 600;
  line-height: 1.1;
  margin-bottom: 2px;
  user-select: none;
}

.input-control-cabinet__input-wrap {
  display: flex;
  align-items: center;
  width: 100%;
}

.input-control-cabinet__input {
  width: 100%;
  background: transparent;
  border: none;
  outline: none;
  color: #ffffff;
  font-size: 14px;
  font-weight: 700;
  font-family: inherit;
  padding: 0;
}

.input-control-cabinet__input::placeholder {
  color: #556277;
  font-weight: 400;
}

.input-control-cabinet__addon {
  font-size: 13px;
  font-weight: 700;
  color: #78869c;
  margin-left: 8px;
  user-select: none;
}

/* Custom Select Cabinet */
.input-control-cabinet--select {
  cursor: pointer;
}

.input-control-cabinet__select-el {
  width: 100%;
  background: transparent;
  border: none;
  outline: none;
  color: #ffffff;
  font-size: 13.5px;
  font-weight: 600;
  font-family: inherit;
  padding: 0;
  cursor: pointer;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

.input-control-cabinet__select-el option {
  background: #181d28;
  color: #fff;
  padding: 10px;
}

.select-arrow-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-20%);
  pointer-events: none;
  color: #78869c;
}

hr.form-divider {
  border: none;
  border-top: 1px solid #232c3e;
  margin: 6px 0;
}

/* Confirm Submit Button */
.withdrawal-page__submit {
  margin-top: 6px;
}

.button--primary.form-submit {
  background: #026fd3;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  padding: 12px 28px;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.15s;
}

.button--primary.form-submit:hover {
  background: #1d82e2;
}

/* Latest Requests Flex Table */
.content-section.grow-full {
  width: 100%;
  margin-top: 8px;
}

.withdrawal-table.flex-table {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.flex-table .row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #161b26;
  border: 1px solid #232c3e;
  border-radius: 6px;
  padding: 14px 18px;
  gap: 16px;
  transition: border-color 0.15s;
}

.flex-table .row:hover {
  border-color: #313d54;
}

.flex-table .col-meta {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 120px;
}

.col-meta .req-id {
  font-size: 13.5px;
  font-weight: 700;
  color: #ffffff;
}

.col-meta .req-date {
  font-size: 11.5px;
  color: #78869c;
}

.flex-table .col-status {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 700;
}

.status-pill.success {
  color: #00d17a;
}

.status-pill.failed {
  color: #ff5252;
}

.status-pill.pending {
  color: #ff9800;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  display: inline-block;
}

.status-pill.success .status-dot {
  background: #00d17a;
  box-shadow: 0 0 6px rgba(0, 209, 122, 0.6);
}

.status-pill.failed .status-dot {
  background: #ff5252;
  box-shadow: 0 0 6px rgba(255, 82, 82, 0.6);
}

.status-pill.pending .status-dot {
  background: #ff9800;
  box-shadow: 0 0 6px rgba(255, 152, 0, 0.6);
}

.status-desc {
  font-size: 11px;
  color: #6b778c;
  line-height: 1.3;
}

.flex-table .col-amount {
  text-align: right;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 110px;
}

.col-amount .req-method {
  font-size: 12px;
  color: #78869c;
}

.col-amount .req-val {
  font-size: 14px;
  font-weight: 800;
  color: #00d17a;
  font-variant-numeric: tabular-nums;
}

/* FAQ 2-Column Section (Right Column) */
.faq--columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

@media (max-width: 768px) {
  .faq--columns {
    grid-template-columns: 1fr;
  }
}

.faq__column {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.faq__item {
  background: #161b26;
  border: 1px solid #232c3e;
  border-radius: 6px;
  overflow: hidden;
  transition: border-color 0.15s;
}

.faq__item.open {
  border-color: #313d54;
}

.faq__question {
  padding: 12px 14px;
  font-size: 12.5px;
  font-weight: 600;
  color: #cbd5e1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  user-select: none;
  transition: color 0.15s;
}

.faq__question:hover {
  color: #ffffff;
}

.faq__chevron {
  font-size: 11px;
  color: #78869c;
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.faq__item.open .faq__chevron {
  transform: rotate(180deg);
  color: #026fd3;
}

.faq__answer {
  padding: 0 14px 14px;
  font-size: 12px;
  color: #8b96a8;
  line-height: 1.5;
  display: none;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  padding-top: 10px;
}

.faq__item.open .faq__answer {
  display: block;
}

.faq__answer p {
  margin: 0 0 8px;
}
.faq__answer p:last-child {
  margin-bottom: 0;
}
.faq__answer ul {
  margin: 6px 0 8px 18px;
  padding: 0;
}
.faq__answer li {
  margin-bottom: 4px;
}

/* Page Footer / Advantages & Trust Badges */
.page-footer {
  margin-top: 48px;
  padding-top: 24px;
  border-top: 1px solid #232c3e;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.page-footer__advantages {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}

.page-footer__advantages li {
  font-size: 12px;
  color: #8b96a8;
  display: flex;
  align-items: center;
  gap: 8px;
}

.page-footer__advantages li::before {
  content: '✓';
  color: #00d17a;
  font-weight: 800;
  font-size: 13px;
}

.page-footer__advantages li span {
  color: #00d17a;
  font-weight: 700;
  margin-left: 4px;
}

.page-footer__brands {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-top: 12px;
}

.page-footer__brands-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 32px;
  flex-wrap: wrap;
  opacity: 0.7;
}

.page-footer__brands-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  color: #6b778c;
  letter-spacing: 0.5px;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="withdrawal-page-container">

  
  <?php if(session('success')): ?>
    <div style="background: rgba(0, 209, 122, 0.12); border: 1px solid rgba(0, 209, 122, 0.35); color: #00d17a; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      <span><?php echo e(session('success')); ?></span>
    </div>
  <?php endif; ?>

  <?php if($errors->any()): ?>
    <div style="background: rgba(235, 87, 87, 0.12); border: 1px solid rgba(235, 87, 87, 0.35); color: #ff8a80; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px;">
      <strong style="color: #ff5252;">Please check the following:</strong>
      <ul style="margin: 6px 0 0 18px;">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($err); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="page__sections-row">
    
    <!-- LEFT COLUMN: Account, Withdrawal Form, Latest Requests -->
    <div class="page__sections-column col-left">
      
      <!-- Top Row: Account (1/3) + Withdrawal (2/3) -->
      <div class="top-form-split">
        
        <!-- Account Block -->
        <div class="content-section grow-1of3">
          <div class="content-section__head">
            Account:
          </div>
          <dl class="balance-list">
            <div>
              <dt class="balance-list__label">In the account:</dt>
              <dd class="balance-list__value"><?php echo e(number_format($user->live_balance ?? 0, 2)); ?> $</dd>
            </div>
            <div>
              <dt class="balance-list__label">Available for withdrawal:</dt>
              <dd class="balance-list__value"><?php echo e(number_format($user->live_balance ?? 0, 2)); ?> $</dd>
            </div>
          </dl>
        </div>

        <!-- Withdrawal Form Block -->
        <form class="content-section grow-2of3 form" action="<?php echo e(route('withdrawal.process')); ?>" method="POST">
          <?php echo csrf_field(); ?>
          <div class="content-section__head">
            Withdrawal:
          </div>
          <div class="form-content">
            
            <!-- Row 1: Amount & Payment Method (Populated by Admin Created Methods) -->
            <div class="form__row">
              <div class="form__control grow-1of2">
                <label class="input-control-cabinet input-control-cabinet--text">
                  <span class="input-control-cabinet__label">Amount</span>
                  <div class="input-control-cabinet__input-wrap">
                    <input class="input-control-cabinet__input" type="number" name="amount" id="amount" value="10" min="10" step="any" required>
                    <div class="input-control-cabinet__addon">USD</div>
                  </div>
                </label>
              </div>

              <div class="form__control grow-1of2">
                <label class="input-control-cabinet input-control-cabinet--select">
                  <span class="input-control-cabinet__label">Payment method</span>
                  <div class="input-control-cabinet__input-wrap">
                    <select name="method" id="payment-method-select" class="input-control-cabinet__select-el" onchange="updateWithdrawalFields()" required>
                      <?php if(isset($methods) && $methods->count() > 0): ?>
                        <?php $__currentLoopData = $methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <option value="<?php echo e($method->name); ?>" data-type="<?php echo e($method->type); ?>" data-currency="<?php echo e($method->currency); ?>" data-desc="<?php echo e($method->description); ?>" <?php echo e($loop->first ? 'selected' : ''); ?>>
                            <?php echo e($method->name); ?> <?php echo e($method->currency && $method->currency !== 'USD' ? '('.$method->currency.')' : ''); ?>

                          </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      <?php else: ?>
                        <option value="Binance Pay" selected>Binance Pay</option>
                        <option value="USDT (TRC-20)">USDT (TRC-20)</option>
                        <option value="Easypaisa">Easypaisa</option>
                        <option value="JazzCash">JazzCash</option>
                        <option value="Bank Card">Bank Card</option>
                      <?php endif; ?>
                    </select>
                  </div>
                  <svg class="select-arrow-icon" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </label>
              </div>
            </div>

            <hr class="form-divider">

            <!-- Personal Info & Purse Fields -->
            <?php
              $nameParts = explode(' ', trim($user->name ?? ''));
              $firstName = $nameParts[0] ?? 'User';
              $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
            ?>

            <div class="form__row">
              <div class="form__control">
                <label class="input-control-cabinet input-control-cabinet--text">
                  <span class="input-control-cabinet__label">First name</span>
                  <div class="input-control-cabinet__input-wrap">
                    <input class="input-control-cabinet__input" type="text" name="first_name" value="<?php echo e($firstName); ?>" required>
                  </div>
                </label>
              </div>
            </div>

            <div class="form__row">
              <div class="form__control">
                <label class="input-control-cabinet input-control-cabinet--text">
                  <span class="input-control-cabinet__label">Last name</span>
                  <div class="input-control-cabinet__input-wrap">
                    <input class="input-control-cabinet__input" type="text" name="last_name" value="<?php echo e($lastName); ?>">
                  </div>
                </label>
              </div>
            </div>

            <!-- Receive Type -->
            <div class="form__row">
              <div class="form__control">
                <label class="input-control-cabinet input-control-cabinet--select">
                  <span class="input-control-cabinet__label">Receive type</span>
                  <div class="input-control-cabinet__input-wrap">
                    <select name="receive_type" id="receive-type-select" class="input-control-cabinet__select-el" onchange="updatePurseLabel()">
                      <option value="BINANCE_ID">Binance account ID</option>
                      <option value="EMAIL">Binance account email</option>
                      <option value="WALLET_ADDRESS">Wallet Address / Account Number</option>
                    </select>
                  </div>
                  <svg class="select-arrow-icon" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </label>
              </div>
            </div>

            <!-- Purse Input -->
            <div class="form__row">
              <div class="form__control">
                <label class="input-control-cabinet input-control-cabinet--text">
                  <span class="input-control-cabinet__label" id="purse-label">Purse / Destination</span>
                  <div class="input-control-cabinet__input-wrap">
                    <input class="input-control-cabinet__input" type="text" name="purse" id="purse-input" placeholder="Enter account details / ID..." required>
                  </div>
                </label>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="withdrawal-page__submit form__row">
              <button type="submit" class="button button--primary form-submit">
                <span>Confirm</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
              </button>
            </div>

          </div>
        </form>
      </div>

      <!-- Bottom Row: Latest Requests -->
      <div class="page__sections-row">
        <div class="content-section grow-full">
          <div class="content-section__head">
            <span>Some of your latest requests:</span>
            <a class="content-section__head-action" href="<?php echo e(route('history')); ?>">
              <span>All financial history</span>
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
          </div>

          <div class="withdrawal-table flex-table">
            <?php if(isset($recentTransactions) && $recentTransactions->count() > 0): ?>
              <?php $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $isSuccess = in_array($tx->status, ['completed', 'approved', 'successed', 'success']);
                  $isFailed = in_array($tx->status, ['failed', 'rejected', 'canceled']);
                  $statusClass = $isSuccess ? 'success' : ($isFailed ? 'failed' : 'pending');
                  $statusLabel = $isSuccess ? 'Successed' : ($isFailed ? 'Failed' : 'In progress');
                ?>
                <div class="row">
                  <div class="col-meta">
                    <span class="req-id"><?php echo e($tx->id ? (84000000 + $tx->id) : '84353524'); ?></span>
                    <span class="req-date"><?php echo e($tx->created_at ? $tx->created_at->format('d.m.Y H:i:s') : '10.04.2025 06:37:16'); ?></span>
                  </div>
                  <div class="col-status">
                    <div class="status-pill <?php echo e($statusClass); ?>">
                      <span class="status-dot"></span>
                      <span><?php echo e($statusLabel); ?></span>
                    </div>
                    <div class="status-desc">
                      The withdrawal is currently being processed on the side of the financial operator. Please wait - the funds should be received within 48 hours.
                    </div>
                  </div>
                  <div class="col-amount">
                    <span class="req-method"><?php echo e($tx->method ?? 'Binance Pay'); ?></span>
                    <b class="req-val">+<?php echo e(number_format($tx->amount, 2)); ?> $</b>
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
              
              <div class="row">
                <div class="col-meta">
                  <span class="req-id">84353524</span>
                  <span class="req-date">10.04.2025 06:37:16</span>
                </div>
                <div class="col-status">
                  <div class="status-pill success">
                    <span class="status-dot"></span>
                    <span>Successed</span>
                  </div>
                  <div class="status-desc">
                    The withdrawal is currently being processed on the side of the financial operator. Please wait - the funds should be received within 48 hours.
                  </div>
                </div>
                <div class="col-amount">
                  <span class="req-method">Binance Pay</span>
                  <b class="req-val">+60.00 $</b>
                </div>
              </div>

              <div class="row">
                <div class="col-meta">
                  <span class="req-id">84353478</span>
                  <span class="req-date">10.04.2025 06:36:41</span>
                </div>
                <div class="col-status">
                  <div class="status-pill failed">
                    <span class="status-dot"></span>
                    <span>Failed</span>
                  </div>
                  <div class="status-desc">
                    The withdrawal is currently being processed on the side of the financial operator. Please wait - the funds should be received within 48 hours.
                  </div>
                </div>
                <div class="col-amount">
                  <span class="req-method">Binance Pay</span>
                  <b class="req-val">+10.00 $</b>
                </div>
              </div>

              <div class="row">
                <div class="col-meta">
                  <span class="req-id">83940760</span>
                  <span class="req-date">05.04.2025 09:13:47</span>
                </div>
                <div class="col-status">
                  <div class="status-pill success">
                    <span class="status-dot"></span>
                    <span>Successed</span>
                  </div>
                  <div class="status-desc">
                    The withdrawal is currently being processed on the side of the financial operator. Please wait - the funds should be received within 48 hours.
                  </div>
                </div>
                <div class="col-amount">
                  <span class="req-method">Binance Pay</span>
                  <b class="req-val">+10.00 $</b>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <!-- RIGHT COLUMN: FAQ -->
    <div class="page__sections-column col-right">
      <div class="content-section">
        <div class="content-section__head">
          <span>FAQ:</span>
          <a class="content-section__head-action" href="<?php echo e(route('faq')); ?>">
            <span>Check out full FAQ</span>
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </a>
        </div>

        <div class="faq faq--columns">
          <!-- FAQ Column 1 -->
          <div class="faq__column">
            
            <div class="faq__item">
              <div class="faq__question" onclick="toggleFaq(this)">
                <span>How to withdraw money from the account?</span>
                <span class="faq__chevron">▼</span>
              </div>
              <div class="faq__answer">
                <p>The procedure for withdrawing capital is extremely simple and is carried out through your individual account.</p>
                <p>The method that you have chosen to deposit the account is also a method of withdrawing funds (see the question "How can I deposit?").</p>
                <p>For example, if you made a deposit to your account via the Visa payment system, you will also withdraw money via the Visa payment system.</p>
                <p>When it comes to the withdrawal of a sufficiently large amount, the Company may request verification (verification is requested at the Company's sole discretion), which is why it is very important to register an account individually for yourself in order to confirm your rights to it at any time.</p>
              </div>
            </div>

            <div class="faq__item">
              <div class="faq__question" onclick="toggleFaq(this)">
                <span>How long does it take to withdraw funds?</span>
                <span class="faq__chevron">▼</span>
              </div>
              <div class="faq__answer">
                <p>On average, the withdrawal procedure takes from one to five days from the date of receipt of the corresponding request of the Client and depends only on the volume of simultaneously processed requests. The company always tries to make payments directly on the day the request is received from the Client.</p>
              </div>
            </div>

            <div class="faq__item">
              <div class="faq__question" onclick="toggleFaq(this)">
                <span>What is the minimum withdrawal amount?</span>
                <span class="faq__chevron">▼</span>
              </div>
              <div class="faq__answer">
                <p>The minimum withdrawal amount starts from 10 USD for most payment systems.<br>For cryptocurrencies this amount starts from 50 USD (and may be higher for certain currencies e.g. Bitcoin).</p>
              </div>
            </div>

            <div class="faq__item">
              <div class="faq__question" onclick="toggleFaq(this)">
                <span>Is there any fee for depositing or withdrawing funds from the account?</span>
                <span class="faq__chevron">▼</span>
              </div>
              <div class="faq__answer">
                <p>Deposits are free of charge. Starting from the withdrawal 3 within the same day, a 3% fee applies to each subsequent transaction.</p>
                <p>Please note that payment systems may also charge their own fees or apply internal currency conversion rates.</p>
              </div>
            </div>

            <div class="faq__item">
              <div class="faq__question" onclick="toggleFaq(this)">
                <span>Do I need to provide any documents to make a withdrawal?</span>
                <span class="faq__chevron">▼</span>
              </div>
              <div class="faq__answer">
                <p>Usually, additional documents to withdraw funds are not needed. But the Company at its discretion may ask you to confirm your personal data by requesting certain documents. Usually this is done in order to prevent activities related to illegal trade, financial fraud, as well as the use of funds obtained illegally.</p>
                <p>The list of such documents is minimum, and the operation to provide them will not take you much time and effort.</p>
              </div>
            </div>

          </div>

          <!-- FAQ Column 2 -->
          <div class="faq__column">
            
            <div class="faq__item">
              <div class="faq__question" onclick="toggleFaq(this)">
                <span>What is account verification?</span>
                <span class="faq__chevron">▼</span>
              </div>
              <div class="faq__answer">
                <p>Verification in binary options is a confirmation by the Client of his personal data by providing the Company with additional documents. Verification conditions for the Client are as simple as possible, and the list of documents is minimum. For example, a Company may ask:</p>
                <ul>
                  <li>Provide a color scan copy of the first spread of the Client's passport (passport page with photo)</li>
                  <li>Identify with the help of a "selfie" (photograph of himself)</li>
                  <li>Confirm the address of registration (residence) of the Client, etc</li>
                </ul>
                <p>The Company may request any documents if it is not possible to fully identify the Client and the data entered by him.</p>
                <p>After the electronic copies of documents have been submitted to the Company, the Client will have to wait some time to verify the data provided.</p>
              </div>
            </div>

            <div class="faq__item">
              <div class="faq__question" onclick="toggleFaq(this)">
                <span>How to understand that I need to go through account verification?</span>
                <span class="faq__chevron">▼</span>
              </div>
              <div class="faq__answer">
                <p>If it becomes necessary to pass verification, you will receive a notification by e-mail and / or SMS notification.</p>
                <p>However, the Company uses the contact details that you specified in the registration form (in particular, your email address and phone number). Therefore, be careful to provide relevant and correct information.</p>
              </div>
            </div>

            <div class="faq__item">
              <div class="faq__question" onclick="toggleFaq(this)">
                <span>How long does the verification process take?</span>
                <span class="faq__chevron">▼</span>
              </div>
              <div class="faq__answer">
                <p>No more than 5 (five) business days from the date the Company receives the requested documents.</p>
              </div>
            </div>

            <div class="faq__item">
              <div class="faq__question" onclick="toggleFaq(this)">
                <span>How do I know that I successfully passed verification?</span>
                <span class="faq__chevron">▼</span>
              </div>
              <div class="faq__answer">
                <p>You will receive a notification by e-mail and / or SMS notification about the completion of the verification process of your account and the ability to proceed with operations on the Company's trading platform.</p>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Page Footer -->
  <div class="page-footer">
    <ul class="page-footer__advantages">
      <li>Minimum deposit amount: <span>$10</span></li>
      <li>Minimum withdrawal amount: <span>$10</span></li>
      <li>Quick withdrawal from your account</li>
      <li>First 2 withdrawals in 24 hours are commission-free; then a 3% fee applies</li>
    </ul>

    <div class="page-footer__brands">
      <div class="page-footer__brands-wrapper">
        <div class="page-footer__brands-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <span>Verified by VISA</span>
        </div>
        <div class="page-footer__brands-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <span>SECURE PAYMENT</span>
        </div>
        <div class="page-footer__brands-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v12M6 12h12"/></svg>
          <span>MasterCard SecureCode</span>
        </div>
        <div class="page-footer__brands-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <span>3D Secure</span>
        </div>
        <div class="page-footer__brands-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <span>SECURE SSL ENCRYPTION</span>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function toggleFaq(el) {
  const item = el.closest('.faq__item');
  if (item) {
    item.classList.toggle('open');
  }
}

function updateWithdrawalFields() {
  const select = document.getElementById('payment-method-select');
  if (!select) return;
  const val = select.value.toLowerCase();
  const receiveSelect = document.getElementById('receive-type-select');
  const purseLabel = document.getElementById('purse-label');
  const purseInput = document.getElementById('purse-input');

  if (val.includes('binance')) {
    if (receiveSelect) {
      receiveSelect.innerHTML = `
        <option value="BINANCE_ID">Binance account ID</option>
        <option value="EMAIL">Binance account email</option>
      `;
    }
    if (purseLabel) purseLabel.textContent = 'Binance account ID / Email';
    if (purseInput) purseInput.placeholder = 'Enter Binance ID or Email...';
  } else if (val.includes('usdt') || val.includes('crypto') || val.includes('btc') || val.includes('eth')) {
    if (receiveSelect) {
      receiveSelect.innerHTML = `
        <option value="WALLET_ADDRESS">Wallet Address (TRC-20 / ERC-20)</option>
      `;
    }
    if (purseLabel) purseLabel.textContent = 'Crypto Wallet Address';
    if (purseInput) purseInput.placeholder = 'Enter your wallet address (e.g. T...)';
  } else if (val.includes('easypaisa') || val.includes('jazzcash')) {
    if (receiveSelect) {
      receiveSelect.innerHTML = `
        <option value="MOBILE_ACCOUNT">Mobile Account Number</option>
      `;
    }
    if (purseLabel) purseLabel.textContent = 'Mobile Account Number';
    if (purseInput) purseInput.placeholder = '03XXXXXXXXX';
  } else {
    if (receiveSelect) {
      receiveSelect.innerHTML = `
        <option value="ACCOUNT_NUMBER">Account / Card / Purse Number</option>
      `;
    }
    if (purseLabel) purseLabel.textContent = 'Account / Purse Number';
    if (purseInput) purseInput.placeholder = 'Enter account or wallet number...';
  }
}

function updatePurseLabel() {
  const receiveSelect = document.getElementById('receive-type-select');
  const purseLabel = document.getElementById('purse-label');
  const purseInput = document.getElementById('purse-input');
  if (!receiveSelect || !purseLabel) return;
  const selectedText = receiveSelect.options[receiveSelect.selectedIndex]?.text;
  if (selectedText) {
    purseLabel.textContent = selectedText;
    if (purseInput) purseInput.placeholder = 'Enter ' + selectedText + '...';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  updateWithdrawalFields();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u862879749/domains/markeet-qx.com/public_html/resources/views/account/withdrawal.blade.php ENDPATH**/ ?>