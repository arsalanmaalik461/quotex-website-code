@extends('admin.layouts.app')
@section('title', 'User: ' . $user->name)
@section('page-title', 'User Detail')

@section('content')

@php
    $accountTabs = [
        'overall' => 'Overall',
        'demo' => 'Demo Account',
        'live' => 'Live Account',
    ];
@endphp

<div style="margin-bottom:16px">
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Back to Users</a>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="flex justify-between items-center" style="gap:16px;flex-wrap:wrap;">
        <div>
            <div class="card-title" style="margin-bottom:4px;">Account View</div>
            <div style="font-size:12px;color:var(--text-muted);">
                Showing {{ $accountTabs[$accountView] ?? 'Overall' }} data for {{ $user->email }}.
                Trade and transaction rows are scoped to this user{{ $accountView !== 'overall' ? ' and account mode' : '' }}.
            </div>
        </div>
        <div class="flex gap-2" style="flex-wrap:wrap;">
            @foreach($accountTabs as $accountKey => $accountLabel)
                <a href="{{ route('admin.users.show', ['user' => $user, 'account' => $accountKey]) }}"
                   class="btn btn-sm {{ $accountView === $accountKey ? 'btn-primary' : 'btn-ghost' }}">
                    {{ $accountLabel }}
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="grid-2" style="gap:20px; margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">User Profile</div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.users.login-as', $user) }}">
                    @csrf
                    <button class="btn btn-warning btn-sm">Login As</button>
                </form>
                @if($user->is_blocked)
                <form method="POST" action="{{ route('admin.users.unblock', $user) }}">
                    @csrf <button class="btn btn-success btn-sm">Unblock</button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.users.block', $user) }}">
                    @csrf <button class="btn btn-danger btn-sm">Block</button>
                </form>
                @endif
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete user {{ $user->name }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>

        <div style="display:grid;gap:12px;font-size:13px;">
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">ID</span>
                <span>#{{ $user->id }}</span>
            </div>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Status</span>
                @if($user->is_blocked) <span class="badge-status red">Blocked</span>
                @else <span class="badge-status green">Active</span> @endif
            </div>
            <div class="flex justify-between items-center" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">KYC Verification</span>
                @php $userKyc = \Illuminate\Support\Facades\Schema::hasTable('kyc_verifications') ? $user->kyc : null; @endphp
                @if($userKyc)
                    <div class="flex items-center gap-2">
                        @if($userKyc->status === 'approved')
                            <span class="badge-status green">Verified</span>
                        @elseif($userKyc->status === 'pending')
                            <span class="badge-status orange">Pending</span>
                        @else
                            <span class="badge-status red">Rejected</span>
                        @endif
                        <a href="{{ route('admin.kyc.show', $userKyc) }}" class="btn btn-ghost btn-sm" style="padding: 2px 8px; font-size: 11px;">Review KYC</a>
                    </div>
                @else
                    <span style="color:var(--text-muted);">Not Submitted</span>
                @endif
            </div>
            @if($accountView !== 'live')
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Demo Balance</span>
                <span>${{ number_format($user->demo_balance, 2) }}</span>
            </div>
            @endif
            @if($accountView !== 'demo')
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Live Balance</span>
                <strong style="color:var(--green)">${{ number_format($user->live_balance, 2) }}</strong>
            </div>
            @endif
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Account Age</span>
                <span>{{ $tradeStats['account_age'] }} days</span>
            </div>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Referrals</span>
                <span>{{ $tradeStats['referral_count'] }}</span>
            </div>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Referral Code</span>
                <span>{{ $user->referral_code ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between" style="padding:10px;background:var(--bg-hover);border-radius:8px;">
                <span style="color:var(--text-muted)">Joined</span>
                <span>{{ $user->created_at->format('M d, Y H:i') }}</span>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-title" style="margin-bottom:12px;">
                {{ strtoupper($accountTabs[$accountView] ?? 'Overall') }} Overview
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">{{ $accountView === 'overall' ? 'Combined Balance' : 'Balance' }}</div>
                    <div class="stat-value" style="font-size:18px">${{ number_format($tradeStats['selected_balance'], 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Trades</div>
                    <div class="stat-value">{{ $tradeStats['total_trades'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Win / Loss</div>
                    <div class="stat-value">
                        <span style="color:var(--green)">{{ $tradeStats['win_trades'] }}</span>
                        / <span style="color:var(--red)">{{ $tradeStats['loss_trades'] }}</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Completed / Active</div>
                    <div class="stat-value">
                        {{ $tradeStats['completed_trades'] }}
                        / <span style="color:var(--text-muted)">{{ $tradeStats['active_trades'] }}</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Win Rate</div>
                    <div class="stat-value" style="font-size:18px">{{ number_format($tradeStats['win_rate'], 2) }}%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Invested</div>
                    <div class="stat-value" style="font-size:18px">${{ number_format($tradeStats['total_invested'], 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Winning Payout</div>
                    <div class="stat-value green" style="font-size:18px">${{ number_format($tradeStats['total_payout'], 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Loss Amount</div>
                    <div class="stat-value red" style="font-size:18px">${{ number_format($tradeStats['loss_amount'], 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Net Profit</div>
                    <div class="stat-value {{ $tradeStats['net_profit'] >= 0 ? 'green' : 'red' }}" style="font-size:18px">
                        ${{ number_format($tradeStats['net_profit'], 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title" style="margin-bottom:16px">Adjust Balance</div>
            <form method="POST" action="{{ route('admin.users.add-funds', $user) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Amount (negative to deduct)</label>
                    <input type="number" name="amount" step="0.01" class="form-control" placeholder="e.g. 100 or -50" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Account Type</label>
                    <select name="account_type" class="form-control">
                        <option value="live">Live Account</option>
                        <option value="demo">Demo Account</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Note (optional)</label>
                    <input type="text" name="note" class="form-control" placeholder="Reason for adjustment">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Apply Balance Change</button>
            </form>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-title" style="margin-bottom:16px">Edit User Details</div>
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf @method('PUT')
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
            </div>
            <div class="form-group">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control" value="{{ $user->country }}">
            </div>
            <div class="form-group">
                <label class="form-label">Account Type</label>
                <select name="account_type" class="form-control">
                    <option value="demo" {{ $user->account_type === 'demo' ? 'selected' : '' }}>Demo</option>
                    <option value="live" {{ $user->account_type === 'live' ? 'selected' : '' }}>Live</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">New Password (leave blank to keep)</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-title" style="margin-bottom:16px">Recent Trades</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    @if($accountView === 'overall')<th>Account</th>@endif
                    <th>Pair</th>
                    <th>Direction</th>
                    <th>Investment</th>
                    <th>Payout</th>
                    <th>Duration</th>
                    <th>Result</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            @forelse($user->trades as $trade)
            <tr>
                <td style="color:var(--text-muted)">{{ $trade->id }}</td>
                @if($accountView === 'overall')
                <td>
                    <span class="badge-status {{ $trade->account_type === 'live' ? 'green' : 'gray' }}">
                        {{ ucfirst($trade->account_type ?? 'demo') }}
                    </span>
                </td>
                @endif
                <td>{{ $trade->asset?->symbol ?? 'N/A' }}</td>
                <td>
                    @if($trade->direction === 'up') <span class="badge-status green">UP</span>
                    @else <span class="badge-status red">DOWN</span> @endif
                </td>
                <td>${{ number_format($trade->investment, 2) }}</td>
                <td>${{ number_format($trade->payout, 2) }}</td>
                <td>{{ round($trade->duration_seconds / 60, 1) }}m</td>
                <td>
                    @if($trade->result === 'win') <span class="badge-status green">WIN</span>
                    @elseif($trade->result === 'loss') <span class="badge-status red">LOSS</span>
                    @elseif($trade->result === 'pending') <span class="badge-status orange">LIVE</span>
                    @else <span class="badge-status gray">{{ $trade->result }}</span> @endif
                </td>
                <td>{{ $trade->created_at->format('M d, H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="{{ $accountView === 'overall' ? 9 : 8 }}" style="text-align:center;color:var(--text-muted);padding:24px">No trades yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-title" style="margin-bottom:16px">Recent Transactions</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    @if($accountView === 'overall')<th>Account</th>@endif
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            @forelse($user->transactions as $tx)
            <tr>
                <td style="color:var(--text-muted)">{{ $tx->id }}</td>
                @if($accountView === 'overall')
                <td>
                    @if($tx->account_type === 'live')
                        <span class="badge-status green">Live</span>
                    @elseif($tx->account_type === 'demo')
                        <span class="badge-status gray">Demo</span>
                    @else
                        <span class="badge-status orange">Legacy</span>
                    @endif
                </td>
                @endif
                <td>{{ ucfirst($tx->type) }}</td>
                <td>${{ number_format($tx->amount, 2) }}</td>
                <td>{{ $tx->method }}</td>
                <td>
                    @if($tx->status === 'approved') <span class="badge-status green">Approved</span>
                    @elseif($tx->status === 'rejected') <span class="badge-status red">Rejected</span>
                    @else <span class="badge-status orange">Pending</span> @endif
                </td>
                <td>{{ $tx->created_at->format('M d, H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="{{ $accountView === 'overall' ? 7 : 6 }}" style="text-align:center;color:var(--text-muted);padding:24px">No transactions</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
