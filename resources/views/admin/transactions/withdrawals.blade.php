@extends('admin.layouts.app')
@section('title', 'Withdrawal Requests')
@section('page-title', '🏦 Withdrawal Requests')

@section('content')

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
        <div class="card-title">Withdrawals ({{ $transactions->total() }})</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>User & KYC</th><th>Amount</th><th>Method & Destination</th>
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
                    <div style="margin-top:4px;">
                        @if($tx->user?->isKycVerified())
                            <span class="badge-status green" style="font-size:10px;">✓ KYC Verified</span>
                        @else
                            <span class="badge-status red" style="font-size:10px;">❌ KYC Unverified</span>
                        @endif
                    </div>
                </td>
                <td style="color:var(--red);font-weight:700;font-size:15px">
                    -${{ number_format($tx->amount, 2) }}
                    <div style="font-size:11px; color:var(--text-muted);">Balance: ${{ number_format($tx->user?->live_balance ?? 0, 2) }}</div>
                </td>
                <td>
                    <strong>{{ $tx->method }}</strong>
                    @if($tx->wallet_address)
                        <div style="margin-top:2px;">
                            <code style="font-size:11px; background:var(--bg-hover); padding:2px 6px; border-radius:4px; color:#fff;">
                                {{ $tx->wallet_address }}
                            </code>
                        </div>
                    @endif
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
                        <form method="POST" action="{{ route('admin.transactions.approve', $tx) }}">
                            @csrf
                            <button class="btn btn-success btn-sm">✓ Approve</button>
                        </form>
                        @if($approvalEmailsEnabled ?? false)
                            <form method="POST" action="{{ route('admin.transactions.approve-with-mail', $tx) }}">
                                @csrf
                                <button class="btn btn-primary btn-sm">Approve & Mail</button>
                            </form>
                        @endif
                        <button class="btn btn-danger btn-sm" onclick="rejectModal({{ $tx->id }}, false)">✕ Reject</button>
                        <button class="btn btn-warning btn-sm" onclick="rejectModal({{ $tx->id }}, true)">Reject & Mail</button>
                    </div>
                    @else
                        <span style="color:var(--text-muted);font-size:12px">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:32px">No withdrawal requests</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $transactions->links() }}</div>
</div>

<div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:28px;width:440px;max-width:95vw">
        <div id="rejectModalTitle" style="font-size:16px;font-weight:700;margin-bottom:16px">Reject Withdrawal</div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="form-group">
                <label id="rejectReasonLabel" class="form-label">Reason (optional)</label>
                <div id="defaultReasonPreview" style="display:none;margin-bottom:12px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:12px;color:var(--text-secondary);font-size:12px;line-height:1.6;">
                    <div style="color:var(--orange);font-weight:700;margin-bottom:4px;">Default Reason</div>
                    <div id="defaultReasonText"></div>
                </div>
                <input id="rejectReasonInput" type="text" name="reason" class="form-control" placeholder="Optional custom reason">
            </div>
            <div class="flex gap-2">
                <button id="rejectSubmitButton" type="submit" class="btn btn-danger">Reject</button>
                <button type="button" onclick="closeModal()" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function rejectModal(id, sendMail) {
    const form = document.getElementById('rejectForm');
    const title = document.getElementById('rejectModalTitle');
    const label = document.getElementById('rejectReasonLabel');
    const input = document.getElementById('rejectReasonInput');
    const submit = document.getElementById('rejectSubmitButton');
    const defaultPreview = document.getElementById('defaultReasonPreview');
    const defaultReasonText = document.getElementById('defaultReasonText');
    const defaultReason = @json($defaultRejectionReason ?? '');

    form.action = sendMail ? `/admin/transactions/${id}/reject-with-mail` : `/admin/transactions/${id}/reject`;
    title.textContent = sendMail ? 'Reject Withdrawal & Send Email' : 'Reject Withdrawal';
    label.textContent = sendMail ? 'Optional Custom Reason' : 'Reason (optional)';
    input.required = false;
    defaultPreview.style.display = sendMail ? 'block' : 'none';
    defaultReasonText.textContent = defaultReason || 'No default reason configured. Enter a custom reason or configure one in Email Settings.';
    submit.textContent = sendMail ? 'Reject & Send Mail' : 'Reject';
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('rejectReasonInput').value = '';
    document.getElementById('defaultReasonPreview').style.display = 'none';
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
@endpush
