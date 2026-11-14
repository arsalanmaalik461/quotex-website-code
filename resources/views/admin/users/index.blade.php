@extends('admin.layouts.app')
@section('title', 'Manage Users')
@section('page-title', '👥 User Management')

@section('content')

{{-- Search & Filter --}}
<div class="card mb-4">
    <form method="GET" class="flex gap-3 items-center" style="flex-wrap:wrap;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone..." class="form-control" style="max-width:280px">
        <select name="status" class="form-control" style="max-width:150px">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
        </select>
        <select name="account_type" class="form-control" style="max-width:150px">
            <option value="">All Accounts</option>
            <option value="demo" {{ request('account_type') === 'demo' ? 'selected' : '' }}>Demo</option>
            <option value="live" {{ request('account_type') === 'live' ? 'selected' : '' }}>Live</option>
        </select>
        <button type="submit" class="btn btn-primary">🔍 Search</button>
        @if(request()->hasAny(['search','status','account_type']))
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value">{{ $users->total() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Page</div>
        <div class="stat-value">{{ $users->currentPage() }}/{{ $users->lastPage() }}</div>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">Users ({{ $users->total() }})</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Balance (Live)</th>
                    <th>Account</th>
                    <th>Trades</th>
                    <th>Referrals</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
            <tr>
                <td style="color:var(--text-muted)">{{ $user->id }}</td>
                <td>
                    <a href="{{ route('admin.users.show', $user) }}" style="color:var(--text-primary);text-decoration:none;font-weight:500">
                        {{ $user->name }}
                    </a>
                    @if($user->country)
                        <div style="font-size:11px;color:var(--text-muted)">{{ $user->country }}</div>
                    @endif
                </td>
                <td>{{ $user->email }}</td>
                <td style="color:{{ $user->live_balance > 0 ? 'var(--green)' : 'inherit' }}">
                    ${{ number_format($user->live_balance, 2) }}
                    <div style="font-size:11px;color:var(--text-muted)">Demo: ${{ number_format($user->demo_balance, 2) }}</div>
                </td>
                <td>
                    <span class="badge-status {{ $user->account_type === 'live' ? 'green' : 'gray' }}">
                        {{ strtoupper($user->account_type) }}
                    </span>
                </td>
                <td>{{ $user->trades_count }}</td>
                <td>{{ $user->referrals_count }}</td>
                <td>
                    @if($user->is_blocked)
                        <span class="badge-status red">🚫 Blocked</span>
                    @else
                        <span class="badge-status green">✓ Active</span>
                    @endif
                </td>
                <td>{{ $user->created_at->format('M d, Y') }}</td>
                <td>
                    <div class="flex gap-2" style="flex-wrap:nowrap">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-sm">View</a>
                        <form method="POST" action="{{ route('admin.users.login-as', $user) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-warning btn-sm" title="Login as user">🔑</button>
                        </form>
                        @if($user->is_blocked)
                        <form method="POST" action="{{ route('admin.users.unblock', $user) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-success btn-sm">Unblock</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.users.block', $user) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-danger btn-sm">Block</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center;color:var(--text-muted);padding:32px">No users found</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div style="margin-top:16px">
        {{ $users->links('pagination::simple-default') }}
    </div>
</div>

@endsection
