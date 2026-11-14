@extends('admin.layouts.app')
@section('title', 'All Transactions')
@section('page-title', '📋 All Transactions')

@section('content')
<div class="card mb-4">
    <form method="GET" class="flex gap-3 items-center" style="flex-wrap:wrap">
        <select name="type" class="form-control" style="max-width:150px">
            <option value="">All Types</option>
            <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposits</option>
            <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Withdrawals</option>
        </select>
        <select name="status" class="form-control" style="max-width:150px">
            <option value="">All Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('admin.transactions.all') }}" class="btn btn-ghost">Clear</a>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>User</th><th>Type</th><th>Amount</th><th>Method</th><th>Status</th><th>Notes</th><th>Date</th></tr>
            </thead>
            <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td style="color:var(--text-muted)">{{ $tx->id }}</td>
                <td>{{ $tx->user?->name ?? 'Deleted' }}</td>
                <td>
                    @if($tx->type === 'deposit') <span class="badge-status green">Deposit</span>
                    @else <span class="badge-status red">Withdrawal</span> @endif
                </td>
                <td style="font-weight:700">${{ number_format($tx->amount, 2) }}</td>
                <td>{{ $tx->method }}</td>
                <td>
                    @if($tx->status === 'approved') <span class="badge-status green">✓ Approved</span>
                    @elseif($tx->status === 'rejected') <span class="badge-status red">✕ Rejected</span>
                    @else <span class="badge-status orange">⏳ Pending</span> @endif
                </td>
                <td style="font-size:12px;color:var(--text-muted)">{{ Str::limit($tx->notes, 40) }}</td>
                <td>{{ $tx->created_at->format('M d, H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:32px">No transactions</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $transactions->links() }}</div>
</div>
@endsection
