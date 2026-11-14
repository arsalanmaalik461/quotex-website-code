@extends('admin.layouts.app')
@section('title', 'Payment Methods')
@section('page-title', '💳 Deposit & Withdrawal Methods')

@section('content')

@if($lgPayMethods->isNotEmpty())
<div class="card" style="margin-bottom: 18px;">
    <div class="card-header">
        <div>
            <div class="card-title">Online Auto Payment</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Gateway credentials are loaded from server environment variables.</div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.payment-methods.lgpay.update') }}" enctype="multipart/form-data">
        @csrf

        <div style="margin-bottom: 18px;">
            <label class="flex items-center gap-2" style="cursor:pointer;">
                <input type="checkbox" name="lgpay_enabled" value="1" {{ $lgPayEnabled ? 'checked' : '' }}>
                <span style="font-size:13px;font-weight:600;color:#fff;">Master Status: Enabled</span>
            </label>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
            @foreach($lgPayMethods as $lg)
                <div style="border:1px solid var(--border);border-radius:8px;padding:16px;background:var(--bg-hover);">
                    <div class="flex items-center justify-between" style="margin-bottom:12px;">
                        <strong style="color:#fff;">{{ $lg->provider_code === 'easypaisa' ? 'EasyPaisa' : 'JazzCash' }}</strong>
                        <label class="flex items-center gap-2" style="cursor:pointer;font-size:12px;color:var(--text-muted);">
                            <input type="checkbox" name="methods[{{ $lg->id }}][is_active]" value="1" {{ $lg->is_active ? 'checked' : '' }}>
                            Enabled
                        </label>
                    </div>

                    <div class="form-group" style="margin-bottom:10px;">
                        <label class="form-label">Display Name</label>
                        <input class="form-control" name="methods[{{ $lg->id }}][name]" value="{{ old("methods.{$lg->id}.name", $lg->name) }}" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                        <div class="form-group">
                            <label class="form-label">Logo / Key</label>
                            <input class="form-control" name="methods[{{ $lg->id }}][icon]" value="{{ old("methods.{$lg->id}.icon", $lg->icon) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Trade Type</label>
                            <input class="form-control" name="methods[{{ $lg->id }}][trade_type]" value="{{ old("methods.{$lg->id}.trade_type", $lg->trade_type) }}" required>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                        <div class="form-group">
                            <label class="form-label">USD to PKR Rate</label>
                            <input type="number" step="0.0001" class="form-control" name="methods[{{ $lg->id }}][conversion_rate]" value="{{ old("methods.{$lg->id}.conversion_rate", $lg->conversion_rate) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gateway Currency</label>
                            <input class="form-control" value="PKR" disabled>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:10px;">
                        <label class="form-label">Logo Image</label>
                        @if($lg->logo_url)
                            <div class="flex items-center gap-2" style="margin-bottom:8px;">
                                <img src="{{ $lg->logo_url }}" alt="{{ $lg->name }}" style="width:42px;height:42px;border-radius:6px;object-fit:contain;background:#fff;padding:3px;">
                                <label class="flex items-center gap-2" style="font-size:12px;color:var(--text-muted);cursor:pointer;">
                                    <input type="checkbox" name="methods[{{ $lg->id }}][remove_logo]" value="1">
                                    Remove current logo
                                </label>
                            </div>
                        @endif
                        <input type="file" class="form-control" name="methods[{{ $lg->id }}][logo]" accept="image/jpeg,image/png,image/webp">
                        <span style="font-size:11px;color:var(--text-muted);">JPG, PNG, or WebP. Leave empty to keep current logo.</span>
                    </div>

                    @if($categories->isNotEmpty())
                        <div class="form-group" style="margin-bottom:10px;">
                            <label class="form-label">Deposit Categories</label>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:6px;">
                                @foreach($categories as $category)
                                    <label style="font-size:12px;color:var(--text-muted);display:flex;gap:6px;align-items:center;">
                                        <input type="checkbox" name="methods[{{ $lg->id }}][category_ids][]" value="{{ $category->id }}" {{ $lg->categories->contains($category->id) ? 'checked' : '' }}>
                                        {{ $category->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                        <div class="form-group">
                            <label class="form-label">Minimum Deposit</label>
                            <input type="number" step="1" class="form-control" name="methods[{{ $lg->id }}][min_amount]" value="{{ old("methods.{$lg->id}.min_amount", $lg->min_amount) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Maximum Deposit</label>
                            <input type="number" step="1" class="form-control" name="methods[{{ $lg->id }}][max_amount]" value="{{ old("methods.{$lg->id}.max_amount", $lg->max_amount) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="methods[{{ $lg->id }}][sort_order]" value="{{ old("methods.{$lg->id}.sort_order", $lg->sort_order) }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end" style="margin-top:16px;">
            <button type="submit" class="btn btn-primary">Save Online Payment Settings</button>
        </div>
    </form>
</div>
@endif

<div class="card">
    <div class="card-header" style="flex-wrap:wrap; gap:16px;">
        <div class="flex items-center gap-3">
            <div class="card-title">All Payment Methods</div>
            <div class="flex gap-2">
                <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-sm {{ !request('type') ? 'btn-primary' : 'btn-ghost' }}">
                    All
                </a>
                <a href="{{ route('admin.payment-methods.index', ['type' => 'deposit']) }}" class="btn btn-sm {{ request('type') === 'deposit' ? 'btn-primary' : 'btn-ghost' }}">
                    Deposit
                </a>
                <a href="{{ route('admin.payment-methods.index', ['type' => 'withdrawal']) }}" class="btn btn-sm {{ request('type') === 'withdrawal' ? 'btn-primary' : 'btn-ghost' }}">
                    Withdrawal
                </a>
            </div>
        </div>

        <div>
            <a href="{{ route('admin.payment-categories.index') }}" class="btn btn-ghost" style="margin-right:8px;">
                Categories
            </a>
            <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-success">
                + Add Payment Method
            </a>
        </div>
    </div>

    @if($methods->isEmpty())
        <div style="text-align: center; padding: 48px; color: var(--text-muted);">
            <div style="font-size: 32px; margin-bottom: 8px;">💳</div>
            <p>No payment methods created yet. Click "+ Add Payment Method" to add your first deposit or withdrawal method.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Icon & Name</th>
                        <th>Type</th>
                        <th>Conversion Rate</th>
                        <th>Wallet / Account Address</th>
                        <th>Limits (USD)</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($methods as $m)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <span style="font-size: 24px;">{{ $m->icon ?? '💳' }}</span>
                                <div>
                                    <strong style="color: #fff;">{{ $m->name }}</strong><br>
                                    <span style="font-size: 11px; color: var(--text-muted);">Sort: #{{ $m->sort_order }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($m->type === 'both')
                                <span class="badge-status blue">Deposit & Withdrawal</span>
                            @elseif($m->type === 'deposit')
                                <span class="badge-status green">Deposit Only</span>
                            @else
                                <span class="badge-status orange">Withdrawal Only</span>
                            @endif
                            @if($m->categories->isNotEmpty())
                                <div style="font-size:11px;color:var(--text-muted);margin-top:5px;">
                                    {{ $m->categories->pluck('name')->join(', ') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong style="color: var(--accent);">1 USD = {{ number_format($m->conversion_rate, 2) }} {{ $m->currency }}</strong>
                        </td>
                        <td>
                            @if($m->wallet_address)
                                <code style="background: var(--bg-hover); padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #fff;">
                                    {{ Str::limit($m->wallet_address, 30) }}
                                </code>
                            @else
                                <span style="color: var(--text-muted); font-size: 12px;">User entered</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 12px;">Min: ${{ number_format($m->min_amount, 2) }}</span><br>
                            <span style="font-size: 11px; color: var(--text-muted);">Max: ${{ number_format($m->max_amount, 2) }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.payment-methods.toggle', $m) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $m->is_active ? 'btn-success' : 'btn-ghost' }}" style="padding: 2px 10px; font-size: 11px;">
                                    {{ $m->is_active ? '✓ Enabled' : '✕ Disabled' }}
                                </button>
                            </form>
                        </td>
                        <td style="text-align: right;">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.payment-methods.edit', $m) }}" class="btn btn-ghost btn-sm">
                                    ✏️ Edit
                                </a>
                                <form method="POST" action="{{ route('admin.payment-methods.destroy', $m) }}" onsubmit="return confirm('Delete payment method {{ $m->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            {{ $methods->links() }}
        </div>
    @endif
</div>

@endsection
