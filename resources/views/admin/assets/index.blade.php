@extends('admin.layouts.app')
@section('title', 'Trading Pairs')
@section('page-title', '📊 Trading Pairs & Assets')

@section('content')

<div class="flex justify-between items-center mb-4">
    <div class="flex gap-2">
        @foreach(['','forex','crypto','commodity','index','otc','signal'] as $t)
        <a href="{{ route('admin.assets.index', array_merge(request()->query(), $t ? ['type'=>$t] : [])) }}"
           class="btn btn-sm {{ request('type') === $t || (!request('type') && $t === '') ? 'btn-primary' : 'btn-ghost' }}">
            {{ $t ? ucfirst($t) : 'All' }}
        </a>
        @endforeach
    </div>
    <a href="{{ route('admin.assets.create') }}" class="btn btn-primary">+ Add New Pair</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Symbol</th><th>Name</th><th>Type</th><th>Payout %</th>
                    <th>OTC</th><th>Control Mode</th><th>Win Side</th><th>Trades</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($assets as $asset)
            <tr>
                <td style="color:var(--text-muted)">{{ $asset->id }}</td>
                <td>
                    <strong style="color:var(--text-primary)">
                        {{ $asset->flag }} {{ $asset->symbol }}
                    </strong>
                </td>
                <td>{{ $asset->name }}</td>
                <td><span class="badge-status blue">{{ $asset->type }}</span></td>
                <td>{{ $asset->payout_percent }}%</td>
                <td>{{ $asset->is_otc ? '✓' : '—' }}</td>
                <td>
                    @if($asset->control_mode === 'pump')
                        <span class="badge-status green">▲ PUMP {{ $asset->max_pump_percent }}%</span>
                    @elseif($asset->control_mode === 'crash')
                        <span class="badge-status red">▼ CRASH {{ $asset->max_crash_percent }}%</span>
                    @else
                        <span class="badge-status gray">Auto</span>
                    @endif
                </td>
                <td>
                    @if($asset->win_side !== 'none')
                        <span class="badge-status orange">{{ str_replace('_',' ',ucfirst($asset->win_side)) }}</span>
                    @else
                        <span style="color:var(--text-muted)">—</span>
                    @endif
                </td>
                <td>{{ $asset->trades_count }}</td>
                <td>
                    <button class="badge-status {{ $asset->is_active ? 'green' : 'red' }}"
                            onclick="toggleAsset({{ $asset->id }}, this)"
                            style="cursor:pointer;border:none">
                        {{ $asset->is_active ? '● Active' : '○ Inactive' }}
                    </button>
                </td>
                <td>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.assets.control', $asset) }}" class="btn btn-warning btn-sm" title="OTC Control">⚙</a>
                        <a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-ghost btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}"
                              onsubmit="return confirm('Delete {{ $asset->symbol }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="11" style="text-align:center;color:var(--text-muted);padding:32px">No assets found</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $assets->links() }}</div>
</div>

@endsection

@push('scripts')
<script>
function toggleAsset(id, btn) {
    fetch(`/admin/assets/${id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            btn.className = `badge-status ${d.is_active ? 'green' : 'red'}`;
            btn.textContent = d.is_active ? '● Active' : '○ Inactive';
        }
    });
}
</script>
@endpush
