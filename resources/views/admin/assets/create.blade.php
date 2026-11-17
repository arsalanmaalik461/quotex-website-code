@extends('admin.layouts.app')
@section('title', $asset ? 'Edit ' . $asset->symbol : 'Add Trading Pair')
@section('page-title', $asset ? '✏️ Edit ' . $asset->symbol : '➕ Add Trading Pair')

@section('content')

<div style="margin-bottom:16px">
    <a href="{{ route('admin.assets.index') }}" class="btn btn-ghost btn-sm">← Back to Assets</a>
</div>

<div class="card" style="max-width:700px">
    <form method="POST" action="{{ $asset ? route('admin.assets.update', $asset) : route('admin.assets.store') }}">
        @csrf
        @if($asset) @method('PUT') @endif

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Symbol *</label>
                <input type="text" name="symbol" class="form-control" value="{{ old('symbol', $asset?->symbol) }}"
                       placeholder="e.g. AZA/USDT" required {{ $asset ? 'readonly' : '' }}>
                @error('symbol')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $asset?->name) }}"
                       placeholder="e.g. Aza Coin / USDT" required>
            </div>
            <div class="form-group">
                <label class="form-label">Type *</label>
                <select name="type" class="form-control" required>
                    @foreach(['forex','crypto','commodity','index','otc','signal'] as $t)
                    <option value="{{ $t }}" {{ old('type', $asset?->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Category (optional)</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $asset?->category) }}" placeholder="e.g. Majors, DeFi, Metals">
            </div>
            <div class="form-group">
                <label class="form-label">Payout % (e.g. 90) *</label>
                <input type="number" name="payout_percent" class="form-control" value="{{ old('payout_percent', $asset?->payout_percent ?? 90) }}" min="1" max="99" required>
            </div>
            <div class="form-group">
                <label class="form-label">Starting / Base Market Price (optional)</label>
                <input type="number" step="0.00001" name="base_price_override" class="form-control" value="{{ old('base_price_override', $asset?->base_price_override) }}" placeholder="e.g. 1.08500, 64250.00, 152.30">
                <span class="text-muted" style="font-size:11px">Leave blank for auto-detected realistic price.</span>
            </div>
            <div class="form-group">
                <label class="form-label">Flag / Emoji (optional)</label>
                <input type="text" name="flag" class="form-control" value="{{ old('flag', $asset?->flag) }}" placeholder="e.g. 🇺🇸 or 🟡">
            </div>
            <div class="form-group">
                <label class="form-label">Trade Outcome / Control Mode *</label>
                <select name="control_mode" class="form-control" required>
                    <option value="auto" {{ old('control_mode', $asset?->control_mode ?? 'auto') === 'auto' ? 'selected' : '' }}>
                        ⟳ Auto (Market Follow - User Sahi Prediction Kare To Win)
                    </option>
                    <option value="force_win" {{ old('control_mode', $asset?->control_mode) === 'force_win' ? 'selected' : '' }}>
                        🏆 Always Win (Force Win - Har User Win Kare)
                    </option>
                    <option value="force_loss" {{ old('control_mode', $asset?->control_mode) === 'force_loss' ? 'selected' : '' }}>
                        📉 Always Loss (Force Loss - Har User Loss Kare)
                    </option>
                    <option value="pump" {{ old('control_mode', $asset?->control_mode) === 'pump' ? 'selected' : '' }}>
                        🚀 Pump Mode (UP Trades Win, DOWN Trades Lose)
                    </option>
                    <option value="crash" {{ old('control_mode', $asset?->control_mode) === 'crash' ? 'selected' : '' }}>
                        💥 Crash Mode (DOWN Trades Win, UP Trades Lose)
                    </option>
                </select>
                <span class="text-muted" style="font-size:11px">Auto mode mein user market ki real direction ke mutabiq win/loss karega (UP par price barhay to win, kam ho to loss).</span>
            </div>
        </div>

        <div class="flex gap-3 mt-4" style="padding:16px;background:var(--bg-hover);border-radius:8px;">
            <label class="flex items-center gap-2" style="cursor:pointer;font-size:13px">
                <input type="checkbox" name="is_otc" value="1" {{ old('is_otc', $asset?->is_otc) ? 'checked' : '' }}>
                OTC Pair
            </label>
            <label class="flex items-center gap-2" style="cursor:pointer;font-size:13px">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $asset?->is_active ?? true) ? 'checked' : '' }}>
                Active (visible to users)
            </label>
        </div>

        <div class="flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                {{ $asset ? 'Update Asset' : 'Create Asset' }}
            </button>
            @if($asset)
            <a href="{{ route('admin.assets.control', $asset) }}" class="btn btn-warning">⚙ OTC Control</a>
            @endif
        </div>
    </form>
</div>

@endsection
