@extends('layouts.platform')

@section('title', 'Live trading | Quotex')

@push('styles')
<style>
/* Quotex Deposit Screen Design */
body {
  background-color: #1c1f2d !important;
}

.modal__backdrop-wrapper {
  min-height: calc(100vh - 70px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
}

.modal__dialog {
  width: 100%;
  max-width: 960px;
  background: #2b3040;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  overflow: hidden;
  position: relative;
}

.modal__body {
  padding: 24px 28px 28px 28px;
}

/* Header */
.modal__header-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}

.VL5jR {
  font-size: 22px;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: -0.2px;
}

.modal__close-btn {
  background: transparent;
  border: none;
  color: #78909c;
  font-size: 22px;
  cursor: pointer;
  line-height: 1;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s;
  text-decoration: none;
}
.modal__close-btn:hover {
  color: #ffffff;
}

/* Country Selector Input */
.P4CNJ {
  margin-bottom: 20px;
}
.NJcSD {
  position: relative;
  display: flex;
  align-items: center;
  background: #282c37;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 8px;
  padding: 0 16px;
  height: 44px;
}
.country-globe-icon {
  margin-right: 12px;
  color: #8b95a5;
  display: flex;
  align-items: center;
}
.FQqgj {
  flex: 1;
  background: transparent;
  border: none;
  color: #ffffff;
  font-size: 14px;
  font-weight: 600;
  outline: none;
}
.FQqgj::placeholder {
  color: #8b95a5;
}
.country-chevron {
  color: #8b95a5;
  font-size: 12px;
  pointer-events: none;
}

/* Main Two-Column Layout */
.fvAJE {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}
@media (max-width: 820px) {
  .fvAJE {
    flex-direction: column;
  }
}

/* Left Sidebar Categories */
.lyV_g {
  width: 250px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
@media (max-width: 820px) {
  .lyV_g {
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
}

.N2udW {
  background: #282c37;
  border-radius: 12px;
  padding: 14px 16px;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
  border: 1px solid transparent;
}
.N2udW:hover {
  background: #313643;
}
.N2udW[aria-checked="true"] {
  background: #0faf59 !important;
  box-shadow: 0 6px 18px rgba(15, 175, 89, 0.28);
}
.N2udW[aria-checked="true"] .IEid_,
.N2udW[aria-checked="true"] .zAk81,
.N2udW[aria-checked="true"] .category-icon {
  color: #ffffff !important;
}

.category-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.category-icon {
  font-size: 16px;
  display: inline-flex;
}
.IEid_ {
  margin: 0;
  font-size: 14px;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}
.zAk81 {
  margin: 2px 0 10px 0;
  font-size: 12px;
  color: #8b95a5;
  font-weight: 500;
}
.uOqDk {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.uOqDk .mini-icon {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  overflow: hidden;
  background: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}
.uOqDk .mini-icon svg,
.uOqDk .mini-icon img {
  width: 100%;
  height: 100%;
}
.PguzQ {
  margin: 0;
  background: rgba(255, 255, 255, 0.22);
  color: #ffffff;
  font-size: 10px;
  font-weight: 700;
  border-radius: 10px;
  padding: 2px 6px;
  line-height: 1.2;
}
.N2udW[aria-checked="false"] .PguzQ {
  background: #3a4050;
  color: #cfd8dc;
}

/* Right Scrollable Methods Container */
.WhQsx {
  flex: 1;
  max-height: 490px;
  overflow-y: auto;
  padding-right: 6px;
}
.WhQsx::-webkit-scrollbar {
  width: 5px;
}
.WhQsx::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 3px;
}
.WhQsx::-webkit-scrollbar-thumb {
  background: #373d4d;
  border-radius: 3px;
}
.WhQsx::-webkit-scrollbar-thumb:hover {
  background: #4a5266;
}

.huM2N {
  margin-bottom: 24px;
}
.qUvM9 {
  color: #ffffff;
  font-size: 15px;
  font-weight: 700;
  margin: 0 0 12px 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.v6IWH {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}
@media (max-width: 600px) {
  .v6IWH {
    grid-template-columns: 1fr;
  }
}

/* Method White Card */
.adllb {
  background: #ffffff;
  border-radius: 8px;
  padding: 10px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  transition: all 0.15s ease;
  user-select: none;
  min-height: 58px;
  box-sizing: border-box;
}
.adllb:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
  filter: brightness(0.98);
}
.method-left {
  display: flex;
  align-items: center;
  gap: 12px;
}
.DAm5y {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.DAm5y svg, .DAm5y img {
  width: 32px;
  height: 32px;
  border-radius: 6px;
}
.method-name-text {
  font-size: 13.5px;
  font-weight: 700;
  color: #1e222d;
  line-height: 1.2;
}
.kbOtR {
  font-size: 11px;
  color: #78909c;
  font-weight: 500;
  margin-top: 3px;
}
.chevron-right {
  color: #b0bec5;
  font-size: 14px;
  font-weight: bold;
  display: flex;
  align-items: center;
}

/* Recent Submissions Bar */
.recent-sub-bar {
  margin-top: 18px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.recent-sub-bar a {
  color: #4f8ef7;
  font-size: 12px;
  text-decoration: none;
  font-weight: 600;
}

/* Payment Details Step Modal / Drawer */
.deposit-step-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.82);
  backdrop-filter: blur(4px);
  z-index: 2500;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.deposit-step-card {
  background: #1e222d;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 16px;
  width: 100%;
  max-width: 520px;
  max-height: 92vh;
  overflow-y: auto;
  padding: 24px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.8);
}
.deposit-step-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
  padding-bottom: 14px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.btn-back-link {
  background: transparent;
  border: none;
  color: #90a4ae;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
}
.btn-back-link:hover { color: #fff; }

.copy-box {
  background: #141720;
  border: 1px solid rgba(0, 200, 83, 0.3);
  border-radius: 8px;
  padding: 12px 14px;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.copy-address-val {
  font-family: monospace;
  font-size: 13.5px;
  color: #ffffff;
  word-break: break-all;
  font-weight: 600;
}
.btn-copy {
  background: #00c853;
  color: #000;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 11px;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.15s;
}
.btn-copy:hover { background: #00e676; }

.instructions-box {
  background: rgba(79, 142, 247, 0.08);
  border-left: 3px solid #4f8ef7;
  padding: 10px 12px;
  border-radius: 0 6px 6px 0;
  font-size: 12.5px;
  color: #cfd8dc;
  margin-bottom: 16px;
  line-height: 1.45;
}

.amount-presets-row {
  display: flex;
  gap: 6px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}
.preset-chip {
  background: #141720;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cfd8dc;
  padding: 5px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.preset-chip:hover, .preset-chip.active {
  border-color: #00c853;
  background: rgba(0, 200, 83, 0.15);
  color: #00e676;
}

.field-input-qx {
  width: 100%;
  box-sizing: border-box;
  background: #141720;
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 14px;
}
.field-input-qx:focus {
  outline: none;
  border-color: #00c853;
}

.proof-section-box {
  background: #141720;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  padding: 14px;
  margin-bottom: 18px;
}

.btn-submit-qx {
  width: 100%;
  padding: 13px;
  background: #00c853;
  color: #000;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(0, 200, 83, 0.25);
  transition: all 0.15s;
}
.btn-submit-qx:hover { background: #00e676; }
</style>
@endpush

@section('content')
<div class="modal__backdrop-wrapper">
  <div class="modal__dialog">
    <div class="modal__body">

      {{-- Header with Title & Close Button --}}
      <div class="modal__header-top">
        <div class="VL5jR">
          Deposit
        </div>
        <a href="{{ url('/trade') }}" class="modal__close-btn" title="Close">
          X
        </a>
      </div>

      {{-- Alert Messages --}}
      @if(session('success'))
        <div style="background: rgba(0, 200, 83, 0.12); border: 1px solid rgba(0, 200, 83, 0.3); color: #b9f6ca; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
          OK {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        <div style="background: rgba(255, 82, 82, 0.12); border: 1px solid rgba(255, 82, 82, 0.3); color: #ff8a80; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
          <strong>Error:</strong> {{ $errors->first() }}
        </div>
      @endif

      {{-- Country Search Box --}}
      <div class="P4CNJ ey44c nFw04">
        <div class="NJcSD _eiRd">
          <span class="country-globe-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
          </span>
          <input class="FQqgj" id="countrySearchInput" placeholder="Search" role="combobox" aria-expanded="false" aria-autocomplete="list" value="Pakistan" oninput="filterMethods(this.value)">
          <span class="country-chevron">v</span>
        </div>
      </div>

      {{-- Main Two-Column View --}}
      <div class="fvAJE">

        {{-- Left Categories Radiogroup --}}
        <div role="radiogroup" aria-label="Payment methods" class="lyV_g K2c3Q">
          @foreach($paymentCategories as $categoryIndex => $category)
            @php
              $categoryMethods = $category->paymentMethods;
              $previewMethods = $categoryMethods->take(5);
              $extraMethods = max($categoryMethods->count() - $previewMethods->count(), 0);
            @endphp
            <div class="N2udW" role="radio" aria-checked="{{ $categoryIndex === 0 ? 'true' : 'false' }}" tabindex="0" onclick="selectCategory('payment-category-{{ $category->id }}', this)">
              <div>
                <div class="category-title-row">
                  <span class="category-icon">{!! renderCategoryIcon($category) !!}</span>
                  <p class="IEid_">{{ strtoupper($category->name) }}</p>
                </div>
                <p class="zAk81">{{ $categoryMethods->count() }} {{ \Illuminate\Support\Str::plural('method', $categoryMethods->count()) }}</p>
                <div class="uOqDk">
                  @foreach($previewMethods as $previewMethod)
                    <span class="mini-icon">
                      @if($previewMethod->logo_url)
                        <img src="{{ $previewMethod->logo_url }}" alt="{{ $previewMethod->name }}" onerror="this.style.display='none';">
                      @else
                        {!! getMethodLogo($previewMethod->icon ?: $previewMethod->provider_code ?: 'card') !!}
                      @endif
                    </span>
                  @endforeach
                  @if($extraMethods > 0)
                    <p class="PguzQ">+{{ $extraMethods }}</p>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
        {{-- Right Side Method Lists --}}
        <div class="WhQsx j0UdX" id="methodsScrollContainer">
          @forelse($paymentCategories as $category)
            <div id="payment-category-{{ $category->id }}" class="huM2N payment-category-section">
              <p class="qUvM9">{{ $category->short_label ?: $category->name }} ({{ $category->paymentMethods->count() }})</p>
              <div class="v6IWH">
                @foreach($category->paymentMethods as $method)
                  @php
                    $isOnline = $method->provider === 'lgpay';
                    $currency = $isOnline ? 'PKR' : $method->currency;
                    $description = $isOnline ? 'Online auto payment.' : $method->description;
                  @endphp
                  {!! renderMethodCard($method->name, 'Min. $' . number_format($method->min_amount, 2), $method->icon ?: $method->provider_code ?: 'card', $method->min_amount, $currency, $method->conversion_rate, $methods, $method->id, $isOnline ? null : $method->wallet_address, $description, $isOnline ? 'lgpay' : 'manual', $method->max_amount, $method->logo_url, $category->id > 0 ? $category->id : null) !!}
                @endforeach
              </div>
            </div>
          @empty
            <div class="huM2N">
              <p class="qUvM9">Payment methods are currently unavailable.</p>
            </div>
          @endforelse
        </div>

      </div>

      {{-- Recent Submissions Status --}}
      @if($recentDeposits && $recentDeposits->count() > 0)
        <div class="recent-sub-bar">
          <div style="font-size: 12.5px; color: #90a4ae;">
            Latest Deposit: 
            <strong style="color: #fff;">{{ $recentDeposits->first()->method }} (${{ number_format($recentDeposits->first()->amount, 2) }})</strong>
            - 
            @if($recentDeposits->first()->status === 'approved')
              <span style="color: #00e676; font-weight: 700;">Approved</span>
            @elseif($recentDeposits->first()->status === 'processing')
              <span style="color: #ffab00; font-weight: 700;">Processing</span>
            @elseif($recentDeposits->first()->status === 'rejected')
              <span style="color: #ff5252; font-weight: 700;">{{ $recentDeposits->first()->provider === 'lgpay' ? 'Failed' : 'Rejected' }}</span>
            @else
              <span style="color: #ffab00; font-weight: 700;">Under Review</span>
            @endif
          </div>
          <a href="#deposit-history-modal" onclick="showHistoryModal()">View History ({{ $recentDeposits->count() }}) &gt;</a>
        </div>
      @endif

    </div>
  </div>
</div>

{{-- Payment Step Drawer / Modal --}}
<div class="deposit-step-backdrop" id="depositStepModal">
  <div class="deposit-step-card">
    <div class="deposit-step-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span id="modal-logo-icon" style="display:inline-flex; width:28px; height:28px;"></span>
        <h3 id="modal-title" style="color:#fff; margin:0; font-size:16px; font-weight:700;">Deposit Details</h3>
      </div>
      <button type="button" class="btn-back-link" onclick="closeDepositModal()">X Close</button>
    </div>

    <form id="depositForm" action="{{ route('deposit.process') }}" data-manual-action="{{ route('deposit.process') }}" data-lgpay-action="{{ route('lgpay.initiate') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="method" id="modal-input-method">
      <input type="hidden" name="payment_method_id" id="modal-input-id">
      <input type="hidden" name="payment_category_id" id="modal-input-category-id">

      {{-- Wallet Address Box with Copy Button --}}
      <div id="walletAddressLabel" style="font-size:12px; font-weight:600; color:#90a4ae; margin-bottom:6px;">
        Send payment to this Account / Address:
      </div>
      <div class="copy-box" id="walletAddressBox">
        <span class="copy-address-val" id="modal-wallet-address">03001234567</span>
        <button type="button" class="btn-copy" id="btnCopyAddress" onclick="copyWalletAddress()">
          Copy
        </button>
      </div>

      {{-- Instructions --}}
      <div class="instructions-box" id="modal-description">
        Please transfer the amount to the address/account above and upload your proof below.
      </div>

      {{-- Amount selection --}}
      <div style="margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
          <label style="font-size:13px; font-weight:600; color:#cfd8dc;">Amount in USD ($) *</label>
          <span style="font-size:11px; color:#00e676; font-weight:600;" id="modal-rate-text">Rate: 1 USD = 280 PKR</span>
        </div>

        <div class="amount-presets-row">
          <button type="button" class="preset-chip" onclick="setPresetAmt(10)">$10</button>
          <button type="button" class="preset-chip" onclick="setPresetAmt(50)">$50</button>
          <button type="button" class="preset-chip active" onclick="setPresetAmt(100)">$100</button>
          <button type="button" class="preset-chip" onclick="setPresetAmt(250)">$250</button>
          <button type="button" class="preset-chip" onclick="setPresetAmt(500)">$500</button>
        </div>

        <div style="position:relative;">
          <input type="number" id="deposit-amount-input" name="amount" class="field-input-qx" style="padding-left:26px; font-size:16px; font-weight:700;" value="100" min="5" step="1" required oninput="calcConvertedAmount()">
          <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-weight:700; color:#90a4ae;">$</span>
        </div>

        {{-- Converted Amount --}}
        <div style="margin-top:8px; padding:10px; background:#141720; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
          <span style="font-size:12px; color:#90a4ae;">Amount to send:</span>
          <strong id="modal-converted-total" style="color:#00e676; font-size:15px;">28,000 PKR</strong>
        </div>
      </div>

      {{-- Payment Verification --}}
      <div class="proof-section-box" id="manualProofFields">
        <div style="font-size:13px; font-weight:700; color:#fff; margin-bottom:4px;">
          Submit Payment Verification *
        </div>
        <p style="font-size:11.5px; color:#90a4ae; margin:0 0 10px 0;">
          Submit your Transaction ID <strong>OR</strong> upload a screenshot receipt.
        </p>

        {{-- TRX ID --}}
        <div style="margin-bottom:10px;">
          <label style="font-size:11.5px; color:#cfd8dc; font-weight:600; display:block; margin-bottom:4px;">
            Transaction ID / TRX Hash
          </label>
          <input type="text" id="input_transaction_id" name="transaction_id" class="field-input-qx" placeholder="e.g. 98327492817 or Blockchain TxID">
        </div>

        {{-- Screenshot upload --}}
        <div>
          <label style="font-size:11.5px; color:#cfd8dc; font-weight:600; display:block; margin-bottom:4px;">
            Payment Receipt Screenshot
          </label>
          <input type="file" id="input_proof_image" name="proof_image" accept="image/*" class="field-input-qx" style="padding:6px;" onchange="previewProofImage(this)">
          <img id="proof-preview" style="max-width:100%; max-height:120px; margin-top:8px; border-radius:6px; display:none; border:1px solid #00c853;">
        </div>
      </div>

      <button type="submit" class="btn-submit-qx" id="btnSubmitDeposit">
        Submit Deposit for Verification
      </button>
    </form>
  </div>
</div>

{{-- History Modal --}}
<div class="deposit-step-backdrop" id="historyModal">
  <div class="deposit-step-card" style="max-width: 600px;">
    <div class="deposit-step-header">
      <h3 style="color:#fff; margin:0; font-size:16px;">Deposit History</h3>
      <button type="button" class="btn-back-link" onclick="closeHistoryModal()">X Close</button>
    </div>
    <div style="overflow-x:auto;">
      <table style="width:100%; font-size:12.5px; border-collapse:collapse;">
        <thead>
          <tr style="border-bottom:1px solid rgba(255,255,255,0.08); color:#8b95a5; text-align:left;">
            <th style="padding:8px;">Date</th>
            <th style="padding:8px;">Method</th>
            <th style="padding:8px;">Amount</th>
            <th style="padding:8px;">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentDeposits as $tx)
            <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
              <td style="padding:8px; color:#78909c;">{{ $tx->created_at->format('M d, H:i') }}</td>
              <td style="padding:8px; color:#fff; font-weight:600;">{{ $tx->method }}</td>
              <td style="padding:8px; color:#00e676; font-weight:700;">${{ number_format($tx->amount, 2) }}</td>
              <td style="padding:8px;">
                @if($tx->status === 'approved')
                  <span style="color:#00e676; font-weight:700;">Approved</span>
                @elseif($tx->status === 'processing')
                  <span style="color:#ffab00; font-weight:700;">Processing</span>
                @elseif($tx->status === 'rejected')
                  <span style="color:#ff5252; font-weight:700;">{{ $tx->provider === 'lgpay' ? 'Failed' : 'Rejected' }}</span>
                @else
                  <span style="color:#ffab00; font-weight:700;">Under Review</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="4" style="padding:16px; text-align:center; color:#8b95a5;">No deposit history found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@php
// Helper functions for SVG logos
function getMethodLogo($type) {
  switch ($type) {
    case 'binance':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#F3BA2F"/><path fill="#12161C" d="M16 6.5l3.2 3.2-3.2 3.2-3.2-3.2L16 6.5zm-5.7 5.7l3.2 3.2-3.2 3.2-3.2-3.2 3.2-3.2zm11.4 0l3.2 3.2-3.2 3.2-3.2-3.2 3.2-3.2zM16 17.9l3.2 3.2-3.2 3.2-3.2-3.2 3.2-3.2zm-7.9 3.4l-1.6 1.6 9.5 9.5 9.5-9.5-1.6-1.6-7.9 7.9-7.9-7.9z"/></svg>';
    case 'easypaisa':
      return '<svg viewBox="0 0 32 32"><rect width="32" height="32" rx="8" fill="#00A651"/><circle cx="16" cy="16" r="9" fill="none" stroke="#ffffff" stroke-width="4" stroke-dasharray="42 15" stroke-linecap="round"/><circle cx="16" cy="16" r="3.5" fill="#ffffff"/></svg>';
    case 'jazzcash':
      return '<svg viewBox="0 0 32 32"><rect width="32" height="32" rx="8" fill="#E30613"/><path d="M11 6h4v14c0 2.2-1.8 4-4 4H9v-3h2c.6 0 1-.4 1-1V6z" fill="#FFCB05"/><path d="M18 10h4v10c0 2.2-1.8 4-4 4h-2v-3h2c.6 0 1-.4 1-1v-6c0-1.7-1.3-3-3-3h-1V8h3c1.7 0 3 .9 3 2z" fill="#FFCB05"/></svg>';
    case 'raast':
      return '<svg viewBox="0 0 32 32"><rect width="32" height="32" rx="8" fill="#006838"/><path d="M7 23h18v2H7zM9 13h14v2H9zm2-4h10v2H11zm5-5l6 4H10l6-4zm-4 11h2v5h-2zm6 0h2v5h-2z" fill="#ffffff"/><text x="16" y="29" font-size="4" fill="#ffffff" text-anchor="middle" font-weight="bold" font-family="sans-serif">RAAST</text></svg>';
    case 'cashmaal':
      return '<svg viewBox="0 0 32 32"><rect width="32" height="32" rx="8" fill="#1B2028"/><rect x="2" y="2" width="28" height="28" rx="6" fill="none" stroke="#FF6B00" stroke-width="1.5"/><text x="16" y="20" font-size="11" fill="#ffffff" text-anchor="middle" font-weight="900" font-family="sans-serif">CM</text></svg>';
    case 'card':
      return '<svg viewBox="0 0 32 32"><rect width="32" height="32" rx="6" fill="#222634"/><rect x="4" y="8" width="24" height="16" rx="3" fill="none" stroke="#4F8EF7" stroke-width="2"/><path d="M4 13h24" stroke="#4F8EF7" stroke-width="2"/><circle cx="10" cy="19" r="1.5" fill="#EB001B"/><circle cx="13" cy="19" r="1.5" fill="#F79E1B"/></svg>';
    case 'usdt':
    case 'usdt_trc20':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#26A17B"/><path fill="#ffffff" d="M17.9 14.5v-2.2h5.6V9H8.5v3.3h5.6v2.2C9.5 14.7 6 15.5 6 16.5s3.5 1.8 8.1 2v7.1h3.8v-7.1c4.6-.2 8.1-1 8.1-2s-3.5-1.8-8.1-2zm0 3.2v-.8c3.9-.1 6.8-.7 6.8-1.4s-2.9-1.3-6.8-1.4v2.2c-.6 0-1.3 0-1.9 0v-2.2c-3.9.1-6.8.7-6.8 1.4s2.9 1.3 6.8 1.4v.8c-4.4-.2-7.8-.9-7.8-1.8 0-.9 3.4-1.6 7.8-1.8V12h3.8v2.5c4.4.2 7.8.9 7.8 1.8 0 .9-3.4 1.6-7.8 1.8z"/><circle cx="25" cy="24" r="5.5" fill="#eb0029"/><text x="25" y="26.5" font-size="5" font-weight="bold" fill="#ffffff" text-anchor="middle" font-family="sans-serif">T</text></svg>';
    case 'usdt_bep20':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#26A17B"/><path fill="#ffffff" d="M17.9 14.5v-2.2h5.6V9H8.5v3.3h5.6v2.2C9.5 14.7 6 15.5 6 16.5s3.5 1.8 8.1 2v7.1h3.8v-7.1c4.6-.2 8.1-1 8.1-2s-3.5-1.8-8.1-2zm0 3.2v-.8c3.9-.1 6.8-.7 6.8-1.4s-2.9-1.3-6.8-1.4v2.2c-.6 0-1.3 0-1.9 0v-2.2c-3.9.1-6.8.7-6.8 1.4s2.9 1.3 6.8 1.4v.8c-4.4-.2-7.8-.9-7.8-1.8 0-.9 3.4-1.6 7.8-1.8V12h3.8v2.5c4.4.2 7.8.9 7.8 1.8 0 .9-3.4 1.6-7.8 1.8z"/><circle cx="25" cy="24" r="5.5" fill="#F3BA2F"/><text x="25" y="26.5" font-size="4.5" font-weight="bold" fill="#000000" text-anchor="middle" font-family="sans-serif">B</text></svg>';
    case 'usdt_erc20':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#26A17B"/><path fill="#ffffff" d="M17.9 14.5v-2.2h5.6V9H8.5v3.3h5.6v2.2C9.5 14.7 6 15.5 6 16.5s3.5 1.8 8.1 2v7.1h3.8v-7.1c4.6-.2 8.1-1 8.1-2s-3.5-1.8-8.1-2zm0 3.2v-.8c3.9-.1 6.8-.7 6.8-1.4s-2.9-1.3-6.8-1.4v2.2c-.6 0-1.3 0-1.9 0v-2.2c-3.9.1-6.8.7-6.8 1.4s2.9 1.3 6.8 1.4v.8c-4.4-.2-7.8-.9-7.8-1.8 0-.9 3.4-1.6 7.8-1.8V12h3.8v2.5c4.4.2 7.8.9 7.8 1.8 0 .9-3.4 1.6-7.8 1.8z"/><circle cx="25" cy="24" r="5.5" fill="#627EEA"/><text x="25" y="26.5" font-size="4.5" font-weight="bold" fill="#ffffff" text-anchor="middle" font-family="sans-serif">E</text></svg>';
    case 'usdt_poly':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#26A17B"/><path fill="#ffffff" d="M17.9 14.5v-2.2h5.6V9H8.5v3.3h5.6v2.2C9.5 14.7 6 15.5 6 16.5s3.5 1.8 8.1 2v7.1h3.8v-7.1c4.6-.2 8.1-1 8.1-2s-3.5-1.8-8.1-2zm0 3.2v-.8c3.9-.1 6.8-.7 6.8-1.4s-2.9-1.3-6.8-1.4v2.2c-.6 0-1.3 0-1.9 0v-2.2c-3.9.1-6.8.7-6.8 1.4s2.9 1.3 6.8 1.4v.8c-4.4-.2-7.8-.9-7.8-1.8 0-.9 3.4-1.6 7.8-1.8V12h3.8v2.5c4.4.2 7.8.9 7.8 1.8 0 .9-3.4 1.6-7.8 1.8z"/><circle cx="25" cy="24" r="5.5" fill="#8247E5"/><text x="25" y="26.5" font-size="4.5" font-weight="bold" fill="#ffffff" text-anchor="middle" font-family="sans-serif">P</text></svg>';
    case 'btc':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#F7931A"/><path fill="#ffffff" d="M22.5 13.7c.3-2.1-1.3-3.2-3.5-3.9l.7-2.9-1.8-.4-.7 2.8c-.5-.1-1-.2-1.5-.3l.7-2.9-1.8-.4-.7 2.9c-.4-.1-.8-.2-1.1-.3l-2.4-.6-.5 1.9s1.3.3 1.3.3c.7.2.8.7.8 1.1l-.8 3.2c.1 0 .1 0 .2.1l-.2-.1-1.1 4.5c-.1.2-.3.6-.8.4 0 0-1.3-.3-1.3-.3l-.9 2 2.3.6c.4.1.9.2 1.3.3l-.7 3 1.8.4.7-2.9c.5.1 1 .2 1.5.3l-.7 2.9 1.8.4.7-2.9c3.1.6 5.4.3 6.3-2.4.8-2.2 0-3.5-1.6-4.3 1.2-.3 2-1.1 2.2-2.7zm-3.9 5.8c-.6 2.3-4.4 1-5.6.8l1-4c1.2.3 5.2.9 4.6 3.2zm.6-5.9c-.5 2.1-3.7.9-4.7.7l.9-3.7c1 .3 4.3.7 3.8 3z"/></svg>';
    case 'ltc':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#345D9D"/><path fill="#ffffff" d="M15.3 7h3.7l-2.6 9.8 2.6-.9-.6 2.2-2.6.9-1.7 6.4 8.7.6-.8 3h-12l5.3-20.2-2.6.9.6-2.2 2.6-.9.4-1.6z"/></svg>';
    case 'eth':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#627EEA"/><path fill="#ffffff" fill-opacity=".6" d="M16 4v8.9l7.5 3.3z"/><path fill="#ffffff" d="M16 4L8.5 16.2l7.5-3.3z"/><path fill="#ffffff" fill-opacity=".6" d="M16 21.9v6.1l7.5-10.4z"/><path fill="#ffffff" d="M16 28V21.9L8.5 17.6z"/><path fill="#ffffff" fill-opacity=".2" d="M16 20.6l7.5-4.4L16 12.9z"/><path fill="#ffffff" fill-opacity=".6" d="M8.5 16.2l7.5 4.4v-7.7z"/></svg>';
    case 'bch':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#0AC18E"/><path fill="#ffffff" d="M19.8 14.5c.3-1.8-1.1-2.8-3-3.4l.6-2.5-1.5-.4-.6 2.4c-.4-.1-.8-.2-1.3-.3l.6-2.5-1.5-.4-.6 2.5c-.3-.1-.7-.2-1-.2l-2.1-.5-.4 1.7s1.1.3 1.1.3c.6.2.7.6.7 1l-.7 2.8-.9 3.9c-.1.2-.3.5-.7.4 0 0-1.1-.3-1.1-.3l-.8 1.7 2 .5c.4.1.7.2 1.1.3l-.6 2.6 1.5.4.6-2.5c.4.1.9.2 1.3.3l-.6 2.5 1.5.4.6-2.5c2.7.5 4.7.3 5.5-2.1.7-1.9 0-3-1.4-3.7 1-.3 1.7-1 1.9-2.3z"/></svg>';
    case 'trx':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#EB0029"/><path fill="#ffffff" d="M6 8.5l19.5 4.5-9.5 12.5L6 8.5zm17.5 4.2L8.5 9.7l7.5 13 7.5-10z"/></svg>';
    case 'dash':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#008CE7"/><path fill="#ffffff" d="M8 17h11.5c3 0 5-1.8 5-4.5s-2-4.5-5-4.5H10l-2 9zm2.5-7h8.5c1.8 0 3 1 3 2.5s-1.2 2.5-3 2.5h-7.5l-1-5zm-4.5 12h14c3 0 5-1.8 5-4.5H6.5l-.5 4.5z"/></svg>';
    case 'poly':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#8247E5"/><path fill="#ffffff" d="M20.6 13.4c-.6-.4-1.4-.4-2 0l-3.3 1.9-2.2 1.3-3.3 1.9c-.6.4-1.4.4-2 0l-2.6-1.5c-.6-.4-.6-1 0-1.4l2.6-1.5c.6-.4 1.4-.4 2 0l2.2 1.3 3.3-1.9 2.2-1.3 3.3-1.9c.6-.4 1.4-.4 2 0l2.6 1.5c.6.4.6 1 0 1.4l-2.6 1.5c-.6.4-1.4.4-2 0l-2.2-1.3-3.3 1.9 2.2 1.3 3.3-1.9z"/></svg>';
    case 'dai':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#F4B731"/><path fill="#ffffff" d="M11 9h6c3.9 0 7 3.1 7 7s-3.1 7-7 7h-6V9zm3 2.5v3.2h5.5c.3 0 .6-.1.8-.2-.8-.9-2-1.5-3.3-1.5H14v-1.5zm0 4.7v3.6h3c1.3 0 2.5-.6 3.3-1.5-.2-.1-.5-.2-.8-.2H14v-1.9z"/></svg>';
    case 'shib':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#FFA409"/><circle cx="16" cy="16" r="10" fill="#E53935"/><path d="M12 11l4 5 4-5-2 9h-4z" fill="#ffffff"/></svg>';
    case 'doge':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#C2A633"/><path fill="#ffffff" d="M11 8h6.5c3.6 0 6.5 2.9 6.5 6.5v3c0 3.6-2.9 6.5-6.5 6.5H11V8zm3 3v4h4v-4h-4zm0 6v4h4v-4h-4z"/></svg>';
    case 'xrp':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#23292F"/><path fill="#ffffff" d="M23.5 8l-3.3 3.3c-2.3 2.3-6.1 2.3-8.4 0L8.5 8H6l4.6 4.6c3 3 7.8 3 10.8 0L26 8h-2.5zm-15 16l3.3-3.3c2.3-2.3 6.1-2.3 8.4 0L23.5 24H26l-4.6-4.6c-3-3-7.8-3-10.8 0L6 24h2.5z"/></svg>';
    case 'ton':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#0088CC"/><path fill="#ffffff" d="M24 10l-14 3 6 11 8-14zm-12.5 2.5l5.5 8-4-6.5-1.5-1.5z"/></svg>';
    case 'kucoin':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#24AE8F"/><path fill="#ffffff" d="M11 8h4v6.5l4-4.5h5l-5.5 6 6 8h-5l-4.5-6.5V24h-4V8z"/></svg>';
    case 'usdc':
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#2775CA"/><path fill="#ffffff" d="M16 6c5.5 0 10 4.5 10 10s-4.5 10-10 10S6 21.5 6 16 10.5 6 16 6zm1.2 5.5h-2.4v1.3c-2.2.3-3.3 1.6-3.3 3.2 0 2.2 1.8 2.8 3.8 3.2 1.5.3 2.2.6 2.2 1.4s-.7 1.3-1.8 1.3c-1.3 0-2.2-.6-2.5-1.7h-2.2c.3 2 1.6 3.1 3.6 3.5v1.3h2.4v-1.3c2.2-.3 3.5-1.6 3.5-3.3 0-2.2-1.7-2.9-3.8-3.3-1.5-.3-2.2-.6-2.2-1.3s.7-1.2 1.7-1.2c1.1 0 1.9.5 2.2 1.5h2.1c-.3-1.8-1.5-2.9-3.3-3.3v-1.3z"/></svg>';
    default:
      return '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#4F8EF7"/><text x="16" y="21" font-size="14" fill="#ffffff" text-anchor="middle" font-weight="bold">$</text></svg>';
  }
}

function renderCategoryIcon($category) {
  if ($category->image_url) {
    return '<img src="' . e($category->image_url) . '" alt="' . e($category->name) . '" onerror="this.style.display=\'none\';">';
  }

  $icon = strtolower(trim((string) $category->icon));
  $map = [
    'fire' => '🔥',
    'popular' => '🔥',
    'wallet' => '👛',
    'e-pay' => '👛',
    'card' => '💳',
    'bank' => '🏦',
    'banks' => '🏦',
    'coin' => '🪙',
    'crypto' => '🪙',
  ];

  return e($map[$icon] ?? ($category->icon ?: '💳'));
}

function renderMethodCard($name, $minText, $logoKey, $minVal, $curr, $rate, $dbMethods, $customId = null, $customAddr = null, $customDesc = null, $provider = 'manual', $maxAmount = null, $logoUrl = null, $categoryId = null) {
  // Try matching with an existing database payment method configured by admin
  $matched = $dbMethods->first(function($m) use ($name) {
    return strtolower(trim($m->name)) === strtolower(trim($name))
        || str_contains(strtolower($m->name), strtolower(explode(' ', $name)[0]))
        || str_contains(strtolower($name), strtolower(explode(' ', $m->name)[0]));
  });

  $methodId = $customId ?? ($matched ? $matched->id : '');
  $currency = $matched ? $matched->currency : $curr;
  $conversionRate = $matched ? (float)$matched->conversion_rate : (float)$rate;
  $walletAddress = $customAddr ?? ($matched ? $matched->wallet_address : ($currency === 'PKR' ? '03001234567' : 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'));
  $description = $customDesc ?? ($matched && $matched->description ? $matched->description : "Please send the required {$currency} amount to the wallet/account address provided above. After payment, enter the Transaction ID or upload your receipt.");
  $minAmount = $matched ? (float)$matched->min_amount : (float)$minVal;

  $dataObj = json_encode([
    'id' => $methodId,
    'name' => $name,
    'currency' => $currency,
    'conversion_rate' => $conversionRate,
    'wallet_address' => $walletAddress,
    'description' => $description,
    'min_amount' => $minAmount,
    'max_amount' => $maxAmount,
    'provider' => $provider,
    'logo_key' => $logoKey,
    'logo_url' => $logoUrl,
    'category_id' => $categoryId
  ]);

  $logoHtml = $logoUrl
    ? '<img src="' . e($logoUrl) . '" alt="' . e($name) . '" onerror="this.style.display=\'none\';">'
    : getMethodLogo($logoKey);

  return "
    <div class=\"adllb\" data-name=\"{$name}\" onclick='openDepositStep({$dataObj})'>
      <div class=\"method-left\">
        <div class=\"DAm5y\">{$logoHtml}</div>
        <div>
          <div class=\"method-name-text\">{$name}</div>
          <div class=\"kbOtR\">{$minText}</div>
        </div>
      </div>
      <span class=\"chevron-right\">&gt;</span>
    </div>
  ";
}
@endphp

@push('scripts')
<script>
let currentMethodData = null;

function selectCategory(sectionId, el) {
  // Update radio buttons state
  document.querySelectorAll('.N2udW').forEach(card => card.setAttribute('aria-checked', 'false'));
  el.setAttribute('aria-checked', 'true');

  // Smooth scroll to the section
  const target = document.getElementById(sectionId);
  const container = document.getElementById('methodsScrollContainer');
  if (target && container) {
    container.scrollTo({
      top: target.offsetTop - container.offsetTop - 10,
      behavior: 'smooth'
    });
  }
}

function filterMethods(searchVal) {
  const query = (searchVal || '').toLowerCase().trim();
  const cards = document.querySelectorAll('.adllb');
  cards.forEach(card => {
    const name = (card.getAttribute('data-name') || '').toLowerCase();
    if (name.includes(query) || query === '') {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });
}

const methodLogosMap = {
  'binance': '{!! addslashes(getMethodLogo('binance')) !!}',
  'easypaisa': '{!! addslashes(getMethodLogo('easypaisa')) !!}',
  'jazzcash': '{!! addslashes(getMethodLogo('jazzcash')) !!}',
  'raast': '{!! addslashes(getMethodLogo('raast')) !!}',
  'cashmaal': '{!! addslashes(getMethodLogo('cashmaal')) !!}',
  'card': '{!! addslashes(getMethodLogo('card')) !!}',
  'usdt': '{!! addslashes(getMethodLogo('usdt')) !!}',
  'usdt_trc20': '{!! addslashes(getMethodLogo('usdt_trc20')) !!}',
  'usdt_bep20': '{!! addslashes(getMethodLogo('usdt_bep20')) !!}',
  'usdt_erc20': '{!! addslashes(getMethodLogo('usdt_erc20')) !!}',
  'usdt_poly': '{!! addslashes(getMethodLogo('usdt_poly')) !!}',
  'btc': '{!! addslashes(getMethodLogo('btc')) !!}',
  'ltc': '{!! addslashes(getMethodLogo('ltc')) !!}',
  'eth': '{!! addslashes(getMethodLogo('eth')) !!}',
  'bch': '{!! addslashes(getMethodLogo('bch')) !!}',
  'trx': '{!! addslashes(getMethodLogo('trx')) !!}',
  'dash': '{!! addslashes(getMethodLogo('dash')) !!}',
  'poly': '{!! addslashes(getMethodLogo('poly')) !!}',
  'dai': '{!! addslashes(getMethodLogo('dai')) !!}',
  'shib': '{!! addslashes(getMethodLogo('shib')) !!}',
  'doge': '{!! addslashes(getMethodLogo('doge')) !!}',
  'xrp': '{!! addslashes(getMethodLogo('xrp')) !!}',
  'ton': '{!! addslashes(getMethodLogo('ton')) !!}',
  'kucoin': '{!! addslashes(getMethodLogo('kucoin')) !!}',
  'usdc': '{!! addslashes(getMethodLogo('usdc')) !!}'
};

function openDepositStep(data) {
  currentMethodData = data;
  const isLGPay = data.provider === 'lgpay';
  const form = document.getElementById('depositForm');
  form.action = isLGPay ? form.dataset.lgpayAction : form.dataset.manualAction;
  form.enctype = isLGPay ? 'application/x-www-form-urlencoded' : 'multipart/form-data';

  document.getElementById('modal-title').innerText = 'Deposit via ' + data.name;
  document.getElementById('modal-logo-icon').innerHTML = data.logo_url
    ? '<img src="' + data.logo_url + '" alt="' + data.name + '" onerror="this.style.display=\'none\'">'
    : (methodLogosMap[data.logo_key] || methodLogosMap['card']);

  document.getElementById('modal-input-method').value = data.name;
  document.getElementById('modal-input-id').value = data.id || '';
  document.getElementById('modal-input-category-id').value = data.category_id || '';

  const wallet = data.wallet_address || (isLGPay ? 'Secure online checkout' : 'Contact administrator for payment details');
  document.getElementById('modal-wallet-address').innerText = wallet;
  document.getElementById('modal-description').innerText = data.description;
  document.getElementById('modal-rate-text').innerText = '1 USD = ' + parseFloat(data.conversion_rate).toFixed(2) + ' ' + data.currency;
  document.getElementById('walletAddressLabel').style.display = isLGPay ? 'none' : 'block';
  document.getElementById('walletAddressBox').style.display = isLGPay ? 'none' : 'flex';
  document.getElementById('manualProofFields').style.display = isLGPay ? 'none' : 'block';
  document.getElementById('btnSubmitDeposit').innerText = isLGPay ? 'Continue to Secure Payment' : 'Submit Deposit for Verification';

  const amtInput = document.getElementById('deposit-amount-input');
  amtInput.min = data.min_amount || 5;
  if (data.max_amount) {
    amtInput.max = data.max_amount;
  } else {
    amtInput.removeAttribute('max');
  }
  if (parseFloat(amtInput.value) < parseFloat(data.min_amount)) {
    amtInput.value = data.min_amount;
  }

  calcConvertedAmount();

  // Reset verification form inputs
  document.getElementById('input_transaction_id').value = '';
  document.getElementById('input_proof_image').value = '';
  document.getElementById('proof-preview').style.display = 'none';

  document.getElementById('depositStepModal').style.display = 'flex';
}

function closeDepositModal() {
  document.getElementById('depositStepModal').style.display = 'none';
}

function showHistoryModal() {
  document.getElementById('historyModal').style.display = 'flex';
}

function closeHistoryModal() {
  document.getElementById('historyModal').style.display = 'none';
}

function copyWalletAddress() {
  const address = document.getElementById('modal-wallet-address').innerText;
  navigator.clipboard.writeText(address).then(() => {
    const btn = document.getElementById('btnCopyAddress');
    btn.innerText = 'Copied!';
    btn.style.background = '#ffffff';
    setTimeout(() => {
      btn.innerText = 'Copy';
      btn.style.background = '#00c853';
    }, 2000);
  }).catch(() => {
    const tempInput = document.createElement('input');
    tempInput.value = address;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    alert('Address copied: ' + address);
  });
}

function setPresetAmt(val) {
  document.querySelectorAll('.preset-chip').forEach(c => c.classList.remove('active'));
  event.target.classList.add('active');
  document.getElementById('deposit-amount-input').value = val;
  calcConvertedAmount();
}

function calcConvertedAmount() {
  if (!currentMethodData) return;
  const amt = parseFloat(document.getElementById('deposit-amount-input').value) || 0;
  const rate = parseFloat(currentMethodData.conversion_rate) || 1.0;
  const converted = (amt * rate).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });
  document.getElementById('modal-converted-total').innerText = converted + ' ' + currentMethodData.currency;
}

function previewProofImage(input) {
  const preview = document.getElementById('proof-preview');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Validation: Require either Transaction ID or proof image screenshot
document.getElementById('depositForm')?.addEventListener('submit', function(e) {
  if (currentMethodData && currentMethodData.provider === 'lgpay') {
    const amt = parseFloat(document.getElementById('deposit-amount-input').value) || 0;
    if (amt < parseFloat(currentMethodData.min_amount || 0) || (currentMethodData.max_amount && amt > parseFloat(currentMethodData.max_amount))) {
      e.preventDefault();
      alert('Please enter an amount within the allowed limits.');
      return false;
    }

    const btn = document.getElementById('btnSubmitDeposit');
    btn.innerText = 'Redirecting...';
    btn.disabled = true;
    return true;
  }

  const txnId = document.getElementById('input_transaction_id').value.trim();
  const fileInput = document.getElementById('input_proof_image');
  const hasFile = fileInput.files && fileInput.files.length > 0;

  if (!txnId && !hasFile) {
    e.preventDefault();
    alert('Please enter your Transaction ID OR upload a payment receipt screenshot.');
    return false;
  }

  const btn = document.getElementById('btnSubmitDeposit');
  btn.innerText = 'Submitting...';
  btn.disabled = true;
});
</script>
@endpush

@endsection
