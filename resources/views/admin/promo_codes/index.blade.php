@extends('admin.layouts.app')
@section('title', 'Market Promo Codes')
@section('page-title', '🎁 Market Promo Codes Management')

@section('content')

{{-- Quick Stats Cards --}}
<div class="stats-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:20px;">
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Total Promo Codes</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#fff; margin-top:4px;">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Active Promo Codes</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:var(--green); margin-top:4px;">{{ $stats['active'] }}</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Deposit Bonuses</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#f59e0b; margin-top:4px;">{{ $stats['deposit'] }}</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Risk Free Codes</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#3b82f6; margin-top:4px;">{{ $stats['risk_free'] }}</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px 20px;">
        <div class="text-muted" style="font-size:12px;">Cashback Codes</div>
        <div class="stat-value" style="font-size:24px; font-weight:800; color:#9c27b0; margin-top:4px;">{{ $stats['cashback'] }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap:wrap; gap:16px; display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <div class="card-title">Promo Code Directory</div>
            
            {{-- Category Filter Pills --}}
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-sm {{ !request('category') || request('category') === 'all' ? 'btn-primary' : 'btn-ghost' }}">
                    All
                </a>
                @foreach($categories as $key => $label)
                    <a href="{{ route('admin.promo-codes.index', ['category' => $key]) }}" class="btn btn-sm {{ request('category') === $key ? 'btn-primary' : 'btn-ghost' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            <a href="{{ route('admin.promo-codes.create') }}" class="btn btn-success" style="background:var(--green); color:#fff; padding:8px 16px; border-radius:6px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <span>+ Add Promo Code</span>
            </a>
        </div>
    </div>

    @if($promoCodes->isEmpty())
        <div style="text-align: center; padding: 48px; color: var(--text-muted);">
            <div style="font-size: 36px; margin-bottom: 8px;">🎁</div>
            <p>No promo codes found matching your criteria. Click "+ Add Promo Code" to create one.</p>
        </div>
    @else
        <div class="table-wrap">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border); text-align:left; color:var(--text-muted); font-size:12px;">
                        <th style="padding:12px 16px;">CODE</th>
                        <th style="padding:12px 16px;">CATEGORY</th>
                        <th style="padding:12px 16px;">REWARD / VALUE</th>
                        <th style="padding:12px 16px;">VALIDITY</th>
                        <th style="padding:12px 16px;">STATUS</th>
                        <th style="padding:12px 16px; text-align:right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($promoCodes as $promo)
                        @php
                            $badgeColors = [
                                'risk_free'        => ['bg' => 'rgba(59,130,246,0.15)', 'color' => '#60a5fa'],
                                'cashback'         => ['bg' => 'rgba(156,39,176,0.15)', 'color' => '#ce93d8'],
                                'deposit_bonus'    => ['bg' => 'rgba(245,158,11,0.15)', 'color' => '#fbbf24'],
                                'turnover_percent' => ['bg' => 'rgba(233,30,99,0.15)', 'color' => '#f48fb1'],
                                'balance_bonus'    => ['bg' => 'rgba(6,182,212,0.15)', 'color' => '#67e8f9'],
                                'cancel_points'    => ['bg' => 'rgba(20,184,166,0.15)', 'color' => '#5eead4'],
                            ];
                            $badge = $badgeColors[$promo->category] ?? ['bg' => 'rgba(255,255,255,0.1)', 'color' => '#fff'];
                        @endphp
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:12px 16px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-family:monospace; font-weight:800; font-size:14px; background:#131722; padding:4px 8px; border-radius:6px; border:1px solid #232d3f; color:#fff;">
                                        {{ $promo->code }}
                                    </span>
                                </div>
                                @if($promo->description)
                                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">{{ $promo->description }}</div>
                                @endif
                            </td>

                            <td style="padding:12px 16px;">
                                <span style="background:{{ $badge['bg'] }}; color:{{ $badge['color'] }}; font-size:11px; font-weight:700; padding:4px 10px; border-radius:12px;">
                                    {{ $categories[$promo->category] ?? ucfirst($promo->category) }}
                                </span>
                            </td>

                            <td style="padding:12px 16px;">
                                <div style="font-weight:700; color:#fff; font-size:14px;">
                                    @if($promo->reward_type === 'percentage')
                                        {{ $promo->reward_value }}%
                                    @elseif($promo->reward_type === 'fixed_amount')
                                        ${{ number_format($promo->reward_value, 2) }}
                                    @else
                                        {{ $promo->reward_value }} points
                                    @endif
                                </div>
                                @if($promo->min_deposit)
                                    <div style="font-size:11px; color:var(--text-muted);">Min. Deposit: ${{ number_format($promo->min_deposit, 2) }}</div>
                                @endif
                            </td>

                            <td style="padding:12px 16px;">
                                @if($promo->is_unlimited || !$promo->expires_at)
                                    <span style="color:#00e676; font-weight:600; font-size:13px;">♾️ Unlimited</span>
                                @else
                                    <span style="color:var(--text-secondary); font-size:13px;">
                                        {{ $promo->expires_at->format('d/m/Y H:i') }}
                                        @if($promo->expires_at < now())
                                            <span style="color:var(--red); font-size:11px; font-weight:700; margin-left:4px;">(Expired)</span>
                                        @endif
                                    </span>
                                @endif
                            </td>

                            <td style="padding:12px 16px;">
                                @if($promo->is_active)
                                    <span class="badge badge-success" style="background:rgba(0,209,122,0.15); color:var(--green); padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700;">Active</span>
                                @else
                                    <span class="badge badge-danger" style="background:rgba(255,76,106,0.15); color:var(--red); padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700;">Inactive</span>
                                @endif
                            </td>

                            <td style="padding:12px 16px; text-align:right;">
                                <div style="display:inline-flex; align-items:center; gap:8px;">
                                    <form action="{{ route('admin.promo-codes.toggle', $promo) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-ghost" title="{{ $promo->is_active ? 'Deactivate' : 'Activate' }}">
                                            {{ $promo->is_active ? '⏸️ Disable' : '▶️ Enable' }}
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.promo-codes.edit', $promo) }}" class="btn btn-sm btn-primary" title="Edit">
                                        ✏️ Edit
                                    </a>

                                    <form action="{{ route('admin.promo-codes.destroy', $promo) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete promo code \'{{ $promo->code }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:16px 20px;">
            {{ $promoCodes->links() }}
        </div>
    @endif
</div>

@endsection
