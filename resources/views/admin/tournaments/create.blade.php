@extends('admin.layouts.app')
@section('title', 'Add Tournament')
@section('page-title', '🏆 Create New Tournament')

@section('content')
<div style="max-width:900px; margin:0 auto;">
    <div class="card">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <div class="card-title">Tournament Information</div>
            <a href="{{ route('admin.tournaments.index') }}" class="btn btn-sm btn-ghost">
                ← Back to List
            </a>
        </div>

        @if($errors->any())
            <div style="background:rgba(255,76,106,0.15); border:1px solid var(--red); color:var(--red); padding:12px 16px; border-radius:8px; margin-bottom:20px;">
                <ul style="margin:0; padding-left:20px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.tournaments.store') }}" method="POST">
            @csrf

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Tournament Title <span style="color:var(--red)">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Crazy Wednesday" value="{{ old('title') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status <span style="color:var(--red)">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active / Upcoming</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Prize Pool ($) <span style="color:var(--red)">*</span></label>
                    <input type="number" step="0.01" name="prize_pool" class="form-control" placeholder="7500" value="{{ old('prize_pool', '7500.00') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Entry Fee ($) <span style="color:var(--red)">*</span></label>
                    <input type="number" step="0.01" name="entry_fee" class="form-control" placeholder="10" value="{{ old('entry_fee', '10.00') }}" required>
                    <small style="color:var(--text-muted); font-size:11px;">Set 0 for Free</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Rebuy Cost ($)</label>
                    <input type="number" step="0.01" name="rebuy_cost" class="form-control" placeholder="10.00" value="{{ old('rebuy_cost', '10.00') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Number of Rebuys</label>
                    <input type="number" name="number_of_rebuys" class="form-control" placeholder="100" value="{{ old('number_of_rebuys', 100) }}">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Duration <span style="color:var(--red)">*</span></label>
                    <input type="text" name="duration" class="form-control" placeholder="e.g. 1 day, 2 days" value="{{ old('duration', '1 day') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Timezone Label</label>
                    <input type="text" name="timezone_label" class="form-control" placeholder="UTC+05:00" value="{{ old('timezone_label', 'UTC+05:00') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Badge Text</label>
                    <input type="text" name="badge_text" class="form-control" placeholder="e.g. Until start: 1 day(s)" value="{{ old('badge_text', 'Until start: 1 day(s)') }}">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Start Date & Time</label>
                    <input type="datetime-local" name="start_date" class="form-control" value="{{ old('start_date') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">End Date & Time</label>
                    <input type="datetime-local" name="end_date" class="form-control" value="{{ old('end_date') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                </div>
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label class="form-label">Tournament Description & Rules</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Total prize pool is $7500. To participate you need to register and pay the entrance fee, after that $100 will be deposited to a special tournament account. Initial conditions in the tournament are the same for all participants...">{{ old('description', 'Total prize pool is $7500. To participate you need to register and pay the entrance fee, after that $100 will be deposited to a special tournament account. Initial conditions in the tournament are the same for all participants. During one day, participants need to make trades on the tournament account. And 5 traders with the biggest tournament balance will become winners.') }}</textarea>
            </div>

            {{-- Prize Pool Distribution Section --}}
            <div style="background:#131722; border:1px solid #232d3f; border-radius:10px; padding:18px 20px; margin-top:20px; margin-bottom:20px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <div>
                        <div style="font-weight:700; font-size:14px; color:#fff;">🏆 Prize Pool Distribution Places</div>
                        <div style="font-size:11px; color:var(--text-muted);">Configure prize amounts for winning ranks</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-ghost" onclick="addPrizePlaceRow()">+ Add Place</button>
                </div>

                <div id="prizeDistributionContainer" style="display:flex; flex-direction:column; gap:8px;">
                    <div class="prize-row" style="display:grid; grid-template-columns: 1fr 1fr 40px; gap:10px; align-items:center;">
                        <input type="text" name="prize_places[]" class="form-control" placeholder="1 Place" value="1 Place">
                        <input type="text" name="prize_amounts[]" class="form-control" placeholder="3000$" value="3000$">
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>
                    </div>
                    <div class="prize-row" style="display:grid; grid-template-columns: 1fr 1fr 40px; gap:10px; align-items:center;">
                        <input type="text" name="prize_places[]" class="form-control" placeholder="2 Place" value="2 Place">
                        <input type="text" name="prize_amounts[]" class="form-control" placeholder="2000$" value="2000$">
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>
                    </div>
                    <div class="prize-row" style="display:grid; grid-template-columns: 1fr 1fr 40px; gap:10px; align-items:center;">
                        <input type="text" name="prize_places[]" class="form-control" placeholder="3 Place" value="3 Place">
                        <input type="text" name="prize_amounts[]" class="form-control" placeholder="1000$" value="1000$">
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>
                    </div>
                    <div class="prize-row" style="display:grid; grid-template-columns: 1fr 1fr 40px; gap:10px; align-items:center;">
                        <input type="text" name="prize_places[]" class="form-control" placeholder="4 Place" value="4 Place">
                        <input type="text" name="prize_amounts[]" class="form-control" placeholder="900$" value="900$">
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>
                    </div>
                    <div class="prize-row" style="display:grid; grid-template-columns: 1fr 1fr 40px; gap:10px; align-items:center;">
                        <input type="text" name="prize_places[]" class="form-control" placeholder="5 Place" value="5 Place">
                        <input type="text" name="prize_amounts[]" class="form-control" placeholder="600$" value="600$">
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>
                    </div>
                </div>
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:24px; border-top:1px solid var(--border); padding-top:20px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-primary); font-size:13px;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span>🟢 <strong>Active immediately</strong></span>
                </label>

                <div style="display:flex; gap:12px;">
                    <a href="{{ route('admin.tournaments.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-success" style="background:var(--green); color:#fff; font-weight:700; padding:10px 24px;">
                        Save Tournament
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function addPrizePlaceRow() {
        const container = document.getElementById('prizeDistributionContainer');
        const count = container.children.length + 1;
        const row = document.createElement('div');
        row.className = 'prize-row';
        row.style.cssText = 'display:grid; grid-template-columns: 1fr 1fr 40px; gap:10px; align-items:center;';
        row.innerHTML = `
            <input type="text" name="prize_places[]" class="form-control" placeholder="${count} Place" value="${count} Place">
            <input type="text" name="prize_amounts[]" class="form-control" placeholder="500$" value="">
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>
        `;
        container.appendChild(row);
    }
</script>
@endsection
