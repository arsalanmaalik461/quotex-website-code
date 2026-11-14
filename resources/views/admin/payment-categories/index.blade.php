@extends('admin.layouts.app')
@section('title', 'Payment Categories')
@section('page-title', 'Payment Categories')

@section('content')

<div style="margin-bottom:16px;display:flex;gap:10px;justify-content:space-between;align-items:center;flex-wrap:wrap;">
    <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-ghost btn-sm">Back to Payment Methods</a>
    <a href="{{ route('admin.payment-categories.create') }}" class="btn btn-success">+ Add Category</a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Deposit Payment Categories</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Categories organize existing payment methods on the deposit page.</div>
        </div>
    </div>

    @if($categories->isEmpty())
        <div style="padding:48px;text-align:center;color:var(--text-muted);">No categories created yet.</div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Slug</th>
                        <th>Methods</th>
                        <th>Deposit</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td>
                                <strong style="color:#fff;">{{ $category->icon ?: 'card' }} {{ $category->name }}</strong><br>
                                <span style="font-size:11px;color:var(--text-muted);">Sort: #{{ $category->sort_order }}</span>
                            </td>
                            <td><code style="font-size:12px;">{{ $category->slug }}</code></td>
                            <td>{{ $category->payment_methods_count }}</td>
                            <td>{{ $category->show_on_deposit ? 'Shown' : 'Hidden' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.payment-categories.toggle', $category) }}" style="display:inline;">
                                    @csrf
                                    <button class="btn btn-sm {{ $category->is_active ? 'btn-success' : 'btn-ghost' }}" style="padding:2px 10px;font-size:11px;">
                                        {{ $category->is_active ? 'Enabled' : 'Disabled' }}
                                    </button>
                                </form>
                            </td>
                            <td style="text-align:right;">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.payment-categories.edit', $category) }}" class="btn btn-ghost btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.payment-categories.destroy', $category) }}" onsubmit="return confirm('Delete category {{ $category->name }}? Payment methods will stay safe.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">{{ $categories->links() }}</div>
    @endif
</div>

@endsection
