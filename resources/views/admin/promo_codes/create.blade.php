@extends('admin.layouts.app')
@section('title', 'Add Promo Code')
@section('page-title', '➕ Create New Promo Code')

@section('content')
<div style="max-width:800px; margin:0 auto;">
    <div class="card">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <div class="card-title">Promo Code Information</div>
            <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-sm btn-ghost">
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

        <form action="{{ route('admin.promo-codes.store') }}" method="POST">
            @csrf

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                {{-- Promo Code Name --}}
                <div class="form-group">
                    <label class="form-label">Promo Code <span style="color:var(--red)">*</span></label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. DEPOSIT50" value="{{ old('code') }}" required style="text-transform:uppercase; font-family:monospace; font-weight:700;">
                    <small style="color:var(--text-muted); font-size:11px;">Unique uppercase promo identifier.</small>
                </div>

                {{-- Category --}}
                <div class="form-group">
                    <label class="form-label">Category <span style="color:var(--red)">*</span></label>
                    <select name="category" class="form-control" required>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                {{-- Reward Type --}}
                <div class="form-group">
                    <label class="form-label">Reward Type <span style="color:var(--red)">*</span></label>
                    <select name="reward_type" id="rewardTypeSelect" class="form-control" required onchange="updateRewardTypeLabel()">
                        <option value="percentage" {{ old('reward_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="fixed_amount" {{ old('reward_type') === 'fixed_amount' ? 'selected' : '' }}>Fixed Amount ($)</option>
                        <option value="points" {{ old('reward_type') === 'points' ? 'selected' : '' }}>Points / Pips</option>
                    </select>
                </div>

                {{-- Reward Value --}}
                <div class="form-group">
                    <label class="form-label" id="rewardValueLabel">Bonus Percentage / Value <span style="color:var(--red)">*</span></label>
                    <input type="number" step="0.01" name="reward_value" class="form-control" placeholder="e.g. 50" value="{{ old('reward_value', '50.00') }}" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                {{-- Minimum Deposit --}}
                <div class="form-group">
                    <label class="form-label">Minimum Deposit Requirement ($)</label>
                    <input type="number" step="0.01" name="min_deposit" class="form-control" placeholder="e.g. 10.00 (optional)" value="{{ old('min_deposit') }}">
                    <small style="color:var(--text-muted); font-size:11px;">Leave empty or 0 if no minimum deposit.</small>
                </div>

                {{-- Expiration Date --}}
                <div class="form-group">
                    <label class="form-label">Expiration Date & Time</label>
                    <input type="datetime-local" name="expires_at" id="expiresAtInput" class="form-control" value="{{ old('expires_at') }}">
                    <small style="color:var(--text-muted); font-size:11px;">Ignored if "Unlimited" is checked below.</small>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                </div>

                <div style="display:flex; flex-direction:column; gap:10px; justify-content:center; padding-top:14px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-primary); font-size:13px;">
                        <input type="checkbox" name="is_unlimited" id="isUnlimitedCheckbox" value="1" {{ old('is_unlimited') ? 'checked' : '' }} onchange="toggleUnlimited()">
                        <span>♾️ <strong>Unlimited Validity</strong> (never expires)</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-primary); font-size:13px;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span>🟢 <strong>Active immediately</strong></span>
                    </label>
                </div>
            </div>

            {{-- Description / Tooltip --}}
            <div class="form-group">
                <label class="form-label">Description / Helper Text</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this promo code bonus terms...">{{ old('description') }}</textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px; border-top:1px solid var(--border); padding-top:20px;">
                <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-success" style="background:var(--green); color:#fff; font-weight:700; padding:10px 24px;">
                    Save Promo Code
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleUnlimited() {
        const checkbox = document.getElementById('isUnlimitedCheckbox');
        const input = document.getElementById('expiresAtInput');
        if (checkbox.checked) {
            input.disabled = true;
            input.style.opacity = '0.5';
        } else {
            input.disabled = false;
            input.style.opacity = '1';
        }
    }
    toggleUnlimited();

    function updateRewardTypeLabel() {
        const type = document.getElementById('rewardTypeSelect').value;
        const label = document.getElementById('rewardValueLabel');
        if (type === 'percentage') {
            label.innerHTML = 'Bonus Percentage (%) <span style="color:var(--red)">*</span>';
        } else if (type === 'fixed_amount') {
            label.innerHTML = 'Fixed Reward Amount ($) <span style="color:var(--red)">*</span>';
        } else {
            label.innerHTML = 'Points / Pips Amount <span style="color:var(--red)">*</span>';
        }
    }
</script>
@endsection
