@extends('admin.layouts.app')
@section('title', 'Tournaments')
@section('page-title', '🏆 Tournaments Management')

@section('content')

{{-- Quick Stats Cards --}}
<div class="stats-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:20px;">
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Total Tournaments</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#fff; margin-top:4px;">{{ $totalCount }}</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Active Tournaments</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:var(--green); margin-top:4px;">{{ $activeCount }}</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Completed Tournaments</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:var(--text-muted); margin-top:4px;">{{ $completedCount }}</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Total Prize Pools</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:var(--accent); margin-top:4px;">${{ number_format($totalPrizePool, 0) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap:wrap; gap:16px; display:flex; align-items:center; justify-content:space-between;">
        <div class="flex items-center gap-3">
            <div class="card-title">All Tournaments</div>
            <div class="flex gap-2">
                <a href="{{ route('admin.tournaments.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-ghost' }}">
                    All
                </a>
                <a href="{{ route('admin.tournaments.index', ['status' => 'active']) }}" class="btn btn-sm {{ request('status') === 'active' ? 'btn-primary' : 'btn-ghost' }}">
                    Active Only
                </a>
                <a href="{{ route('admin.tournaments.index', ['status' => 'completed']) }}" class="btn btn-sm {{ request('status') === 'completed' ? 'btn-primary' : 'btn-ghost' }}">
                    Completed Only
                </a>
            </div>
        </div>

        <div>
            <a href="{{ route('admin.tournaments.create') }}" class="btn btn-success" style="background:var(--green); color:#fff; padding:8px 16px; border-radius:6px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <span>+ Add Tournament</span>
            </a>
        </div>
    </div>

    @if($tournaments->isEmpty())
        <div style="text-align: center; padding: 48px; color: var(--text-muted);">
            <div style="font-size: 36px; margin-bottom: 8px;">🏆</div>
            <p>No tournaments created yet. Click "+ Add Tournament" to create your first trading tournament.</p>
        </div>
    @else
        <div class="table-wrap">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border); text-align:left; color:var(--text-muted); font-size:12px;">
                        <th style="padding:12px 14px;">Tournament Title</th>
                        <th style="padding:12px 14px;">Prize Pool</th>
                        <th style="padding:12px 14px;">Entry Fee</th>
                        <th style="padding:12px 14px;">Duration</th>
                        <th style="padding:12px 14px;">Badge / Status Note</th>
                        <th style="padding:12px 14px;">Status</th>
                        <th style="padding:12px 14px; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tournaments as $t)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05); font-size:13px;">
                        <td style="padding:12px 14px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:20px;">🏆</span>
                                <div>
                                    <strong style="color:#fff; font-size:14px;">{{ $t->title }}</strong>
                                    @if($t->description)
                                        <div style="font-size:11px; color:var(--text-muted); max-width:240px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            {{ $t->description }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 14px; font-weight:800; color:var(--green); font-size:14px;">
                            ${{ number_format($t->prize_pool, 0) }}
                        </td>
                        <td style="padding:12px 14px; font-weight:700; color:#cbd5e1;">
                            {{ (float)$t->entry_fee == 0 ? 'FREE (0 $)' : '$'.number_format($t->entry_fee, 0) }}
                        </td>
                        <td style="padding:12px 14px; color:#cbd5e1;">
                            ⏱ {{ $t->duration }}
                        </td>
                        <td style="padding:12px 14px; font-size:12px; color:var(--text-muted);">
                            <span style="background:rgba(2,111,211,0.15); color:#2b99ff; padding:2px 8px; border-radius:4px; font-weight:600;">
                                {{ $t->badge_text ?? 'Active' }}
                            </span>
                        </td>
                        <td style="padding:12px 14px;">
                            @if($t->status === 'active')
                                <span class="badge" style="background:rgba(0,209,122,0.15); color:#00d17a; border:1px solid rgba(0,209,122,0.3); padding:4px 8px; border-radius:4px; font-weight:700;">
                                    ● Active
                                </span>
                            @else
                                <span class="badge" style="background:rgba(120,134,156,0.15); color:#8b96a8; border:1px solid rgba(120,134,156,0.2); padding:4px 8px; border-radius:4px; font-weight:600;">
                                    ✕ Completed
                                </span>
                            @endif
                        </td>
                        <td style="padding:12px 14px; text-align:right;">
                            <div style="display:inline-flex; align-items:center; gap:8px;">
                                <a href="{{ route('admin.tournaments.edit', $t->id) }}" class="btn btn-sm" style="font-size:11px; padding:4px 8px; border:1px solid var(--border); background:var(--bg-primary); color:var(--text-primary); text-decoration:none; border-radius:4px;">
                                    Edit
                                </a>

                                <form action="{{ route('admin.tournaments.toggle', $t->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm" style="font-size:11px; padding:4px 8px; border:1px solid var(--border); background:var(--bg-primary); color:var(--text-secondary); border-radius:4px; cursor:pointer;" title="{{ $t->status === 'active' ? 'Mark Completed' : 'Mark Active' }}">
                                        {{ $t->status === 'active' ? 'Complete' : 'Activate' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.tournaments.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Delete tournament {{ $t->title }}?')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm" style="font-size:11px; padding:4px 8px; border:1px solid rgba(255,76,106,0.3); background:rgba(255,76,106,0.1); color:#ff4c6a; border-radius:4px; cursor:pointer;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            {{ $tournaments->links() }}
        </div>
    @endif
</div>

@endsection
