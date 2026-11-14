@extends('admin.layouts.app')
@section('title', 'Admin Logs')
@section('page-title', '📝 Admin Activity Logs')

@section('content')
<div class="card mb-4">
    <form method="GET" class="flex gap-3 items-center">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action..." class="form-control" style="max-width:250px">
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request('action')) <a href="{{ route('admin.logs') }}" class="btn btn-ghost">Clear</a> @endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Admin</th><th>Action</th><th>Target</th><th>Description</th><th>IP</th><th>Time</th></tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="color:var(--text-muted)">{{ $log->id }}</td>
                <td>{{ $log->admin?->name ?? 'System' }}</td>
                <td><code style="background:var(--bg-hover);padding:2px 8px;border-radius:4px;font-size:11px;color:var(--accent)">{{ $log->action }}</code></td>
                <td style="font-size:12px;color:var(--text-muted)">{{ $log->target_type }}#{{ $log->target_id }}</td>
                <td style="font-size:12px">{{ Str::limit($log->description, 80) }}</td>
                <td style="font-size:11px;color:var(--text-muted)">{{ $log->ip_address }}</td>
                <td style="font-size:12px">{{ $log->created_at->format('M d, H:i:s') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px">No logs yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $logs->links() }}</div>
</div>
@endsection
