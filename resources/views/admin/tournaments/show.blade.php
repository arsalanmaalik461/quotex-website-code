@extends('admin.layouts.app')
@section('title', 'Tournament Participants — ' . $tournament->title)
@section('page-title', '🏆 ' . $tournament->title . ' — Participants')

@section('content')

{{-- Quick Stats Cards --}}
<div class="stats-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:20px;">
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Total Registered</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#fff; margin-top:4px;">{{ $totalRegistrations }}</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Entry Fees Collected</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:var(--green); margin-top:4px;">${{ number_format($totalEntryFees, 2) }}</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Prize Pool</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:var(--accent); margin-top:4px;">${{ number_format($tournament->prize_pool, 0) }}</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Entry Fee</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#fff; margin-top:4px;">{{ (float)$tournament->entry_fee == 0 ? 'FREE' : '$' . number_format($tournament->entry_fee, 0) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap:wrap; gap:16px; display:flex; align-items:center; justify-content:space-between;">
        <div class="card-title">Registered Users</div>
        <a href="{{ route('admin.tournaments.index') }}" class="btn btn-sm" style="font-size:12px; padding:6px 12px; border:1px solid var(--border); background:var(--bg-primary); color:var(--text-primary); text-decoration:none; border-radius:4px;">
            ← Back to Tournaments
        </a>
    </div>

    @if($registrations->isEmpty())
        <div style="text-align: center; padding: 48px; color: var(--text-muted);">
            <div style="font-size: 36px; margin-bottom: 8px;">👥</div>
            <p>No users have registered for this tournament yet.</p>
        </div>
    @else
        <div class="table-wrap">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border); text-align:left; color:var(--text-muted); font-size:12px;">
                        <th style="padding:12px 14px;">User</th>
                        <th style="padding:12px 14px;">Email</th>
                        <th style="padding:12px 14px;">Entry Fee Paid</th>
                        <th style="padding:12px 14px;">Tournament Balance</th>
                        <th style="padding:12px 14px;">Status</th>
                        <th style="padding:12px 14px;">Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrations as $r)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05); font-size:13px;">
                        <td style="padding:12px 14px;">
                            <strong style="color:#fff; font-size:14px;">{{ $r->user->name ?? '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-muted);">ID: {{ $r->user_id }}</div>
                        </td>
                        <td style="padding:12px 14px; color:#cbd5e1;">
                            {{ $r->user->email ?? '—' }}
                        </td>
                        <td style="padding:12px 14px; font-weight:700; color:#cbd5e1;">
                            ${{ number_format($r->entry_fee_paid, 2) }}
                        </td>
                        <td style="padding:12px 14px; font-weight:700; color:var(--green);">
                            ${{ number_format($r->tournament_balance, 2) }}
                        </td>
                        <td style="padding:12px 14px;">
                            <span class="badge" style="background:rgba(0,209,122,0.15); color:#00d17a; border:1px solid rgba(0,209,122,0.3); padding:4px 8px; border-radius:4px; font-weight:700;">
                                {{ ucfirst($r->status) }}
                            </span>
                        </td>
                        <td style="padding:12px 14px; color:#cbd5e1;">
                            {{ $r->created_at ? $r->created_at->format('d M Y, H:i') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            {{ $registrations->links() }}
        </div>
    @endif
</div>

@endsection
