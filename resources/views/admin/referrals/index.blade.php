@extends('admin.layouts.app')

@section('title', 'Referral & Affiliate Program')
@section('page-title', 'Referral & Affiliate Management')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-size:20px; font-weight:700; color:var(--text-primary);">Referral & Affiliate Program</h2>
        <p style="font-size:13px; color:var(--text-secondary); margin-top:4px;">
            Monitor affiliate referrals, track user profit/loss commission payouts, and manage system rates.
        </p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('admin.referrals.settings') }}" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:8px;">
            <span>⚙️</span> Commission Settings
        </a>
    </div>
</div>

{{-- Current Commission Rates Pill --}}
<div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, rgba(79,142,247,0.08), rgba(0,209,122,0.05)); border: 1px solid rgba(79,142,247,0.25);">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:40px; height:40px; border-radius:10px; background:var(--accent-glow); display:flex; align-items:center; justify-content:center; font-size:20px;">
                🤝
            </div>
            <div>
                <div style="font-size:14px; font-weight:700; color:var(--text-primary);">Active Commission Rates</div>
                <div style="font-size:12px; color:var(--text-secondary);">
                    Status: 
                    @if($settings->is_enabled)
                        <span style="color:var(--green); font-weight:700;">● Active & Rewarding</span>
                    @else
                        <span style="color:var(--red); font-weight:700;">● System Paused</span>
                    @endif
                </div>
            </div>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <div style="background:var(--bg-card); padding:8px 14px; border-radius:8px; border:1px solid var(--border); text-align:center;">
                <div style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Trade WIN %</div>
                <div style="font-size:16px; font-weight:800; color:var(--green);">{{ $settings->trade_win_commission_percent }}%</div>
            </div>
            <div style="background:var(--bg-card); padding:8px 14px; border-radius:8px; border:1px solid var(--border); text-align:center;">
                <div style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Trade LOSS %</div>
                <div style="font-size:16px; font-weight:800; color:var(--orange);">{{ $settings->trade_loss_commission_percent }}%</div>
            </div>
            <div style="background:var(--bg-card); padding:8px 14px; border-radius:8px; border:1px solid var(--border); text-align:center;">
                <div style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Deposit %</div>
                <div style="font-size:16px; font-weight:800; color:var(--accent);">{{ $settings->deposit_commission_percent }}%</div>
            </div>
            <div style="background:var(--bg-card); padding:8px 14px; border-radius:8px; border:1px solid var(--border); text-align:center;">
                <div style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Join Bonus</div>
                <div style="font-size:16px; font-weight:800; color:#fff;">${{ number_format($settings->join_commission_amount, 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Commission Paid</div>
        <div class="stat-value green">${{ number_format($stats['total_commissions_paid'], 2) }}</div>
        <div class="stat-sub">{{ $stats['total_commission_events'] }} total reward events</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Trade WIN Commissions</div>
        <div class="stat-value green">${{ number_format($stats['trade_win_commissions'], 2) }}</div>
        <div class="stat-sub">From referred traders' winning profit</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Trade LOSS RevShare</div>
        <div class="stat-value orange">${{ number_format($stats['trade_loss_commissions'], 2) }}</div>
        <div class="stat-sub">From referred traders' trade losses</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Deposit Commissions</div>
        <div class="stat-value" style="color:var(--accent);">${{ number_format($stats['deposit_commissions'], 2) }}</div>
        <div class="stat-sub">From referred trader deposits</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Referred Traders</div>
        <div class="stat-value">{{ number_format($stats['total_referrals']) }}</div>
        <div class="stat-sub">Users registered via referral codes</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Referrers</div>
        <div class="stat-value">{{ number_format($stats['active_referrers']) }}</div>
        <div class="stat-sub">Traders with >= 1 active referral</div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 24px;">
    {{-- Referrers Leaderboard --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">🏆 All Affiliates / Referrers</div>
            <span class="badge-status blue">All Referrers</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Affiliate</th>
                        <th>Ref Code</th>
                        <th>Invited</th>
                        <th>Total Earned</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topReferrers as $referrer)
                        <tr>
                            <td>
                                <div style="font-weight:600; color:var(--text-primary);">{{ $referrer->name }}</div>
                                <div style="font-size:11px; color:var(--text-muted);">{{ $referrer->email }}</div>
                            </td>
                            <td>
                                <code style="background:var(--bg-hover); padding:3px 6px; border-radius:4px; color:var(--accent); font-size:12px;">{{ $referrer->referral_code ?? '—' }}</code>
                            </td>
                            <td>
                                <span class="badge-status blue">{{ $referrer->referrals_count }} traders</span>
                            </td>
                            <td>
                                <strong style="color:var(--green); font-size:14px;">${{ number_format($referrer->commissions_earned_sum_commission_amount ?? 0, 2) }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('admin.referrals.user', $referrer->id) }}" class="btn btn-ghost btn-sm">View Tree</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:24px; color:var(--text-muted);">No active referrers recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Commission Distribution Breakdown --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Commission Type Breakdown</div>
        </div>
        <div style="display:flex; flex-direction:column; gap:16px; padding:10px 0;">
            @php
                $total = max(1, $stats['total_commissions_paid']);
                $winPct = round(($stats['trade_win_commissions'] / $total) * 100, 1);
                $lossPct = round(($stats['trade_loss_commissions'] / $total) * 100, 1);
                $depPct = round(($stats['deposit_commissions'] / $total) * 100, 1);
                $joinPct = round(($stats['join_commissions'] / $total) * 100, 1);
            @endphp

            <div>
                <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                    <span style="color:var(--green); font-weight:600;">Trade WIN Commissions ({{ $settings->trade_win_commission_percent }}%)</span>
                    <strong>${{ number_format($stats['trade_win_commissions'], 2) }} ({{ $winPct }}%)</strong>
                </div>
                <div style="height:8px; background:var(--bg-primary); border-radius:4px; overflow:hidden;">
                    <div style="height:100%; width:{{ $winPct }}%; background:var(--green); border-radius:4px;"></div>
                </div>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                    <span style="color:var(--orange); font-weight:600;">Trade LOSS RevShare ({{ $settings->trade_loss_commission_percent }}%)</span>
                    <strong>${{ number_format($stats['trade_loss_commissions'], 2) }} ({{ $lossPct }}%)</strong>
                </div>
                <div style="height:8px; background:var(--bg-primary); border-radius:4px; overflow:hidden;">
                    <div style="height:100%; width:{{ $lossPct }}%; background:var(--orange); border-radius:4px;"></div>
                </div>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                    <span style="color:var(--accent); font-weight:600;">Deposit Commissions ({{ $settings->deposit_commission_percent }}%)</span>
                    <strong>${{ number_format($stats['deposit_commissions'], 2) }} ({{ $depPct }}%)</strong>
                </div>
                <div style="height:8px; background:var(--bg-primary); border-radius:4px; overflow:hidden;">
                    <div style="height:100%; width:{{ $depPct }}%; background:var(--accent); border-radius:4px;"></div>
                </div>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                    <span style="color:#fff; font-weight:600;">Signup / Join Bonuses</span>
                    <strong>${{ number_format($stats['join_commissions'], 2) }} ({{ $joinPct }}%)</strong>
                </div>
                <div style="height:8px; background:var(--bg-primary); border-radius:4px; overflow:hidden;">
                    <div style="height:100%; width:{{ $joinPct }}%; background:#7b5ea7; border-radius:4px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Full Commissions History Ledger --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">📜 Live Referral Commissions Ledger</div>
        <form method="GET" action="{{ route('admin.referrals.index') }}" style="display:flex; gap:10px; align-items:center;">
            <select name="type" class="form-control" style="width:160px; padding:6px 10px; font-size:12px;" onchange="this.form.submit()">
                <option value="">All Event Types</option>
                <option value="trade_win" {{ request('type') == 'trade_win' ? 'selected' : '' }}>Trade WIN Profit</option>
                <option value="trade_loss" {{ request('type') == 'trade_loss' ? 'selected' : '' }}>Trade LOSS RevShare</option>
                <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Deposit Commission</option>
                <option value="join" {{ request('type') == 'join' ? 'selected' : '' }}>Join Bonus</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search email / name..." class="form-control" style="width:200px; padding:6px 10px; font-size:12px;">
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
            @if(request()->filled('type') || request()->filled('search'))
                <a href="{{ route('admin.referrals.index') }}" class="btn btn-ghost btn-sm">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Referrer (Receiver)</th>
                    <th>Referred Trader</th>
                    <th>Type</th>
                    <th>Base Amount</th>
                    <th>Rate</th>
                    <th>Commission Earned</th>
                    <th>Status</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commissions as $comm)
                    <tr>
                        <td>#{{ $comm->id }}</td>
                        <td>
                            <div style="font-weight:600; color:var(--text-primary);">{{ $comm->referrer->name ?? 'User #' . $comm->referrer_id }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">{{ $comm->referrer->email ?? '' }}</div>
                        </td>
                        <td>
                            <div style="font-weight:600; color:var(--text-primary);">{{ $comm->referredUser->name ?? 'User #' . $comm->referred_user_id }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">{{ $comm->referredUser->email ?? '' }}</div>
                        </td>
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
                        <td>
                            @if($comm->base_amount > 0)
                                ${{ number_format($comm->base_amount, 2) }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <strong style="color:var(--text-primary);">{{ $comm->commission_percent }}%</strong>
                        </td>
                        <td>
                            <strong style="color:var(--green); font-size:14px;">+${{ number_format($comm->commission_amount, 2) }}</strong>
                        </td>
                        <td>
                            @if($comm->status === 'credited')
                                <span class="badge-status green">✓ Credited</span>
                            @elseif($comm->status === 'pending')
                                <span class="badge-status orange">⏳ Pending</span>
                            @else
                                <span class="badge-status red">✕ Cancelled</span>
                            @endif
                        </td>
                        <td style="font-size:11px; color:var(--text-muted);">
                            {{ $comm->created_at->format('M d, Y H:i:s') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:32px; color:var(--text-muted);">
                            No commission records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($commissions->hasPages())
        <div style="padding-top:16px;">
            {{ $commissions->links() }}
        </div>
    @endif
</div>
@endsection
