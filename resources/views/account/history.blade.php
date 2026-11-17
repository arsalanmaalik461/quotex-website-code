@extends('layouts.platform')

@section('title', ($isPending ?? false) ? 'Pending trades | Quotex' : 'Trade history | Quotex')

@push('styles')
<style>
/* Quotex Exact Trade History Page Styling */
.page__body,
.page__body--full {
  max-width: 100% !important;
  width: 100% !important;
  margin: 0 !important;
  padding: 20px 32px 48px 32px !important;
  box-sizing: border-box !important;
  background-color: #0f141e !important;
  color: #fff;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
}

.DckO3 {
  width: 100% !important;
  max-width: 1440px !important;
  margin: 0 auto !important;
  padding: 0 !important;
  box-sizing: border-box !important;
}

/* Sub-navigation tabs: Trade history / Pending trades */
.uUTKR {
  display: flex;
  align-items: center;
  gap: 32px;
  border-bottom: 1px solid #232d3f;
  margin-bottom: 24px;
  padding-bottom: 0px;
}

.uUTKR ._5zzD {
  color: #78869e;
  font-size: 15px;
  font-weight: 600;
  padding: 12px 4px 16px;
  text-decoration: none;
  border-bottom: 2px solid transparent;
  transition: color 0.2s ease, border-color 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.uUTKR ._5zzD:hover {
  color: #ffffff;
}

.uUTKR ._5zzD.active,
.uUTKR ._5zzD.qMbLp {
  color: #ffffff;
  border-bottom-color: #0074e4;
}

.tab-badge {
  background: #232d3f;
  color: #94a3b8;
  font-size: 11px;
  padding: 2px 7px;
  border-radius: 10px;
}

/* Filter controls bar: Date, Account, Export, Pagination */
.K6V4a {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 20px;
}

.K6V4a-left {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

/* Date range picker */
.date-picker-range__root {
  background: #171d2b;
  border: 1px solid #232d3f;
  border-radius: 8px;
  padding: 6px 14px;
  display: inline-flex;
  align-items: center;
  box-sizing: border-box;
}

.date-picker-range__container {
  display: flex;
  align-items: center;
  gap: 8px;
}

.date-picker-range__label {
  color: #63738e;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
}

.react-datepicker__input-container input {
  background: transparent;
  border: none;
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
  outline: none;
  width: 190px;
  cursor: pointer;
}

/* Real date-range inputs (native date pickers) + Apply/Clear. */
.date-range-inputs {
  display: flex;
  align-items: center;
  gap: 6px;
}

.date-range-inputs input[type="date"] {
  background: #171d2b;
  border: 1px solid #232d3f;
  border-radius: 8px;
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
  outline: none;
  padding: 7px 10px;
  color-scheme: dark;
}

.date-range-inputs input[type="date"]:focus {
  border-color: #026FD3;
}

.date-range-sep {
  color: #63738e;
  font-size: 13px;
}

.date-range-apply-btn {
  background: #026FD3;
  border: none;
  border-radius: 8px;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  padding: 8px 14px;
  cursor: pointer;
}

.date-range-apply-btn:hover {
  background: #007bf8;
}

.date-range-clear-btn {
  color: #8fa0b5;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  white-space: nowrap;
}

.date-range-clear-btn:hover {
  color: #fff;
}

/* Account selector */
.tc1BY {
  position: relative;
}

.kFF7G.eJuLa {
  background: #171d2b;
  border: 1px solid #232d3f;
  border-radius: 8px;
  padding: 8px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
  user-select: none;
  transition: border-color 0.2s;
  min-width: 190px;
  justify-content: space-between;
}

.kFF7G.eJuLa:hover {
  border-color: #3b4b66;
}

.kFF7G.eJuLa .olsfR {
  color: #63738e;
  font-size: 12px;
  font-weight: 500;
  margin-right: 4px;
}

.account-type-dropdown-menu {
  display: none;
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  width: 100%;
  background: #171d2b;
  border: 1px solid #232d3f;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.5);
  z-index: 100;
  overflow: hidden;
}

.account-type-dropdown-menu.open {
  display: block;
}

.acc-opt-item {
  padding: 10px 16px;
  color: #cbd5e1;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  text-decoration: none;
  display: block;
}

.acc-opt-item:hover {
  background: #232d3f;
  color: #ffffff;
}

.acc-opt-item.active {
  color: #0074e4;
  font-weight: 700;
  background: rgba(0, 116, 228, 0.08);
}

/* Export button */
.P4CNJ.bZWOo .Xc2cf {
  background: #171d2b;
  border: 1px solid #232d3f;
  border-radius: 8px;
  padding: 8px 18px;
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  transition: background 0.2s, border-color 0.2s;
}

.P4CNJ.bZWOo .Xc2cf:hover {
  background: #202838;
  border-color: #3b4b66;
}

/* Pagination controls */
.Z3ZjJ, .iZaxY {
  display: flex;
  align-items: center;
  gap: 6px;
}

.iZaxY {
  margin-top: 24px;
  justify-content: flex-end;
}

.THTtN.wH2oT {
  background: #171d2b;
  border: 1px solid #232d3f;
  border-radius: 6px;
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 600;
  color: #ffffff;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s, border-color 0.2s;
}

.THTtN.wH2oT:hover:not(.DLvqx) {
  background: #232d3f;
  border-color: #3b4b66;
}

.THTtN.wH2oT.DLvqx,
.THTtN.wH2oT.disabled {
  opacity: 0.35;
  cursor: not-allowed;
  pointer-events: none;
}

.f8uhl {
  color: #78869e;
  font-size: 13px;
  font-weight: 600;
  padding: 0 8px;
}

/* The Table Container */
.Ah8Om {
  background: #171d2b;
  border: 1px solid #232d3f;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
}

/* Table Header (Desktop) */
.rclm9 {
  display: grid;
  grid-template-columns: 2fr 1.6fr 0.8fr 1.6fr 1.6fr 0.6fr 1.2fr 1.2fr;
  padding: 14px 24px;
  background: #131822;
  border-bottom: 1px solid #232d3f;
  color: #63738e;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

/* Mobile Header (Hidden on desktop) */
.Z859i {
  display: none;
}

/* Table Row */
.bQvOA {
  display: grid;
  grid-template-columns: 2fr 1.6fr 0.8fr 1.6fr 1.6fr 0.6fr 1.2fr 1.2fr;
  padding: 14px 24px;
  border-bottom: 1px solid #1c2534;
  align-items: center;
  font-size: 13px;
  transition: background 0.15s ease;
}

.bQvOA:hover {
  background: #1c2333;
}

.bQvOA:last-child {
  border-bottom: none;
}

/* Asset column */
.Z6JNP {
  display: flex;
  align-items: center;
  gap: 10px;
}

.asset-symbol-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.asset-flag {
  font-size: 18px;
  line-height: 1;
}

.Z6JNP b {
  color: #ffffff;
  font-weight: 700;
  font-size: 13.5px;
}

/* Info column */
.tA1xK {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.wOyXY {
  font-size: 13px;
  font-weight: 700;
  color: #ffffff;
}

.YHOFT {
  font-size: 11px;
  color: #53627a;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 140px;
}

/* Chart column */
.chart-col-cell {
  display: flex;
  align-items: center;
}

.chart-icon-link {
  color: #63738e;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
  border-radius: 6px;
  transition: color 0.15s, background 0.15s;
  text-decoration: none;
}

.chart-icon-link:hover {
  color: #0074e4;
  background: rgba(0, 116, 228, 0.1);
}

/* Quote columns */
.sDT1M, .yaLys {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.rFjCr, .yaLys > div:first-child {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
}

/* IP column */
.IbHql {
  color: #53627a;
  font-weight: 500;
  font-size: 13px;
}

/* Amount & Profit column */
.dj8M9 {
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 4px;
}

.dj8M9.uLCSj {
  font-size: 13px;
}

.dj8M9.fTGaJ {
  font-size: 14px;
}

/* Win Green / Loss Red */
.cKYQK, .cKYQK b {
  color: #00b977 !important;
}

.rSpgN, .rSpgN b {
  color: #ff3355 !important;
}

.dir-indicator {
  font-size: 14px;
  font-weight: 800;
}

/* Mobile Row Elements - hidden on desktop */
.IMdQw, .leFd4, .TQG8A, .g4YxO {
  display: none;
}

/* Empty State */
.empty-trades-wrap {
  padding: 64px 24px;
  text-align: center;
}

.empty-trades-icon {
  font-size: 40px;
  margin-bottom: 12px;
  opacity: 0.6;
}

.empty-trades-title {
  font-size: 16px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 8px;
}

.empty-trades-text {
  font-size: 13px;
  color: #63738e;
  margin-bottom: 20px;
}

.empty-trades-btn {
  display: inline-block;
  background: #0074e4;
  color: #fff;
  font-weight: 700;
  font-size: 13px;
  padding: 10px 24px;
  border-radius: 8px;
  text-decoration: none;
  transition: background 0.2s;
}

.empty-trades-btn:hover {
  background: #0060be;
}

/* Responsive Breakpoints for Mobile */
@media (max-width: 960px) {
  .rclm9 {
    display: none;
  }

  .Z859i {
    display: grid;
    grid-template-columns: 1.2fr 1.5fr 1fr;
    padding: 12px 16px;
    background: #131822;
    border-bottom: 1px solid #232d3f;
    color: #63738e;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
  }

  .bQvOA {
    display: grid;
    grid-template-columns: 1.2fr 1.5fr 1fr;
    padding: 14px 16px;
    gap: 12px;
  }

  /* Hide desktop columns */
  .bQvOA > .Z6JNP,
  .bQvOA > .tA1xK,
  .bQvOA > .chart-col-cell,
  .bQvOA > .sDT1M,
  .bQvOA > .yaLys,
  .bQvOA > .IbHql,
  .bQvOA > .dj8M9.uLCSj,
  .bQvOA > .dj8M9.fTGaJ {
    display: none;
  }

  /* Show mobile structures */
  .IMdQw {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .QEiO2 b {
    color: #ffffff;
    font-size: 13px;
  }

  .aYU1j {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 11px;
  }

  .aYU1j span:first-child {
    color: #ffffff;
    font-weight: 700;
  }

  .wiNHN {
    color: #53627a;
    font-size: 11px;
  }

  .leFd4, .TQG8A {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 11px;
  }

  .vjfFl, .O07P7 {
    color: #63738e;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
  }

  .leFd4 span, .TQG8A span {
    font-family: monospace;
    font-weight: 600;
    color: #ffffff;
  }

  .dOyf4 {
    color: #63738e;
    font-size: 10px;
  }

  .g4YxO {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
  }
}
</style>
@endpush

@section('content')
<div class="page__body page__body--full" dir="ltr">
  <div class="DckO3">
    
    <!-- Sub-navigation tabs: Trade history / Pending trades -->
    <div class="uUTKR">
      <a class="_5zzD {{ !($isPending ?? false) ? 'qMbLp active' : '' }}" href="{{ route('trades') }}?account={{ $accountType ?? 'demo' }}">
        Trade history
      </a>
      <a class="_5zzD {{ ($isPending ?? false) ? 'qMbLp active' : '' }}" href="{{ route('pending-trades') }}?account={{ $accountType ?? 'demo' }}">
        Pending trades
        @if(isset($pendingCount) && $pendingCount > 0)
          <span class="tab-badge">{{ $pendingCount }}</span>
        @endif
      </a>
    </div>

    <!-- Filter row: Date Range, Account Type, Export, Pagination -->
    <div class="K6V4a">
      <div class="K6V4a-left">
        <!-- Date Range Filter: two real date inputs that submit
             startDate/endDate to the backend (which already filters
             on them). The old readonly text input had no datepicker
             and no way to set the dates at all. -->
        <form method="GET" action="{{ route('trades') }}" class="date-picker-range__root">
          <input type="hidden" name="account" value="{{ $accountType ?? 'demo' }}">
          <div class="date-picker-range__container">
            <div class="date-picker-range__label">
              Date Range:
            </div>
            <div class="date-range-inputs">
              <input type="date" id="startDateInput" name="startDate"
                value="{{ $startDate ?? '' }}"
                max="{{ now()->format('Y-m-d') }}"
                aria-label="Start date">
              <span class="date-range-sep">–</span>
              <input type="date" id="endDateInput" name="endDate"
                value="{{ $endDate ?? '' }}"
                max="{{ now()->format('Y-m-d') }}"
                aria-label="End date">
            </div>
            <button type="submit" class="date-range-apply-btn">Apply</button>
            @if(($startDate ?? '') || ($endDate ?? ''))
              <a href="{{ route('trades', ['account' => $accountType ?? 'demo']) }}" class="date-range-clear-btn">Clear</a>
            @endif
          </div>
        </form>

        <!-- Account Type Dropdown -->
        <div class="tc1BY">
          <div class="kFF7G eJuLa" id="accountTypeToggle" tabindex="0" role="combobox" aria-expanded="false">
            <div>
              <label class="olsfR">Account Type:</label>
              <span id="accountCurrentText">{{ ($accountType ?? 'demo') === 'live' ? 'Live Account' : 'Demo Account' }}</span>
            </div>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </div>
          <div class="account-type-dropdown-menu" id="accountTypeMenu">
            <a href="{{ request()->fullUrlWithQuery(['account' => 'demo', 'page' => 1]) }}" class="acc-opt-item {{ ($accountType ?? 'demo') === 'demo' ? 'active' : '' }}">
              Demo Account
            </a>
            <a href="{{ request()->fullUrlWithQuery(['account' => 'live', 'page' => 1]) }}" class="acc-opt-item {{ ($accountType ?? 'demo') === 'live' ? 'active' : '' }}">
              Live Account
            </a>
          </div>
        </div>

        <!-- Export to CSV button -->
        <div class="P4CNJ bZWOo">
          <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="Xc2cf" title="Download trades history as CSV">
            <div class="z4ZwJ">
              Export to CSV
            </div>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
          </a>
        </div>
      </div>

      <!-- Top Pagination -->
      <div class="Z3ZjJ" aria-label="Pagination">
        <a href="{{ $trades->previousPageUrl() ?? 'javascript:void(0)' }}" 
           class="THTtN wH2oT {{ $trades->onFirstPage() ? 'DLvqx disabled' : 'ovrkj' }}" 
           aria-label="Prev">
          <span class="PnkSX">Prev</span>
        </a>
        <div class="f8uhl" aria-live="polite" aria-atomic="true" aria-label="Page {{ $trades->currentPage() }} of {{ max(1, $trades->lastPage()) }}">
          <span aria-hidden="true">{{ $trades->currentPage() }}/{{ max(1, $trades->lastPage()) }}</span>
        </div>
        <a href="{{ $trades->nextPageUrl() ?? 'javascript:void(0)' }}" 
           class="THTtN wH2oT {{ $trades->hasMorePages() ? 'ovrkj' : 'DLvqx disabled' }}" 
           aria-label="Next">
          <span class="PnkSX">Next</span>
        </a>
      </div>
    </div>

    <!-- Table Card Container -->
    <div class="Ah8Om">
      <div>
        <!-- Desktop Table Header -->
        <div class="rclm9">
          <div>Asset</div>
          <div>Info</div>
          <div>Chart</div>
          <div>Opening quote</div>
          <div>Closing quote</div>
          <div>IP</div>
          <div>Amount</div>
          <div>Profit</div>
        </div>

        <!-- Mobile Table Header -->
        <div class="Z859i">
          <div>Asset/Amount/IP</div>
          <div>Opening / Closing quotes</div>
          <div>Amount/Profit</div>
        </div>

        <!-- Rows Container -->
        <div>
          @forelse($trades as $t)
            @php
              $isWin = ($t->result === 'win');
              $isPendingTrade = ($t->result === 'pending');
              $isDraw = ($t->result === 'draw' || $t->result === 'tie');
              $profitColorClass = $isWin ? 'cKYQK' : (($isPendingTrade || $isDraw) ? '' : 'rSpgN');
              $tradeUuid = $t->uuid ?? sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
            @endphp
            <div class="bQvOA">
              <!-- 1. Asset Column -->
              <div class="Z6JNP">
                <div class="asset-symbol-wrap">
                  <span class="asset-flag">{{ $t->asset->flag_icon ?? '🌐' }}</span>
                  <b>{{ $t->asset->symbol ?? 'USD/JPY (OTC)' }}</b>
                </div>
              </div>

              <!-- 2. Info Column -->
              <div class="tA1xK">
                <div class="wOyXY">
                  {{ $t->asset->payout_rate ?? 90 }}%
                </div>
                <div class="YHOFT" title="{{ $tradeUuid }}">
                  {{ $tradeUuid }}
                </div>
              </div>

              <!-- 3. Chart Column -->
              <div class="chart-col-cell">
                <a href="{{ route('trade') }}?asset={{ $t->asset_id }}" class="chart-icon-link" title="Open chart for {{ $t->asset->symbol ?? 'asset' }}">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3v18h18"/>
                    <path d="m19 9-5 5-4-4-3 3"/>
                  </svg>
                </a>
              </div>

              <!-- 4. Opening Quote Column -->
              <div class="sDT1M">
                <div class="rFjCr">
                  <div>{{ number_format((float)($t->open_price ?? 0), 3) }}</div>
                </div>
                <div class="YHOFT">
                  {{ $t->opened_at ? $t->opened_at->format('d/m/Y, H:i:s') : $t->created_at->format('d/m/Y, H:i:s') }}
                </div>
              </div>

              <!-- 5. Closing Quote Column -->
              <div class="yaLys">
                <div>
                  {{ $t->close_price ? number_format((float)$t->close_price, 3) : ($isPendingTrade ? 'In progress' : '—') }}
                </div>
                <div class="YHOFT">
                  {{ $t->closed_at ? $t->closed_at->format('d/m/Y, H:i:s') : ($isPendingTrade ? 'Pending expiry' : '—') }}
                </div>
              </div>

              <!-- 6. IP Column -->
              <div class="IbHql">
                —
              </div>

              <!-- 7. Amount Column -->
              <div class="dj8M9 uLCSj {{ $profitColorClass }}">
                <b>
                  <span class="dir-indicator">{{ $t->direction === 'up' ? '↑' : '↓' }}</span>
                  {{ number_format((float)$t->investment, 2) }}$
                </b>
              </div>

              <!-- 8. Profit Column -->
              <div class="dj8M9 fTGaJ {{ $profitColorClass }}">
                <b>
                  @if($isPendingTrade)
                    <span style="color:#78869e;">Pending</span>
                  @else
                    @php $rowProfit = \App\Services\TradeResultService::settledProfit($t); @endphp
                    {{ ($rowProfit > 0 ? '+' : ($rowProfit < 0 ? '-' : '')) . number_format(abs($rowProfit), 2) }}$
                  @endif
                </b>
              </div>

              <!-- Mobile View Blocks -->
              <div class="IMdQw">
                <div class="QEiO2">
                  <b>{{ $t->asset->symbol ?? 'USD/JPY (OTC)' }}</b>
                </div>
                <div class="aYU1j">
                  <span>{{ $t->asset->payout_rate ?? 90 }}%</span>
                  <span class="YHOFT">{{ substr($tradeUuid, 0, 8) }}...</span>
                </div>
                <div class="wiNHN">—</div>
              </div>

              <div class="leFd4">
                <div class="vjfFl">Opening quote:</div>
                <span>{{ number_format((float)($t->open_price ?? 0), 3) }}</span>
                <div class="dOyf4">{{ $t->opened_at ? $t->opened_at->format('d/m/Y, H:i:s') : $t->created_at->format('d/m/Y, H:i:s') }}</div>
              </div>

              <div class="TQG8A">
                <div class="O07P7">Closing quote:</div>
                <span>{{ $t->close_price ? number_format((float)$t->close_price, 3) : 'In progress' }}</span>
                <div class="dOyf4">{{ $t->closed_at ? $t->closed_at->format('d/m/Y, H:i:s') : 'Pending' }}</div>
              </div>

              <div class="g4YxO">
                <div class="dj8M9 uLCSj {{ $profitColorClass }}">
                  <b>
                    <span class="dir-indicator">{{ $t->direction === 'up' ? '↑' : '↓' }}</span>
                    {{ number_format((float)$t->investment, 2) }}$
                  </b>
                </div>
                <div class="dj8M9 fTGaJ {{ $profitColorClass }}">
                  <b>
                    @if($isPendingTrade)
                      Pending
                    @else
                      @php $rowProfit = \App\Services\TradeResultService::settledProfit($t); @endphp
                      {{ ($rowProfit > 0 ? '+' : ($rowProfit < 0 ? '-' : '')) . number_format(abs($rowProfit), 2) }}$
                    @endif
                  </b>
                </div>
              </div>
            </div>
          @empty
            <div class="empty-trades-wrap">
              <div class="empty-trades-icon">📊</div>
              <div class="empty-trades-title">
                {{ ($isPending ?? false) ? 'No pending trades found' : 'No trades recorded in this period' }}
              </div>
              <div class="empty-trades-text">
                {{ ($isPending ?? false) ? 'You do not have any active or pending orders right now.' : 'Start trading to see your complete real-time performance and deal history here.' }}
              </div>
              <a href="{{ route('trade') }}" class="empty-trades-btn">
                Trade Now
              </a>
            </div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- Bottom Pagination -->
    @if($trades->total() > 0)
      <div class="iZaxY" aria-label="Pagination">
        <a href="{{ $trades->previousPageUrl() ?? 'javascript:void(0)' }}" 
           class="THTtN wH2oT {{ $trades->onFirstPage() ? 'DLvqx disabled' : 'ovrkj' }}" 
           aria-label="Prev">
          <span class="PnkSX">Prev</span>
        </a>
        <div class="f8uhl" aria-live="polite" aria-atomic="true" aria-label="Page {{ $trades->currentPage() }} of {{ max(1, $trades->lastPage()) }}">
          <span aria-hidden="true">{{ $trades->currentPage() }}/{{ max(1, $trades->lastPage()) }}</span>
        </div>
        <a href="{{ $trades->nextPageUrl() ?? 'javascript:void(0)' }}" 
           class="THTtN wH2oT {{ $trades->hasMorePages() ? 'ovrkj' : 'DLvqx disabled' }}" 
           aria-label="Next">
          <span class="PnkSX">Next</span>
        </a>
      </div>
    @endif

  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Account Type Dropdown Toggle
  const toggleBtn = document.getElementById('accountTypeToggle');
  const menu = document.getElementById('accountTypeMenu');

  if (toggleBtn && menu) {
    toggleBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      const isOpen = menu.classList.contains('open');
      menu.classList.toggle('open', !isOpen);
      toggleBtn.setAttribute('aria-expanded', !isOpen);
    });

    document.addEventListener('click', function (e) {
      if (!toggleBtn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('open');
        toggleBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }
});
</script>
@endpush
@endsection
