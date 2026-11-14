@extends('admin.layouts.app')
@section('title', 'Deposit Requests')
@section('page-title', '💰 Deposit Requests')

@section('content')

{{-- Filter --}}
<div class="card mb-4">
    <form method="GET" class="flex gap-3 items-center">
        <select name="status" class="form-control" style="max-width:160px">
            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Deposits ({{ $transactions->total() }})</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>User</th><th>Amount</th><th>Method</th>
                    <th>Payment Proof / TRX ID</th>
                    <th>Status</th><th>Notes</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td style="color:var(--text-muted)">{{ $tx->id }}</td>
                <td>
                    <a href="{{ route('admin.users.show', $tx->user) }}" style="color:var(--text-primary);text-decoration:none;font-weight:500">
                        {{ $tx->user?->name ?? 'Deleted' }}
                    </a>
                    <div style="font-size:11px;color:var(--text-muted)">{{ $tx->user?->email }}</div>
                </td>
                <td>
                    <span style="color:var(--green);font-weight:700;font-size:15px">${{ number_format($tx->amount, 2) }}</span>
                    @if($tx->converted_amount)
                        <div style="font-size:11px;color:var(--accent);">≈ {{ number_format($tx->converted_amount, 2) }} {{ $tx->currency }}</div>
                    @endif
                </td>
                <td>
                    <strong>{{ $tx->method }}</strong>
                    @if($tx->wallet_address)
                        <div style="font-size:11px;color:var(--text-muted);font-family:monospace;">{{ Str::limit($tx->wallet_address, 20) }}</div>
                    @endif
                </td>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        @if($tx->proof_image)
                            <a href="{{ $tx->proof_image_url }}" target="_blank" title="Click to view payment receipt">
                                <img src="{{ $tx->proof_image_url }}" style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid var(--accent);">
                            </a>
                        @endif
                        <div>
                            @if($tx->transaction_id)
                                <code style="font-size:11px; background:var(--bg-hover); padding:2px 6px; border-radius:4px; color:#fff;">
                                    {{ $tx->transaction_id }}
                                </code>
                            @elseif(!$tx->proof_image)
                                <span style="color:var(--text-muted); font-size:11px;">No proof attached</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    @if($tx->status === 'approved') <span class="badge-status green">✓ Approved</span>
                    @elseif($tx->status === 'rejected') <span class="badge-status red">✕ Rejected</span>
                    @else <span class="badge-status orange">⏳ Pending</span> @endif
                </td>
                <td style="color:var(--text-muted);font-size:12px">{{ $tx->notes ?? '—' }}</td>
                <td>{{ $tx->created_at->format('M d, Y H:i') }}</td>
                <td>
                    @if($tx->status === 'pending')
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('admin.transactions.approve', $tx) }}" onsubmit="return confirm('Approve deposit of ${{ number_format($tx->amount, 2) }} for {{ $tx->user?->name }}?')">
                            @csrf
                            <button class="btn btn-success btn-sm">✓ Approve</button>
                        </form>
                        <button class="btn btn-danger btn-sm" onclick="rejectModal({{ $tx->id }})">✕ Reject</button>
                    </div>
                    @else
                        <span style="color:var(--text-muted);font-size:12px">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:32px">No deposit requests</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $transactions->links() }}</div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:28px;width:440px;max-width:95vw">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px">Reject Deposit</div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Reason (optional)</label>
                <input type="text" name="reason" class="form-control" placeholder="e.g. Unverified payment method">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-danger">Reject</button>
                <button type="button" onclick="closeModal()" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function rejectModal(id) {
    document.getElementById('rejectForm').action = `/admin/transactions/${id}/reject`;
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
@endpush
