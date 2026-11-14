@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('page-title', '⚡ Dashboard')

@push('styles')
<style>
.chart-bar-wrap { display:flex; align-items:flex-end; gap:6px; height:80px; padding-top:8px; }
.chart-bar { flex:1; border-radius:4px 4px 0 0; background:linear-gradient(to top, var(--accent), rgba(79,142,247,0.3)); min-width:20px; position:relative; transition:.3s; }
.chart-bar:hover { opacity:.8; }
.chart-labels { display:flex; gap:6px; margin-top:4px; }
.chart-label { flex:1; text-align:center; font-size:10px; color:var(--text-muted); }
</style>
@endpush

@section('content')

{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
        <div class="stat-sub">{{ $stats['active_users'] }} active · {{ $stats['blocked_users'] }} blocked</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Trades Now</div>
        <div class="stat-value orange">{{ number_format($stats['active_trades']) }}</div>
        <div class="stat-sub">{{ number_format($stats['total_trades_today']) }} trades today</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Volume Today</div>
        <div class="stat-value green">${{ number_format($stats['total_volume_today'], 2) }}</div>
        <div class="stat-sub">Total: ${{ number_format($stats['total_volume'], 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending Deposits</div>
        <div class="stat-value {{ $stats['pending_deposits'] > 0 ? 'orange' : '' }}">{{ $stats['pending_deposits'] }}</div>
        <div class="stat-sub">Approved: ${{ number_format($stats['total_deposit_amount'], 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Today's Deposits</div>
        <div class="stat-value green">${{ number_format($stats['today_deposit_amount'], 2) }}</div>
        <div class="stat-sub">Approved deposits today</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending Withdrawals</div>
        <div class="stat-value {{ $stats['pending_withdrawals'] > 0 ? 'red' : '' }}">{{ $stats['pending_withdrawals'] }}</div>
        <div class="stat-sub">Users balance: ${{ number_format($stats['total_users_balance'], 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Today's Approved Withdrawals</div>
        <div class="stat-value red">${{ number_format($stats['today_approved_withdrawal_amount'], 2) }}</div>
        <div class="stat-sub">Approved withdrawals today</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Assets</div>
        <div class="stat-value blue">{{ $stats['active_assets'] }}</div>
        <div class="stat-sub">{{ $stats['total_assets'] }} total pairs</div>
    </div>
    <div class="stat-card" style="cursor: pointer;" onclick="window.location='{{ route('admin.kyc.index', ['status' => 'pending']) }}'">
        <div class="stat-label">Pending KYC Verifications</div>
        <div class="stat-value {{ ($stats['pending_kyc'] ?? 0) > 0 ? 'orange' : '' }}">{{ $stats['pending_kyc'] ?? 0 }}</div>
        <div class="stat-sub">Click to review applications</div>
    </div>
</div>

{{-- Charts + Recent --}}
<div class="grid-2" style="gap:20px; margin-bottom:20px;">

    {{-- Volume Chart --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">📈 Trade Volume (Last 7 Days)</div>
        </div>
        @php $volumes = array_column($tradeVolumeChart, 'volume'); $maxVol = count($volumes) ? max(max($volumes), 1) : 1; @endphp
        <div class="chart-bar-wrap">
            @foreach($tradeVolumeChart as $day)
                @php $h = max(4, round(($day['volume'] / $maxVol) * 80)); @endphp
                <div class="chart-bar" style="height:{{ $h }}px" title="{{ $day['date'] }}: ${{ number_format($day['volume'],2) }} ({{ $day['count'] }} trades)"></div>
            @endforeach
        </div>
        <div class="chart-labels">
            @foreach($tradeVolumeChart as $day)
                <div class="chart-label">{{ date('d', strtotime($day['date'])) }}</div>
            @endforeach
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">👥 New Users</div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Balance</th><th>Joined</th></tr></thead>
                <tbody>
                @forelse($recentUsers as $u)
                <tr>
                    <td><a href="{{ route('admin.users.show', $u) }}" style="color:var(--text-primary);text-decoration:none;">{{ $u->name }}</a></td>
                    <td>{{ $u->email }}</td>
                    <td class="{{ $u->live_balance > 0 ? 'green' : '' }}" style="color:{{ $u->live_balance > 0 ? 'var(--green)' : 'inherit' }}">${{ number_format($u->live_balance, 2) }}</td>
                    <td>{{ $u->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-muted)">No users yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Recent Trades --}}
<div class="card mb-4">
    <div class="card-header">
        <div class="card-title">🔄 Recent Trades</div>
        <a href="{{ route('admin.live-trades.index') }}" class="btn btn-ghost btn-sm">Live Monitor →</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>User</th><th>Pair</th><th>Direction</th>
                    <th>Investment</th><th>Payout</th><th>Result</th><th>Time</th>
                </tr>
            </thead>
            <tbody>
            @forelse($recentTrades as $trade)
            <tr>
                <td style="color:var(--text-muted)">{{ $trade->id }}</td>
                <td>{{ $trade->user?->name ?? 'N/A' }}</td>
                <td>{{ $trade->asset?->symbol ?? 'N/A' }}</td>
                <td>
                    @if($trade->direction === 'up')
                        <span class="badge-status green">▲ UP</span>
                    @else
                        <span class="badge-status red">▼ DOWN</span>
                    @endif
                </td>
                <td>${{ number_format($trade->investment, 2) }}</td>
                <td>${{ number_format($trade->payout, 2) }}</td>
                <td>
                    @if($trade->result === 'win') <span class="badge-status green">WIN</span>
                    @elseif($trade->result === 'loss') <span class="badge-status red">LOSS</span>
                    @elseif($trade->result === 'pending') <span class="badge-status orange">PENDING</span>
                    @else <span class="badge-status gray">{{ $trade->result }}</span>
                    @endif
                </td>
                <td>{{ $trade->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:var(--text-muted)">No trades yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Logs --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">📝 Recent Admin Actions</div>
        <a href="{{ route('admin.logs') }}" class="btn btn-ghost btn-sm">Full Log</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Admin</th><th>Action</th><th>Description</th><th>Time</th></tr></thead>
            <tbody>
            @forelse($recentLogs as $log)
            <tr>
                <td>{{ $log->admin?->name ?? 'System' }}</td>
                <td><code style="background:var(--bg-hover);padding:2px 6px;border-radius:4px;font-size:11px">{{ $log->action }}</code></td>
                <td>{{ Str::limit($log->description, 60) }}</td>
                <td>{{ $log->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:var(--text-muted)">No actions logged</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
