@extends('admin.layouts.app')
@section('title', 'KYC Verifications')
@section('page-title', '🪪 KYC Verifications')

@section('content')

{{-- Stats Row --}}
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-label">Pending Reviews</div>
        <div class="stat-value orange">{{ $stats['pending'] }}</div>
        <div class="stat-sub">Awaiting admin action</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Approved Users</div>
        <div class="stat-value green">{{ $stats['approved'] }}</div>
        <div class="stat-sub">Verified accounts</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Rejected KYCs</div>
        <div class="stat-value red">{{ $stats['rejected'] }}</div>
        <div class="stat-sub">Requires user re-submission</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Submissions</div>
        <div class="stat-value blue">{{ $stats['total'] }}</div>
        <div class="stat-sub">Lifetime submissions</div>
    </div>
</div>

<div class="card">
    {{-- Header & Filters --}}
    <div class="card-header" style="flex-wrap:wrap; gap:16px;">
        <div class="flex items-center gap-3">
            <div class="card-title">All KYC Applications</div>
            <div class="flex gap-2">
                <a href="{{ route('admin.kyc.index', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' || !request('status') ? 'btn-primary' : 'btn-ghost' }}">
                    Pending ({{ $stats['pending'] }})
                </a>
                <a href="{{ route('admin.kyc.index', ['status' => 'approved']) }}" class="btn btn-sm {{ request('status') === 'approved' ? 'btn-primary' : 'btn-ghost' }}">
                    Approved ({{ $stats['approved'] }})
                </a>
                <a href="{{ route('admin.kyc.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-primary' : 'btn-ghost' }}">
                    Rejected ({{ $stats['rejected'] }})
                </a>
                <a href="{{ route('admin.kyc.index', ['status' => 'all']) }}" class="btn btn-sm {{ request('status') === 'all' ? 'btn-primary' : 'btn-ghost' }}">
                    All ({{ $stats['total'] }})
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.kyc.index') }}" class="flex gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, phone, email..." value="{{ request('search') }}" style="min-width: 220px;">
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.kyc.index', ['status' => request('status')]) }}" class="btn btn-ghost btn-sm">Clear</a>
            @endif
        </form>
    </div>

    {{-- Submissions Table --}}
    @if($verifications->isEmpty())
        <div style="text-align: center; padding: 48px; color: var(--text-muted);">
            <div style="font-size: 32px; margin-bottom: 8px;">📭</div>
            <p>No KYC submissions found matching this filter.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID / Date</th>
                        <th>User Account</th>
                        <th>Applicant Details</th>
                        <th>Document Proofs</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($verifications as $v)
                    <tr>
                        <td>
                            <strong>#{{ $v->id }}</strong><br>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ $v->created_at->format('M d, Y H:i') }}</span>
                        </td>
                        <td>
                            @if($v->user)
                                <a href="{{ route('admin.users.show', $v->user_id) }}" style="color: var(--accent); font-weight: 600; text-decoration: none;">
                                    {{ $v->user->name }}
                                </a><br>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ $v->user->email }}</span>
                            @else
                                <span style="color: var(--text-muted);">User deleted (#{{ $v->user_id }})</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $v->full_name }}</strong><br>
                            <span style="font-size: 12px; color: var(--text-secondary);">📞 {{ $v->phone }}</span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <div title="ID Front" style="text-align:center;">
                                    <img src="{{ $v->id_card_front_url }}" style="width: 44px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border); cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                    <div style="font-size: 9px; color: var(--text-muted);">Front</div>
                                </div>
                                <div title="ID Back" style="text-align:center;">
                                    <img src="{{ $v->id_card_back_url }}" style="width: 44px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border); cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                    <div style="font-size: 9px; color: var(--text-muted);">Back</div>
                                </div>
                                <div title="Live Selfie" style="text-align:center;">
                                    <img src="{{ $v->live_selfie_url }}" style="width: 32px; height: 32px; object-fit: cover; border-radius: 50%; border: 2px solid var(--accent); cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                    <div style="font-size: 9px; color: var(--accent);">Selfie</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($v->status === 'approved')
                                <span class="badge-status green">✓ Approved</span>
                            @elseif($v->status === 'rejected')
                                <span class="badge-status red">❌ Rejected</span>
                                @if($v->rejection_reason)
                                    <div style="font-size: 11px; color: var(--red); max-width: 180px; margin-top: 4px;" title="{{ $v->rejection_reason }}">
                                        {{ Str::limit($v->rejection_reason, 45) }}
                                    </div>
                                @endif
                            @else
                                <span class="badge-status orange">⏳ Pending</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.kyc.show', $v) }}" class="btn btn-ghost btn-sm" title="Inspect Full KYC">
                                    🔍 Review
                                </a>

                                @if($v->status !== 'approved')
                                <form method="POST" action="{{ route('admin.kyc.approve', $v) }}" onsubmit="return confirm('Approve KYC verification for {{ $v->full_name }}?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                @endif

                                @if($v->status !== 'rejected')
                                <button type="button" class="btn btn-danger btn-sm" onclick="openRejectModal('{{ $v->id }}', '{{ addslashes($v->full_name) }}')">
                                    Reject
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            {{ $verifications->links() }}
        </div>
    @endif
</div>

{{-- Reject Reason Modal --}}
<div id="rejectModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width: 100%; max-width: 480px; margin: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.8);">
        <div class="card-header">
            <div class="card-title" style="color:var(--red);">Reject KYC Verification</div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeRejectModal()">✕</button>
        </div>

        <form id="rejectForm" method="POST" action="">
            @csrf
            <div style="padding: 10px 0 20px 0;">
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">
                    Rejecting KYC for: <strong id="rejectUserName" style="color:#fff;"></strong>.
                    Please provide a clear rejection reason. This reason will be shown directly to the user so they can correct it and resubmit.
                </p>

                <div class="form-group">
                    <label class="form-label" for="rejection_reason">Rejection Reason *</label>
                    <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" placeholder="e.g. ID card photo is blurry or unreadable. Live selfie face does not clearly match the ID card photo. Please retake photos in good lighting." required></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openRejectModal(kycId, userName) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    const nameSpan = document.getElementById('rejectUserName');

    form.action = `/admin/kyc/${kycId}/reject`;
    nameSpan.innerText = userName;
    modal.style.display = 'flex';
    document.getElementById('rejection_reason').focus();
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.getElementById('rejection_reason').value = '';
}
</script>
@endpush

@endsection
