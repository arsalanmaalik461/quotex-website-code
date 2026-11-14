@extends('layouts.platform')

@section('title', 'Market | Quotex')

@push('styles')
<style>
  /* Platform full width body container for Market */
  .page__body--full {
    padding: 0 !important;
    max-width: 100% !important;
  }

  .market-page-wrapper {
    background: #0f141e;
    padding: 24px 32px 40px 32px;
    min-height: calc(100vh - 120px);
    box-sizing: border-box;
    width: 100%;
  }

  .DckO3 {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
  }

  .market {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    width: 100%;
    box-sizing: border-box;
  }

  @media (max-width: 1280px) {
    .market {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 800px) {
    .market {
      grid-template-columns: 1fr;
    }
    .market-page-wrapper {
      padding: 16px 12px 32px 12px;
    }
  }

  /* Card Base */
  .market-card,
  .market-free-risk,
  .market-cashback,
  .market-deposit-bonus,
  .market-percentage,
  .market-balance,
  .market-сancel-point {
    background: #171d2b;
    border: 1px solid #232d3f;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    min-height: 380px;
    padding: 22px 24px;
    box-sizing: border-box;
    position: relative;
  }

  /* Card Header */
  .market-card__header,
  .market-free-risk__header,
  .market-cashback__header,
  .market-deposit-bonus__header,
  .market-percentage__header,
  .market-balance__header,
  .market-сancel-point__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 22px;
  }

  .market-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
  }

  .market-badge-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .market-badge-icon.blue   { background: #026FD3; }
  .market-badge-icon.purple { background: #9c27b0; }
  .market-badge-icon.orange { background: #ff6b00; }
  .market-badge-icon.pink   { background: #e91e63; }
  .market-badge-icon.sky    { background: #3b82f6; }
  .market-badge-icon.teal   { background: #00bcd4; }

  .market-card-title,
  .market-free-risk__header-title,
  .market-cashback__header-title,
  .market-deposit-bonus__header-title,
  .market-percentage__header-title,
  .market-balance__header-title,
  .market-сancel-point__header-title {
    font-size: 18px;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.2;
    margin-bottom: 3px;
  }

  .market-card-subtitle,
  .market-free-risk__header-subtitle,
  .market-cashback__header-subtitle,
  .market-deposit-bonus__header-subtitle,
  .market-percentage__header-subtitle,
  .market-balance__header-subtitle,
  .market-сancel-point__header-subtitle {
    font-size: 11px;
    font-weight: 700;
    color: #5f6f89;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .market-card-subtitle.green,
  .market-free-risk__header-subtitle.green,
  .market-cashback__header-subtitle.green,
  .market-deposit-bonus__header-subtitle.green,
  .market-percentage__header-subtitle.green,
  .market-balance__header-subtitle.green,
  .market-сancel-point__header-subtitle.green {
    color: #00e676 !important;
  }

  /* Tooltip Help Icon */
  .market-card-help,
  .market-free-risk__header-help,
  .market-cashback__header-help,
  .market-deposit-bonus__header-help,
  .market-percentage__header-help,
  .market-balance__header-help,
  .market-сancel-point__header-help {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #1f283a;
    border: 1px solid #2c3952;
    color: #5f6f89;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    position: relative;
    transition: all 0.2s;
    flex-shrink: 0;
  }

  .market-card-help:hover,
  .market-free-risk__header-help:hover,
  .market-cashback__header-help:hover,
  .market-deposit-bonus__header-help:hover,
  .market-percentage__header-help:hover,
  .market-balance__header-help:hover,
  .market-сancel-point__header-help:hover {
    color: #ffffff;
    border-color: #3f5172;
    background: #27344c;
  }

  .market-card-tooltip,
  .market-free-risk__header-tooltip,
  .market-cashback__header-tooltip,
  .market-deposit-bonus__header-tooltip,
  .market-percentage__header-tooltip,
  .market-balance__header-tooltip,
  .market-сancel-point__header-tooltip {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    bottom: 130%;
    right: 0;
    width: 250px;
    background: #10141e;
    border: 1px solid #2b384e;
    color: #b0c0d8;
    font-size: 12px;
    line-height: 1.45;
    padding: 10px 14px;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.65);
    z-index: 100;
    transition: opacity 0.2s, visibility 0.2s;
    pointer-events: none;
    font-weight: 400;
    text-transform: none;
    text-align: left;
  }

  .market-card-help:hover .market-card-tooltip,
  .market-free-risk__header-help:hover .market-free-risk__header-tooltip,
  .market-cashback__header-help:hover .market-cashback__header-tooltip,
  .market-deposit-bonus__header-help:hover .market-deposit-bonus__header-tooltip,
  .market-percentage__header-help:hover .market-percentage__header-tooltip,
  .market-balance__header-help:hover .market-balance__header-tooltip,
  .market-сancel-point__header-help:hover .market-сancel-point__header-tooltip {
    visibility: visible;
    opacity: 1;
  }

  /* Promocodes Table */
  .market-promocodes {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 180px;
  }

  .market-promocodes__header {
    display: grid;
    grid-template-columns: 1.3fr 1fr 0.7fr;
    padding: 0 6px 12px 6px;
    font-size: 11px;
    font-weight: 700;
    color: #5f6f89;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .market-promocodes__header .--end {
    text-align: right;
  }

  /* Empty State */
  .market-promocodes__not {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 10px;
    text-align: center;
  }

  .market-empty-box {
    margin-bottom: 14px;
    opacity: 0.8;
  }

  .market-promocodes__not-block {
    color: #5f6f89;
    font-size: 13px;
    line-height: 1.5;
    max-width: 260px;
  }

  /* Active Promocode List Items */
  .market-promocodes-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
  }

  .market-promocode {
    display: grid;
    grid-template-columns: 1.3fr 1fr 0.7fr;
    align-items: center;
    background: #131722;
    padding: 12px 14px;
    border-radius: 8px;
    border: 1px solid #1f2738;
    transition: background 0.15s, border-color 0.15s;
  }

  .market-promocode:hover {
    background: #161b27;
    border-color: #29344b;
  }

  .market-promocode__name {
    font-size: 13px;
    font-weight: 700;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .market-promocode__status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #8a98b0;
    font-weight: 500;
  }

  .status-check-circle {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #233044;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .market-promocode__using {
    text-align: right;
  }

  .market-link-use {
    color: #026FD3;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color 0.2s;
  }

  .market-link-use:hover {
    color: #2196f3;
  }

  /* Card Footer */
  .market-card__footer,
  .market-free-risk__footer,
  .market-cashback__footer,
  .market-deposit-bonus__footer,
  .market-percentage__footer,
  .market-balance__footer,
  .market-сancel-point__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 18px;
    border-top: 1px solid #1c2436;
  }

  .space_between {
    justify-content: space-between;
  }

  .opacity {
    opacity: 0.55;
    transition: opacity 0.2s;
  }

  .market-card:hover .opacity,
  .market-free-risk:hover .opacity,
  .market-cashback:hover .opacity,
  .market-deposit-bonus:hover .opacity,
  .market-percentage:hover .opacity,
  .market-balance:hover .opacity,
  .market-сancel-point:hover .opacity {
    opacity: 0.9;
  }

  .market-footer-history,
  .market-free-risk__footer-history,
  .market-cashback__footer-history,
  .market-deposit-bonus__footer-history,
  .market-percentage__footer-history,
  .market-balance__footer-history,
  .market-сancel-point__footer-history {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #687994;
    font-size: 13px;
    cursor: pointer;
    font-weight: 500;
    transition: color 0.2s;
  }

  .market-footer-history:hover,
  .market-free-risk__footer-history:hover,
  .market-cashback__footer-history:hover,
  .market-deposit-bonus__footer-history:hover,
  .market-percentage__footer-history:hover,
  .market-balance__footer-history:hover,
  .market-сancel-point__footer-history:hover {
    color: #b0c0d8;
  }

  /* Enter promo code button */
  .KtjVk.JQZcs._5qIw,
  .btn-enter-promo {
    background: #00c853;
    color: #ffffff;
    font-weight: 700;
    font-size: 13px;
    padding: 9px 18px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .KtjVk.JQZcs._5qIw:hover,
  .btn-enter-promo:hover {
    background: #00e676;
  }

  .KtjVk.JQZcs._5qIw:active,
  .btn-enter-promo:active {
    transform: scale(0.98);
  }

  /* Modal overlay for Enter Promo Code */
  .promo-modal-backdrop {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
  }
  .promo-modal-backdrop.open {
    display: flex;
  }
  .promo-modal-dialog {
    background: #171d2b;
    border: 1px solid #2a374f;
    border-radius: 12px;
    width: 90%;
    max-width: 440px;
    padding: 24px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.7);
    animation: promoModalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }
  @keyframes promoModalPop {
    0% { transform: scale(0.95); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
  }
  .promo-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
  }
  .promo-modal-title {
    font-size: 17px;
    font-weight: 700;
    color: #ffffff;
  }
  .promo-modal-close {
    background: none;
    border: none;
    color: #687994;
    font-size: 20px;
    cursor: pointer;
    line-height: 1;
  }
  .promo-modal-close:hover {
    color: #ffffff;
  }
  .promo-modal-input {
    width: 100%;
    background: #0f141e;
    border: 1px solid #232d3f;
    border-radius: 8px;
    padding: 12px 14px;
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    font-family: monospace;
    letter-spacing: 1px;
    box-sizing: border-box;
    margin-bottom: 16px;
    transition: border-color 0.2s;
  }
  .promo-modal-input:focus {
    outline: none;
    border-color: #026FD3;
  }
  .promo-modal-msg {
    font-size: 13px;
    margin-bottom: 16px;
    display: none;
    padding: 8px 12px;
    border-radius: 6px;
  }
  .promo-modal-msg.success {
    display: block;
    background: rgba(0, 200, 83, 0.15);
    color: #00e676;
    border: 1px solid rgba(0, 200, 83, 0.3);
  }
  .promo-modal-msg.error {
    display: block;
    background: rgba(255, 76, 106, 0.15);
    color: #ff4c6a;
    border: 1px solid rgba(255, 76, 106, 0.3);
  }
  .promo-modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
  }
  .promo-modal-btn-cancel {
    background: #232d3f;
    color: #b0c0d8;
    border: none;
    border-radius: 6px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
  }
  .promo-modal-btn-apply {
    background: #00c853;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 10px 22px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
  }

  /* Market purchase: Buy button on each promo row */
  .market-buy-btn {
    background: #00c853;
    color: #ffffff;
    font-weight: 700;
    font-size: 12px;
    padding: 7px 14px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-family: inherit;
    white-space: nowrap;
    transition: background 0.2s, transform 0.1s;
  }
  .market-buy-btn:hover {
    background: #00e676;
  }
  .market-buy-btn:active {
    transform: scale(0.98);
  }
  .market-buy-btn:disabled {
    cursor: default;
    transform: none;
  }
  .market-buy-btn.owned {
    background: #232d3f;
    color: #5f6f89;
  }
  .market-owned-tag {
    display: inline-block;
    background: rgba(0, 200, 83, 0.15);
    color: #00e676;
    border: 1px solid rgba(0, 200, 83, 0.3);
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    margin-left: 6px;
    vertical-align: middle;
  }
  .market-more-hint {
    color: #5f6f89;
    font-size: 12px;
    padding: 10px 6px 0 6px;
  }
  .market-promocode__using {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 6px;
  }

  /* "Show all" category catalogue modal */
  .promo-modal-dialog.wide {
    max-width: 560px;
  }
  #marketCategoryList {
    max-height: 60vh;
    overflow-y: auto;
  }
  .market-catalog-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    justify-content: space-between;
    background: #131722;
    border: 1px solid #1f2738;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 10px;
  }
  .market-catalog-name {
    font-size: 13px;
    font-weight: 700;
    color: #ffffff;
  }
  .market-catalog-status {
    font-size: 12px;
    color: #8a98b0;
    margin-top: 2px;
  }
  .market-catalog-grant {
    font-size: 12px;
    color: #8a98b0;
    line-height: 1.45;
    margin-top: 6px;
  }
  .market-catalog-action {
    flex-shrink: 0;
  }

  /* Purchase confirm modal */
  .market-purchase-summary {
    background: #131722;
    border: 1px solid #1f2738;
    border-radius: 8px;
    padding: 6px 14px;
    margin-bottom: 14px;
  }
  .market-purchase-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 9px 0;
    border-bottom: 1px solid #1c2436;
    font-size: 13px;
    color: #8a98b0;
  }
  .market-purchase-row:last-child {
    border-bottom: none;
  }
  .market-purchase-row strong {
    color: #ffffff;
    font-weight: 700;
  }
  .market-purchase-grant {
    background: rgba(2, 111, 211, 0.12);
    border: 1px solid rgba(2, 111, 211, 0.35);
    color: #b0c0d8;
    font-size: 13px;
    line-height: 1.5;
    padding: 10px 14px;
    border-radius: 8px;
    margin-bottom: 16px;
  }
</style>
@endpush

@section('content')
<div class="market-page-wrapper">
  <div class="DckO3">
    <div class="market">
      
      @php
        $cardConfigs = [
          'risk_free' => [
            'class'   => 'market-free-risk',
            'badge'   => 'blue',
            'title'   => 'Risk Free',
            'tooltip' => 'Cancel one losing trade within the bonus limit, if the trade has no bonus funds.',
            'icon'    => 'shield',
          ],
          'cashback' => [
            'class'   => 'market-cashback',
            'badge'   => 'purple',
            'title'   => 'Cashback',
            'tooltip' => 'Get a partial refund of losses at the end of the bonus period. No cashback is credited if the result is profitable.',
            'icon'    => 'cashback',
          ],
          'deposit_bonus' => [
            'class'   => 'market-deposit-bonus',
            'badge'   => 'orange',
            'title'   => 'Deposit Bonus',
            'tooltip' => 'Receive an extra percentage on your deposit. Bonus size and limits depend on the promo code.',
            'icon'    => 'deposit',
          ],
          'turnover_percent' => [
            'class'   => 'market-percentage',
            'badge'   => 'pink',
            'title'   => 'Percentage of turnover',
            'tooltip' => 'Get a percentage of the total amount of all trades during the bonus period. Credited once after the bonus expires.',
            'icon'    => 'percent',
          ],
          'balance_bonus' => [
            'class'   => 'market-balance',
            'badge'   => 'sky',
            'title'   => 'Balance Bonus',
            'tooltip' => 'Instantly get bonus funds to your real balance with no turnover requirements.',
            'icon'    => 'balance',
          ],
          'cancel_points' => [
            'class'   => 'market-сancel-point',
            'badge'   => 'teal',
            'title'   => 'Cancel X points',
            'tooltip' => 'Losing trades within the specified number of points are canceled automatically.<br><br>The number of points depends on the promo code.',
            'icon'    => 'cancel',
          ],
        ];
      @endphp

      @foreach($cardConfigs as $catKey => $config)
        @php
          $items = $activePromos[$catKey] ?? collect([]);
          $count = $items->count();
          $hasCodes = $count > 0;
        @endphp

        <div class="{{ $config['class'] }}">
          {{-- Header --}}
          <div class="{{ $config['class'] }}__header">
            <div class="market-header-left">
              <div class="market-badge-icon {{ $config['badge'] }}">
                @if($config['icon'] === 'shield')
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                  </svg>
                @elseif($config['icon'] === 'cashback')
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.5 2v6h-6"/>
                    <path d="M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                  </svg>
                @elseif($config['icon'] === 'deposit')
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                  </svg>
                @elseif($config['icon'] === 'percent')
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="5" x2="5" y2="19"/>
                    <circle cx="6.5" cy="6.5" r="2.5"/>
                    <circle cx="17.5" cy="17.5" r="2.5"/>
                  </svg>
                @elseif($config['icon'] === 'balance')
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 12 20 22 4 22 4 12"/>
                    <rect x="2" y="7" width="20" height="5"/>
                    <line x1="12" y1="22" x2="12" y2="7"/>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                  </svg>
                @elseif($config['icon'] === 'cancel')
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="3" ry="3"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                  </svg>
                @endif
              </div>

              <div>
                <div class="{{ $config['class'] }}__header-title">
                  {{ $config['title'] }}
                </div>
                <div class="{{ $config['class'] }}__header-subtitle {{ $hasCodes ? 'green' : '' }}">
                  {{ $count }} promo code{{ $count === 1 ? '' : 's' }} available
                </div>
              </div>
            </div>

            <div class="{{ $config['class'] }}__header-help">
              <span>?</span>
              <div class="{{ $config['class'] }}__header-tooltip">
                {!! $config['tooltip'] !!}
              </div>
            </div>
          </div>

          {{-- Promocodes list / Empty box --}}
          <div class="market-promocodes">
            <div class="market-promocodes__header">
              <div>Promo code</div>
              <div>Status</div>
              <div class="--end">Using</div>
            </div>

            @if($hasCodes)
              <div class="market-promocodes-list">
                @foreach($items->take(3) as $promo)
                  @php
                    $owned = in_array($promo->id, $ownedPromoIds ?? []);
                    $price = $promo->priceAmount();
                  @endphp
                  <div class="market-promocode active">
                    <div class="market-promocode__name">
                      {{ $promo->formatted_name }}
                      @if($owned)<span class="market-owned-tag">Owned</span>@endif
                    </div>
                    <div class="market-promocode__status">
                      <span class="status-check-circle">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                      </span>
                      <span>{{ $promo->formatted_status }}</span>
                    </div>
                    <div class="market-promocode__using active">
                      <button type="button" class="market-buy-btn {{ $owned ? 'owned' : '' }}" {{ $owned ? 'disabled' : '' }} onclick="openPurchaseModal({{ $promo->id }})">{{ $owned ? 'Owned' : ($price > 0 ? 'Buy $' . number_format($price, 2) : 'Get free') }}</button>
                      @if($promo->category === 'deposit_bonus')
                        <a href="{{ route('deposit', ['promo' => $promo->code]) }}" class="market-link-use">
                          <span>Use it</span>
                          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
              @if($count > 3)
                <div class="market-more-hint">+{{ $count - 3 }} more in this category — tap "Show all" below to see them.</div>
              @endif
            @else
              <div class="market-promocodes__not">
                <div class="market-empty-box">
                  <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="#36445d" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                    <path d="m3.3 7 8.7 5 8.7-5"/>
                    <path d="M12 22V12"/>
                  </svg>
                </div>
                <div class="market-promocodes__not-block">
                  You don't have a promo code history yet. You can add a promo code using the button below.
                </div>
              </div>
            @endif
          </div>

          {{-- Footer --}}
          <div class="{{ $config['class'] }}__footer space_between {{ $hasCodes ? '' : 'opacity' }}">
            <div class="{{ $config['class'] }}__footer-history pointer" onclick="openCategoryModal('{{ $catKey }}', '{{ $config['title'] }}')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>Show all</span>
            </div>
            <button type="button" class="KtjVk JQZcs _5qIw" onclick="openPromoModal()">
              <span class="oQ4Z4">
                Enter promo code
              </span>
            </button>
          </div>
        </div>
      @endforeach

    </div>
  </div>
</div>

{{-- Enter Promo Code Modal --}}
<div class="promo-modal-backdrop" id="promoCodeModal">
  <div class="promo-modal-dialog">
    <div class="promo-modal-header">
      <div class="promo-modal-title">🎁 Enter Promo Code</div>
      <button type="button" class="promo-modal-close" onclick="closePromoModal()">&times;</button>
    </div>
    
    <div class="promo-modal-body">
      <p style="color:#8a98b0; font-size:13px; margin-top:0; margin-bottom:14px; line-height:1.4;">
        Enter your promo code to activate bonus deposit rewards or risk-free features on your account.
      </p>

      <div id="promoModalMsg" class="promo-modal-msg"></div>

      <input type="text" id="promoInputCode" class="promo-modal-input" placeholder="e.g. DEPOSIT50" maxlength="40" autocomplete="off" autofocus>
      
      <div class="promo-modal-actions">
        <button type="button" class="promo-modal-btn-cancel" onclick="closePromoModal()">Cancel</button>
        <button type="button" class="promo-modal-btn-apply" id="btnSubmitPromo" onclick="submitPromoCode()">Activate Code</button>
      </div>
    </div>
  </div>
</div>

<script>
  function openPromoModal() {
    const modal = document.getElementById('promoCodeModal');
    const input = document.getElementById('promoInputCode');
    const msg = document.getElementById('promoModalMsg');
    msg.className = 'promo-modal-msg';
    msg.style.display = 'none';
    msg.innerText = '';
    modal.classList.add('open');
    setTimeout(() => { input.focus(); }, 100);
  }

  function closePromoModal() {
    document.getElementById('promoCodeModal').classList.remove('open');
  }

  document.getElementById('promoCodeModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closePromoModal();
    }
  });

  document.getElementById('promoInputCode').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      submitPromoCode();
    }
  });

  function submitPromoCode() {
    const input = document.getElementById('promoInputCode');
    const btn = document.getElementById('btnSubmitPromo');
    const msg = document.getElementById('promoModalMsg');
    const code = input.value.trim().toUpperCase();

    if (!code) {
      msg.className = 'promo-modal-msg error';
      msg.innerText = 'Please enter a promo code.';
      msg.style.display = 'block';
      return;
    }

    btn.disabled = true;
    btn.innerText = 'Checking...';

    fetch('{{ route("market.apply") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ code: code })
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(({ status, body }) => {
      btn.disabled = false;
      btn.innerText = 'Activate Code';

      if (status === 200 && body.success) {
        msg.className = 'promo-modal-msg success';
        msg.innerText = body.message;
        msg.style.display = 'block';

        setTimeout(() => {
          if (body.redirect) {
            window.location.href = body.redirect;
          } else {
            window.location.reload();
          }
        }, 1200);
      } else {
        msg.className = 'promo-modal-msg error';
        msg.innerText = body.message || 'Invalid or expired promo code.';
        msg.style.display = 'block';
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerText = 'Activate Code';
      msg.className = 'promo-modal-msg error';
      msg.innerText = 'Error connecting to server. Please try again.';
      msg.style.display = 'block';
    });
  }
</script>

{{-- "Show all" category catalogue modal: every promo code in one category --}}
<div class="promo-modal-backdrop" id="marketCategoryModal">
  <div class="promo-modal-dialog wide">
    <div class="promo-modal-header">
      <div class="promo-modal-title" id="marketCategoryTitle">Market</div>
      <button type="button" class="promo-modal-close" onclick="closeCategoryModal()">&times;</button>
    </div>
    <div id="marketCategoryList"></div>
  </div>
</div>

{{-- Purchase confirm modal: centered in-app confirm, never a native dialog --}}
<div class="promo-modal-backdrop" id="marketPurchaseModal">
  <div class="promo-modal-dialog">
    <div class="promo-modal-header">
      <div class="promo-modal-title" id="marketPurchaseTitle">Buy promo code</div>
      <button type="button" class="promo-modal-close" onclick="closePurchaseModal()">&times;</button>
    </div>
    <div class="promo-modal-body">
      <div id="marketPurchaseMsg" class="promo-modal-msg"></div>

      <div class="market-purchase-summary">
        <div class="market-purchase-row"><span>Item</span><strong id="mpItem"></strong></div>
        <div class="market-purchase-row"><span>Price</span><strong id="mpPrice"></strong></div>
        <div class="market-purchase-row"><span>Pay from</span><strong>Live balance</strong></div>
        <div class="market-purchase-row"><span>Live balance</span><strong id="mpBalance"></strong></div>
        <div class="market-purchase-row"><span>Balance after purchase</span><strong id="mpAfter"></strong></div>
      </div>

      <div class="market-purchase-grant" id="mpGrant"></div>

      <div class="promo-modal-actions">
        <button type="button" class="promo-modal-btn-cancel" onclick="closePurchaseModal()">Cancel</button>
        <button type="button" class="promo-modal-btn-apply" id="btnConfirmPurchase" onclick="confirmPurchase()">Confirm purchase</button>
      </div>
    </div>
  </div>
</div>

@php
  // Catalogue for the "Show all" modal and the purchase modal, rendered
  // once server-side so the modals never need an extra request.
  $marketCatalog = [];
  foreach ($activePromos as $cat => $promos) {
    $marketCatalog[$cat] = $promos->map(function ($p) use ($ownedPromoIds) {
      return [
        'id'       => $p->id,
        'code'     => $p->code,
        'name'     => $p->formatted_name,
        'status'   => $p->formatted_status,
        'price'    => $p->priceAmount(),
        'grant'    => $p->grantDescription(),
        'owned'    => in_array($p->id, $ownedPromoIds ?? []),
        'category' => $p->category,
      ];
    })->values()->all();
  }
@endphp

<script>
  // Full Market catalogue (all active promo codes, grouped by category).
  const MARKET_CATALOG = @json($marketCatalog);
  // Live balance at page load; refreshed from the purchase response.
  let MARKET_LIVE_BALANCE = {{ (float) ($user->live_balance ?? 0) }};
  // The purchase currently being confirmed (id + one idempotency key per
  // confirm-modal open, so a rapid double-click replays the same key and
  // the server returns the first purchase instead of charging twice).
  let MARKET_CURRENT_PURCHASE = null;

  function marketMoney(n) {
    return '$' + Number(n).toFixed(2);
  }

  function marketUuid() {
    if (window.crypto && crypto.randomUUID) {
      return crypto.randomUUID();
    }
    return 'mp-' + Date.now() + '-' + Math.random().toString(16).slice(2);
  }

  function marketEscapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function marketFindItem(id) {
    for (const cat of Object.keys(MARKET_CATALOG)) {
      const found = MARKET_CATALOG[cat].find(item => item.id === id);
      if (found) {
        return found;
      }
    }
    return null;
  }

  function marketBuyButtonHtml(item) {
    if (item.owned) {
      return '<button type="button" class="market-buy-btn owned" disabled>Owned</button>';
    }
    const label = item.price > 0 ? 'Buy ' + marketMoney(item.price) : 'Get free';
    return '<button type="button" class="market-buy-btn" onclick="openPurchaseModal(' + item.id + ')">' + label + '</button>';
  }

  // "Show all": list EVERY promo code in this category, with its price,
  // status, what it grants, and a Buy button.
  function openCategoryModal(catKey, title) {
    const items = MARKET_CATALOG[catKey] || [];
    document.getElementById('marketCategoryTitle').innerText = title + ' — all promo codes';
    const list = document.getElementById('marketCategoryList');

    if (!items.length) {
      list.innerHTML = '<p style="color:#8a98b0; font-size:13px;">No promo codes in this category yet.</p>';
    } else {
      list.innerHTML = items.map(item => {
        return '<div class="market-catalog-item">'
          + '<div class="market-catalog-info">'
          + '<div class="market-catalog-name">' + marketEscapeHtml(item.name)
          + (item.owned ? ' <span class="market-owned-tag">Owned</span>' : '') + '</div>'
          + '<div class="market-catalog-status">' + marketEscapeHtml(item.status) + '</div>'
          + '<div class="market-catalog-grant">' + marketEscapeHtml(item.grant) + '</div>'
          + '</div>'
          + '<div class="market-catalog-action">' + marketBuyButtonHtml(item) + '</div>'
          + '</div>';
      }).join('');
    }

    document.getElementById('marketCategoryModal').classList.add('open');
  }

  function closeCategoryModal() {
    document.getElementById('marketCategoryModal').classList.remove('open');
  }

  // Open the centered in-app purchase confirm modal for one item.
  function openPurchaseModal(promoId) {
    const item = marketFindItem(promoId);
    if (!item) {
      return;
    }

    MARKET_CURRENT_PURCHASE = { id: item.id, key: marketUuid() };
    closeCategoryModal();

    document.getElementById('marketPurchaseTitle').innerText = 'Buy ' + item.code;
    document.getElementById('mpItem').innerText = item.name;
    document.getElementById('mpPrice').innerText = marketMoney(item.price);
    document.getElementById('mpBalance').innerText = marketMoney(MARKET_LIVE_BALANCE);

    const after = MARKET_LIVE_BALANCE - item.price;
    const afterEl = document.getElementById('mpAfter');
    afterEl.innerText = marketMoney(after);
    afterEl.style.color = after < 0 ? '#ff4c6a' : '#ffffff';

    document.getElementById('mpGrant').innerText = item.grant;

    const msg = document.getElementById('marketPurchaseMsg');
    msg.className = 'promo-modal-msg';
    msg.style.display = 'none';
    msg.innerText = '';

    const btn = document.getElementById('btnConfirmPurchase');
    btn.disabled = false;
    btn.innerText = 'Confirm purchase';

    document.getElementById('marketPurchaseModal').classList.add('open');
  }

  function closePurchaseModal() {
    document.getElementById('marketPurchaseModal').classList.remove('open');
  }

  function confirmPurchase() {
    if (!MARKET_CURRENT_PURCHASE) {
      return;
    }

    const btn = document.getElementById('btnConfirmPurchase');
    const msg = document.getElementById('marketPurchaseMsg');

    btn.disabled = true;
    btn.innerText = 'Processing...';

    fetch('{{ route("market.purchase") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        promo_code_id: MARKET_CURRENT_PURCHASE.id,
        idempotency_key: MARKET_CURRENT_PURCHASE.key
      })
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(({ status, body }) => {
      btn.disabled = false;
      btn.innerText = 'Confirm purchase';

      if (status === 200 && body.success) {
        msg.className = 'promo-modal-msg success';
        msg.innerText = body.message
          + (body.granted ? ' ' + body.granted : '')
          + ' New live balance: ' + marketMoney(body.new_balance) + '.';
        msg.style.display = 'block';
        MARKET_LIVE_BALANCE = body.new_balance;

        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        msg.className = 'promo-modal-msg error';
        msg.innerText = body.message || 'Purchase failed. Please try again.';
        msg.style.display = 'block';
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerText = 'Confirm purchase';
      msg.className = 'promo-modal-msg error';
      msg.innerText = 'Error connecting to server. Please try again.';
      msg.style.display = 'block';
    });
  }

  document.getElementById('marketCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeCategoryModal();
    }
  });

  document.getElementById('marketPurchaseModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closePurchaseModal();
    }
  });
</script>
@endsection
