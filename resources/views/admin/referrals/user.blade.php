@extends('admin.layouts.app')

@section('title', 'Affiliate Tree: ' . $user->name)
@section('page-title', 'User Affiliate Details')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-size:20px; font-weight:700; color:var(--text-primary);">Affiliate Details: {{ $user->name }}</h2>
        <p style="font-size:13px; color:var(--text-secondary); margin-top:4px;">
            Email: <strong style="color:#fff;">{{ $user->email }}</strong> | Ref Code: <code style="color:var(--accent);">{{ $user->referral_code }}</code>
        </p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-ghost">View User Profile</a>
        <a href="{{ route('admin.referrals.index') }}" class="btn btn-ghost">← All Affiliates</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Commission Earned</div>
        <div class="stat-value green">${{ number_format($user->getTotalCommissionEarned(), 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Win Commissions</div>
        <div class="stat-value green">${{ number_format($user->getWinCommissionEarned(), 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Loss RevShare</div>
        <div class="stat-value orange">${{ number_format($user->getLossCommissionEarned(), 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Deposit Commissions</div>
        <div class="stat-value" style="color:var(--accent);">${{ number_format($user->getDepositCommissionEarned(), 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Referral Deposits</div>
        <div class="stat-value" style="color:var(--accent);">${{ number_format($totalReferralDeposits, 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Invited Traders</div>
        <div class="stat-value">{{ $user->referrals->count() }}</div>
    </div>
</div>

{{-- Referred Users List --}}
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <div class="card-title">👥 Invited Traders ({{ $user->referrals->count() }})</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Trader Name</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Account Type</th>
                    <th>Total Trades</th>
                    <th>Live Balance</th>
                    <th>Joined At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($user->referrals as $referred)
                    <tr>
                        <td>#{{ $referred->id }}</td>
                        <td><strong>{{ $referred->name }}</strong></td>
                        <td>{{ $referred->email }}</td>
                        <td>{{ $referred->country ?? '—' }}</td>
                        <td>
                            <span class="badge-status {{ $referred->account_type === 'live' ? 'green' : 'orange' }}">
                                {{ strtoupper($referred->account_type) }}
                            </span>
                        </td>
                        <td>
                            {{ $referred->live_trades_count ?? 0 }} live
                            <div style="font-size:11px;color:var(--text-muted);">{{ $referred->trades_count ?? 0 }} total</div>
                        </td>
                        <td>${{ number_format($referred->live_balance ?? 0, 2) }}</td>
                        <td>{{ $referred->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $referred->id) }}" class="btn btn-ghost btn-sm">Inspect</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:24px; color:var(--text-muted);">
                            This user has not invited any traders yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Commission Earnings Log --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">📜 Commission Payouts History</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>From Trader</th>
                    <th>Type</th>
                    <th>Base Amount</th>
                    <th>Rate</th>
                    <th>Commission Earned</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commissions as $comm)
                    <tr>
                        <td>#{{ $comm->id }}</td>
                        <td>{{ $comm->referredUser->name ?? $comm->referredUser->email ?? 'User #' . $comm->referred_user_id }}</td>
                        <td>
                            @if($comm->type === 'trade_win')
                                <span class="badge-status green">🎯 Trade Win</span>
                            @elseif($comm->type === 'trade_loss')
                                <span class="badge-status orange">📉 Trade Loss RevShare</span>
                            @elseif($comm->type === 'deposit')
                                <span class="badge-status blue">💰 Deposit</span>
                            @else
                                <span class="badge-status gray">🎁 Signup Bonus</span>
                            @endif
                        </td>
                        <td>${{ number_format($comm->base_amount, 2) }}</td>
                        <td>{{ $comm->commission_percent }}%</td>
                        <td><strong style="color:var(--green);">+${{ number_format($comm->commission_amount, 2) }}</strong></td>
                        <td><span class="badge-status green">✓ {{ ucfirst($comm->status) }}</span></td>
                        <td style="font-size:11px; color:var(--text-muted);">{{ $comm->notes }}</td>
                        <td style="font-size:11px; color:var(--text-muted);">{{ $comm->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:24px; color:var(--text-muted);">
                            No commission records for this user yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
