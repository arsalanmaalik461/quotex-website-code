@extends('admin.layouts.app')
@section('title', 'Edit Payment Method: ' . $paymentMethod->name)
@section('page-title', '✏️ Edit Payment Method')

@section('content')

<div style="margin-bottom: 16px;">
    <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-ghost btn-sm">← Back to Payment Methods</a>
</div>

<div class="card" style="max-width: 760px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title">Edit Payment Method: {{ $paymentMethod->name }}</div>
    </div>

    <form method="POST" action="{{ route('admin.payment-methods.update', $paymentMethod) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label">Method Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $paymentMethod->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Type *</label>
                <select name="type" class="form-control" required>
                    <option value="both" {{ old('type', $paymentMethod->type) === 'both' ? 'selected' : '' }}>Deposit & Withdrawal</option>
                    <option value="deposit" {{ old('type', $paymentMethod->type) === 'deposit' ? 'selected' : '' }}>Deposit Only</option>
                    <option value="withdrawal" {{ old('type', $paymentMethod->type) === 'withdrawal' ? 'selected' : '' }}>Withdrawal Only</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label">Icon / Emoji</label>
                <input type="text" name="icon" class="form-control" value="{{ old('icon', $paymentMethod->icon) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Currency Symbol / Code *</label>
                <input type="text" name="currency" class="form-control" value="{{ old('currency', $paymentMethod->currency) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Conversion Rate (1 USD = ?) *</label>
                <input type="number" step="0.0001" name="conversion_rate" class="form-control" value="{{ old('conversion_rate', $paymentMethod->conversion_rate) }}" required>
                <span style="font-size: 11px; color: var(--text-muted);">Amount in local currency per 1 USD</span>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Logo Upload</label>
            @if($paymentMethod->logo_url)
                <div style="margin-bottom:8px;display:flex;align-items:center;gap:10px;">
                    <img src="{{ $paymentMethod->logo_url }}" alt="{{ $paymentMethod->name }}" style="width:36px;height:36px;border-radius:6px;object-fit:contain;background:#fff;">
                    <label style="font-size:12px;color:var(--text-muted);">
                        <input type="checkbox" name="remove_logo" value="1">
                        Remove current logo
                    </label>
                </div>
            @endif
            <input type="file" name="logo" class="form-control" accept="image/*">
            <span style="font-size: 11px; color: var(--text-muted);">Leave empty to keep the current logo.</span>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Receiving Wallet Address / Account Number</label>
            <input type="text" name="wallet_address" class="form-control" value="{{ old('wallet_address', $paymentMethod->wallet_address) }}">
            <span style="font-size: 11px; color: var(--text-muted);">This is the address/number shown to customers for depositing, with a Copy button</span>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Payment Instructions / Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $paymentMethod->description) }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div class="form-group">
                <label class="form-label">Min Amount (USD) *</label>
                <input type="number" step="1" name="min_amount" class="form-control" value="{{ old('min_amount', $paymentMethod->min_amount) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Max Amount (USD) *</label>
                <input type="number" step="1" name="max_amount" class="form-control" value="{{ old('max_amount', $paymentMethod->max_amount) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $paymentMethod->sort_order) }}">
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <label class="flex items-center gap-2" style="cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $paymentMethod->is_active) ? 'checked' : '' }}>
                <span style="font-size: 13px; font-weight: 600; color: #fff;">Active (Visible to customers)</span>
            </label>
        </div>

        @if($categories->isNotEmpty())
            <div class="card" style="background:var(--bg-hover);margin-bottom:24px;">
                <div class="card-header">
                    <div>
                        <div class="card-title">Deposit Categories</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Assign this method to one or more categories.</div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;">
                    @foreach($categories as $category)
                        @php
                            $checked = old('category_ids')
                                ? in_array($category->id, old('category_ids', []))
                                : $paymentMethod->categories->contains($category->id);
                        @endphp
                        <label style="display:flex;gap:8px;align-items:center;color:#fff;font-size:13px;">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" {{ $checked ? 'checked' : '' }}>
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

@endsection
