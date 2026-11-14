@extends('layouts.platform')

@section('title', 'Transactions | Quotex')

@push('styles')
<style>
/* Unified money history (all transaction types in one list) */
.transactions-page {
  width: 100%;
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px 20px 60px;
  box-sizing: border-box;
  color: #c9d1d9;
}
.transactions-page h1 {
  font-size: 22px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 6px 0;
}
.transactions-page .tx-subtitle {
  font-size: 13px;
  color: #8b95a5;
  margin: 0 0 20px 0;
}
.tx-nav {
  display: flex;
  gap: 18px;
  flex-wrap: wrap;
  margin-bottom: 20px;
  font-size: 13px;
}
.tx-nav a {
  color: #00e676;
  font-weight: 600;
  text-decoration: none;
}
.tx-nav a:hover {
  text-decoration: underline;
}
.tx-card {
  background: #2b3040;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  overflow: hidden;
}
.tx-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.tx-table th {
  text-align: left;
  padding: 12px 14px;
  color: #8b95a5;
  font-size: 11.5px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  white-space: nowrap;
}
.tx-table td {
  padding: 12px 14px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  vertical-align: top;
}
.tx-table tr:last-child td {
  border-bottom: none;
}
.tx-table .tx-date {
  color: #78909c;
  white-space: nowrap;
}
.tx-table .tx-id {
  color: #90a4ae;
  font-family: monospace;
}
.tx-table .tx-type {
  color: #ffffff;
  font-weight: 600;
  white-space: nowrap;
}
.tx-table .tx-account {
  display: inline-block;
  margin-left: 6px;
  padding: 1px 6px;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.08);
  color: #b0bec5;
  font-size: 10.5px;
  font-weight: 600;
  text-transform: uppercase;
}
.tx-table .tx-details {
  color: #b0bec5;
  font-size: 12.5px;
  line-height: 1.45;
}
.tx-table .tx-amount {
  font-weight: 700;
  white-space: nowrap;
  text-align: right;
}
.tx-amount.in {
  color: #00e676;
}
.tx-amount.out {
  color: #ff5252;
}
.tx-status {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}
.tx-status.approved {
  background: rgba(0, 200, 83, 0.15);
  color: #00e676;
}
.tx-status.pending,
.tx-status.processing {
  background: rgba(255, 171, 0, 0.15);
  color: #ffab00;
}
.tx-status.rejected,
.tx-status.failed {
  background: rgba(255, 82, 82, 0.15);
  color: #ff5252;
}
.tx-filters {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
  margin-bottom: 16px;
}
.tx-filters select,
.tx-filters input[type="date"],
.tx-filters input[type="text"] {
  background: #2b3040;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 8px;
  color: #ffffff;
  font-size: 13px;
  padding: 8px 10px;
  outline: none;
}
.tx-filters select:focus,
.tx-filters input:focus {
  border-color: #026fd3;
}
.tx-filters input[type="text"] {
  flex: 1 1 160px;
  min-width: 140px;
}
.tx-filters button {
  background: #026fd3;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}
.tx-filters button:hover {
  background: #1d82e2;
}
.tx-filters .tx-clear {
  color: #8b95a5;
  font-size: 13px;
  text-decoration: none;
}
.tx-filters .tx-clear:hover {
  text-decoration: underline;
}
.tx-fee-note {
  display: block;
  font-size: 11px;
  font-weight: 400;
  color: #ffab00;
  margin-top: 3px;
  white-space: normal;
}
.tx-empty {
  padding: 32px 16px;
  text-align: center;
  color: #8b95a5;
  font-size: 13.5px;
  line-height: 1.6;
}
.tx-pager {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  margin-top: 20px;
  font-size: 13px;
}
.tx-pager a {
  color: #ffffff;
  background: #2b3040;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 8px;
  padding: 8px 16px;
  text-decoration: none;
  font-weight: 600;
}
.tx-pager a.disabled {
  opacity: 0.4;
  pointer-events: none;
}
.tx-pager span {
  color: #8b95a5;
  font-family: monospace;
  font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="transactions-page">
  <h1>Transactions</h1>
  <p class="tx-subtitle">All money movements on your account — deposits, withdrawals, promo bonuses and tournament entries — in one place.</p>

  <div class="tx-nav">
    <a href="{{ route('deposit') }}">Deposit</a>
    <a href="{{ route('withdrawal') }}">Withdrawal</a>
    <a href="{{ route('history') }}">Trade history</a>
  </div>

  {{-- Filters: type / status / date range / search (GET, kept across
       pagination via withQueryString). --}}
  @php $f = $filters ?? ['type' => '', 'status' => '', 'from' => '', 'to' => '', 'q' => '']; @endphp
  <form method="GET" action="{{ route('transactions.index') }}" class="tx-filters">
    <select name="type" aria-label="Filter by type">
      <option value="">All types</option>
      <option value="deposit" {{ ($f['type'] ?? '') === 'deposit' ? 'selected' : '' }}>Deposit</option>
      <option value="withdrawal" {{ ($f['type'] ?? '') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
      <option value="bonus" {{ ($f['type'] ?? '') === 'bonus' ? 'selected' : '' }}>Promo bonus</option>
      <option value="tournament_entry" {{ ($f['type'] ?? '') === 'tournament_entry' ? 'selected' : '' }}>Tournament entry</option>
    </select>
    <select name="status" aria-label="Filter by status">
      <option value="">All statuses</option>
      @foreach(['pending' => 'Pending', 'processing' => 'Processing', 'approved' => 'Approved', 'rejected' => 'Rejected', 'failed' => 'Failed'] as $value => $label)
        <option value="{{ $value }}" {{ ($f['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
    <input type="date" name="from" value="{{ $f['from'] ?? '' }}" aria-label="From date">
    <input type="date" name="to" value="{{ $f['to'] ?? '' }}" aria-label="To date">
    <input type="text" name="q" value="{{ $f['q'] ?? '' }}" placeholder="Search method, notes or reference…" aria-label="Search transactions">
    <button type="submit">Filter</button>
    @if(!empty($f['type']) || !empty($f['status']) || !empty($f['from']) || !empty($f['to']) || !empty($f['q']))
      <a class="tx-clear" href="{{ route('transactions.index') }}">Clear</a>
    @endif
  </form>

  <div class="tx-card">
    @php
      // Money-in types show with a + sign, money-out types with a − sign.
      $moneyInTypes = ['deposit', 'bonus'];
      $typeLabels = [
        'deposit' => 'Deposit',
        'withdrawal' => 'Withdrawal',
        'bonus' => 'Promo bonus',
        'tournament_entry' => 'Tournament entry',
        'market_purchase' => 'Market purchase',
      ];
    @endphp

    @if($transactions->count() > 0)
      <table class="tx-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>ID</th>
            <th>Type</th>
            <th>Details</th>
            <th>Status</th>
            <th style="text-align: right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          @foreach($transactions as $tx)
            @php
              $isIn = in_array($tx->type, $moneyInTypes, true);
              $typeLabel = $typeLabels[$tx->type] ?? ucfirst(str_replace('_', ' ', $tx->type));
              $statusKey = strtolower($tx->status ?? 'pending');
              $statusLabel = match($statusKey) {
                'approved', 'completed', 'success' => 'Approved',
                'rejected', 'failed', 'canceled' => 'Rejected',
                'processing' => 'Processing',
                default => 'Pending',
              };
            @endphp
            <tr>
              <td class="tx-date">{{ $tx->created_at ? $tx->created_at->format('M d, Y H:i') : '—' }}</td>
              <td class="tx-id">#{{ 84000000 + $tx->id }}</td>
              <td class="tx-type">
                {{ $typeLabel }}
                @if(!empty($tx->account_type))
                  <span class="tx-account">{{ $tx->account_type }}</span>
                @endif
              </td>
              <td class="tx-details">
                {{ $tx->method ?? '' }}
                @if(!empty($tx->notes))
                  <br><span style="color:#78909c;">{{ $tx->notes }}</span>
                @endif
              </td>
              <td><span class="tx-status {{ $statusKey }}">{{ $statusLabel }}</span></td>
              <td class="tx-amount {{ $isIn ? 'in' : 'out' }}">{{ $isIn ? '+' : '−' }}${{ number_format((float) $tx->amount, 2) }}
                @if(!empty($tx->fee_amount) && (float) $tx->fee_amount > 0)
                  <span class="tx-fee-note">Fee ${{ number_format((float) $tx->fee_amount, 2) }} · You receive ${{ number_format((float) ($tx->net_amount ?? ((float) $tx->amount - (float) $tx->fee_amount)), 2) }}</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="tx-empty">
        You don't have any transactions yet.<br>
        Deposits, withdrawals, promo bonuses, tournament entries and Market purchases will appear here.
      </div>
    @endif
  </div>

  @if($transactions->hasPages())
    <div class="tx-pager" aria-label="Pagination">
      <a href="{{ $transactions->previousPageUrl() ?? 'javascript:void(0)' }}" class="{{ $transactions->onFirstPage() ? 'disabled' : '' }}" aria-label="Prev">Prev</a>
      <span aria-live="polite">Page {{ $transactions->currentPage() }} of {{ max(1, $transactions->lastPage()) }}</span>
      <a href="{{ $transactions->nextPageUrl() ?? 'javascript:void(0)' }}" class="{{ $transactions->hasMorePages() ? '' : 'disabled' }}" aria-label="Next">Next</a>
    </div>
  @endif
</div>
@endsection
