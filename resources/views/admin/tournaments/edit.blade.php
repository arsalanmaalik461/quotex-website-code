@extends('admin.layouts.app')
@section('title', 'Edit Tournament')
@section('page-title', '✏️ Edit Tournament: ' . $tournament->title)

@section('content')
<div style="max-width:900px; margin:0 auto;">
    <div class="card">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <div class="card-title">Edit Tournament Details</div>
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

        <form action="{{ route('admin.tournaments.update', $tournament->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Tournament Title <span style="color:var(--red)">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Crazy Wednesday" value="{{ old('title', $tournament->title) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status <span style="color:var(--red)">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ old('status', $tournament->status) === 'active' ? 'selected' : '' }}>Active / Upcoming</option>
                        <option value="completed" {{ old('status', $tournament->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Prize Pool ($) <span style="color:var(--red)">*</span></label>
                    <input type="number" step="0.01" name="prize_pool" class="form-control" placeholder="7500" value="{{ old('prize_pool', $tournament->prize_pool) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Entry Fee ($) <span style="color:var(--red)">*</span></label>
                    <input type="number" step="0.01" name="entry_fee" class="form-control" placeholder="10" value="{{ old('entry_fee', $tournament->entry_fee) }}" required>
                    <small style="color:var(--text-muted); font-size:11px;">Set 0 for Free</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Rebuy Cost ($)</label>
                    <input type="number" step="0.01" name="rebuy_cost" class="form-control" placeholder="10.00" value="{{ old('rebuy_cost', $tournament->rebuy_cost ?? 10.00) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Number of Rebuys</label>
                    <input type="number" name="number_of_rebuys" class="form-control" placeholder="100" value="{{ old('number_of_rebuys', $tournament->number_of_rebuys ?? 100) }}">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Duration <span style="color:var(--red)">*</span></label>
                    <input type="text" name="duration" class="form-control" placeholder="e.g. 1 day, 2 days" value="{{ old('duration', $tournament->duration) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Timezone Label</label>
                    <input type="text" name="timezone_label" class="form-control" placeholder="UTC+05:00" value="{{ old('timezone_label', $tournament->timezone_label ?? 'UTC+05:00') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Badge Text</label>
                    <input type="text" name="badge_text" class="form-control" placeholder="e.g. Until start: 1 day(s)" value="{{ old('badge_text', $tournament->badge_text) }}">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Start Date & Time</label>
                    <input type="datetime-local" name="start_date" class="form-control" value="{{ old('start_date', $tournament->start_date ? $tournament->start_date->format('Y-m-d\TH:i') : '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">End Date & Time</label>
                    <input type="datetime-local" name="end_date" class="form-control" value="{{ old('end_date', $tournament->end_date ? $tournament->end_date->format('Y-m-d\TH:i') : '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $tournament->sort_order) }}">
                </div>
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label class="form-label">Tournament Description & Rules</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $tournament->description) }}</textarea>
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

                @php
                    $distribution = $tournament->resolved_prize_distribution;
                @endphp

                <div id="prizeDistributionContainer" style="display:flex; flex-direction:column; gap:8px;">
                    @foreach($distribution as $item)
                        <div class="prize-row" style="display:grid; grid-template-columns: 1fr 1fr 40px; gap:10px; align-items:center;">
                            <input type="text" name="prize_places[]" class="form-control" placeholder="1 Place" value="{{ $item['place'] ?? '' }}">
                            <input type="text" name="prize_amounts[]" class="form-control" placeholder="1000$" value="{{ $item['prize'] ?? '' }}">
                            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:24px; border-top:1px solid var(--border); padding-top:20px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-primary); font-size:13px;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tournament->is_active) ? 'checked' : '' }}>
                    <span>🟢 <strong>Active</strong></span>
                </label>

                <div style="display:flex; gap:12px;">
                    <a href="{{ route('admin.tournaments.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-success" style="background:var(--green); color:#fff; font-weight:700; padding:10px 24px;">
                        Update Tournament
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
