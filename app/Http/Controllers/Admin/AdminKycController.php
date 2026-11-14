<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\KycVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminKycController extends Controller
{
    private function ensureTableExists(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('kyc_verifications')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
            }
        }
    }

    public function index(Request $request)
    {
        $this->ensureTableExists();

        if (!\Illuminate\Support\Facades\Schema::hasTable('kyc_verifications')) {
            $verifications = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
            return view('admin.kyc.index', compact('verifications', 'stats'));
        }

        $query = KycVerification::with(['user', 'reviewer']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('email', 'like', "%$s%")
                         ->orWhere('name', 'like', "%$s%");
                  });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $verifications = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'    => KycVerification::count(),
            'pending'  => KycVerification::where('status', 'pending')->count(),
            'approved' => KycVerification::where('status', 'approved')->count(),
            'rejected' => KycVerification::where('status', 'rejected')->count(),
        ];

        return view('admin.kyc.index', compact('verifications', 'stats'));
    }

    public function show(KycVerification $kyc)
    {
        $kyc->load(['user', 'reviewer']);
        return view('admin.kyc.show', compact('kyc'));
    }

    public function approve(Request $request, KycVerification $kyc)
    {
        $kyc->update([
            'status' => 'approved',
            'rejection_reason' => null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($kyc->user) {
            $kyc->user->update([
                'kyc_status' => 'verified',
            ]);
        }

        AdminLog::create([
            'admin_id' => Auth::id() ?? 1,
            'action' => 'kyc_approved',
            'target_type' => 'KycVerification',
            'target_id' => $kyc->id,
            'description' => "Approved KYC verification #{$kyc->id} for user {$kyc->full_name} (User #{$kyc->user_id})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', "KYC Verification for {$kyc->full_name} has been approved successfully.");
    }

    public function reject(Request $request, KycVerification $kyc)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:4|max:1000',
        ], [
            'rejection_reason.required' => 'Please provide a reason explaining why this KYC verification is being rejected.',
        ]);

        $kyc->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($kyc->user) {
            $kyc->user->update([
                'kyc_status' => 'rejected',
            ]);
        }

        AdminLog::create([
            'admin_id' => Auth::id() ?? 1,
            'action' => 'kyc_rejected',
            'target_type' => 'KycVerification',
            'target_id' => $kyc->id,
            'description' => "Rejected KYC verification #{$kyc->id} for user {$kyc->full_name}. Reason: {$request->rejection_reason}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', "KYC Verification for {$kyc->full_name} has been rejected with reason.");
    }
}
