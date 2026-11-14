@extends('admin.layouts.app')
@section('title', 'Create Trading Signal')
@section('page-title', '📡 Create Trading Signal')

@section('content')

<div style="max-width: 700px; margin: 0 auto;">
    <div class="card">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between;">
            <div class="card-title">New Signal Details</div>
            <a href="{{ route('admin.signals.index') }}" class="btn btn-ghost" style="text-decoration:none; color:var(--text-muted); font-size:12px;">
                ← Back to Signals
            </a>
        </div>

        <form action="{{ route('admin.signals.store') }}" method="POST" style="padding: 20px 24px;">
            @csrf

            <!-- Asset / Pair Selector -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#fff; margin-bottom:8px;">
                    Trading Pair / Asset <span style="color:var(--red);">*</span>
                </label>
                <select name="asset_id" id="asset_id" class="form-control" style="width:100%; padding:10px 12px; background:var(--bg-primary); border:1px solid var(--border); border-radius:6px; color:#fff; font-size:14px;" required>
                    <option value="" disabled selected>-- Select Trading Asset / Pair --</option>
                    @foreach($assets as $a)
                        <option value="{{ $a->id }}" data-payout="{{ $a->payout_percent }}">
                            {{ $a->symbol }} — {{ $a->name ?? $a->type }} (Payout: {{ $a->payout_percent }}%)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Direction Selection -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#fff; margin-bottom:8px;">
                    Signal Direction <span style="color:var(--red);">*</span>
                </label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <label style="border:2px solid #23342c; background:#12231b; border-radius:8px; padding:14px; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:all 0.15s;" id="label-call">
                        <input type="radio" name="direction" value="call" checked style="accent-color:#00d17a; width:18px; height:18px;">
                        <span style="color:#00d17a; font-weight:800; font-size:15px;">▲ CALL / BUY (UP)</span>
                    </label>

                    <label style="border:2px solid #3c1e24; background:#241216; border-radius:8px; padding:14px; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:all 0.15s;" id="label-put">
                        <input type="radio" name="direction" value="put" style="accent-color:#ff4c6a; width:18px; height:18px;">
                        <span style="color:#ff4c6a; font-weight:800; font-size:15px;">▼ PUT / SELL (DOWN)</span>
                    </label>
                </div>
            </div>

            <!-- Duration Selection -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#fff; margin-bottom:8px;">
                    Signal Duration (Expiry Time) <span style="color:var(--red);">*</span>
                </label>
                
                <!-- Quick Duration Presets -->
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px;">
                    <button type="button" class="btn btn-sm btn-ghost dur-btn" onclick="setDuration(1)">1 min</button>
                    <button type="button" class="btn btn-sm btn-ghost dur-btn" onclick="setDuration(2)">2 min</button>
                    <button type="button" class="btn btn-sm btn-ghost dur-btn" onclick="setDuration(5)">5 min</button>
                    <button type="button" class="btn btn-sm btn-ghost dur-btn" onclick="setDuration(10)">10 min</button>
                    <button type="button" class="btn btn-sm btn-primary dur-btn active" onclick="setDuration(15)">15 min</button>
                    <button type="button" class="btn btn-sm btn-ghost dur-btn" onclick="setDuration(30)">30 min</button>
                    <button type="button" class="btn btn-sm btn-ghost dur-btn" onclick="setDuration(60)">1 hour</button>
                    <button type="button" class="btn btn-sm btn-ghost dur-btn" onclick="setDuration(240)">4 hours</button>
                </div>

                <div style="display:flex; align-items:center; gap:10px;">
                    <input type="number" name="duration_minutes" id="duration_minutes" class="form-control" value="15" min="0.5" step="any" style="width:140px; padding:8px 12px; background:var(--bg-primary); border:1px solid var(--border); border-radius:6px; color:#fff; font-size:14px; font-weight:700;" required>
                    <span style="color:var(--text-muted); font-size:13px;">Minutes (e.g. 15 = 15:00 expiration)</span>
                </div>
            </div>

            <!-- Start Time (Optional) -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#fff; margin-bottom:4px;">
                    Start Time
                </label>
                <div style="color:var(--text-muted); font-size:12px; margin-bottom:8px;">Leave empty to start immediately. Expiration will automatically be (Start Time + Duration).</div>
                <input type="datetime-local" name="start_time" class="form-control" style="width:100%; padding:8px 12px; background:var(--bg-primary); border:1px solid var(--border); border-radius:6px; color:#fff; font-size:13px;">
            </div>

            <!-- Notes (Optional) -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#fff; margin-bottom:8px;">
                    Admin Notes / Strategy Info (Optional)
                </label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. Strong breakout on 15m timeframe" style="width:100%; padding:8px 12px; background:var(--bg-primary); border:1px solid var(--border); border-radius:6px; color:#fff; font-size:13px;">
            </div>

            <div style="display:flex; align-items:center; justify-content:flex-end; gap:12px;">
                <a href="{{ route('admin.signals.index') }}" class="btn btn-ghost" style="padding:10px 18px; text-decoration:none; color:var(--text-muted);">
                    Cancel
                </a>
                <button type="submit" class="btn btn-success" style="padding:10px 24px; font-weight:800; font-size:14px; background:var(--green); color:#fff; border:none; border-radius:6px; cursor:pointer;">
                    🚀 Publish Live Signal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function setDuration(mins) {
    document.getElementById('duration_minutes').value = mins;
    document.querySelectorAll('.dur-btn').forEach(b => {
        b.classList.remove('btn-primary', 'active');
        b.classList.add('btn-ghost');
    });
    event.target.classList.remove('btn-ghost');
    event.target.classList.add('btn-primary', 'active');
}
</script>

@endsection
