<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminPromoCodeController extends Controller
{
    private function ensureTableExists(): void
    {
        if (!Schema::hasTable('promo_codes')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('promo_codes') && PromoCode::count() === 0) {
            // Seed initial Quotex promo codes
            PromoCode::create([
                'code'         => 'DEPOSIT50',
                'category'     => 'deposit_bonus',
                'reward_type'  => 'percentage',
                'reward_value' => 50.00,
                'min_deposit'  => 50.00,
                'expires_at'   => '2030-10-29 23:59:59',
                'is_unlimited' => false,
                'is_active'    => true,
                'sort_order'   => 1,
                'description'  => 'Receive a 50% extra bonus on your deposit.',
            ]);

            PromoCode::create([
                'code'         => 'WELCOME50',
                'category'     => 'deposit_bonus',
                'reward_type'  => 'percentage',
                'reward_value' => 50.00,
                'min_deposit'  => 10.00,
                'expires_at'   => null,
                'is_unlimited' => true,
                'is_active'    => true,
                'sort_order'   => 2,
                'description'  => 'Welcome 50% deposit bonus for all traders.',
            ]);

            PromoCode::create([
                'code'         => 'DEPOSIT40',
                'category'     => 'deposit_bonus',
                'reward_type'  => 'percentage',
                'reward_value' => 40.00,
                'min_deposit'  => 30.00,
                'expires_at'   => '2030-10-29 23:59:59',
                'is_unlimited' => false,
                'is_active'    => true,
                'sort_order'   => 3,
                'description'  => 'Receive a 40% extra bonus on your deposit.',
            ]);
        }
    }

    public function index(Request $request)
    {
        $this->ensureTableExists();

        $query = PromoCode::query();

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where('code', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
        }

        $promoCodes = $query->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'       => PromoCode::count(),
            'active'      => PromoCode::where('is_active', true)->count(),
            'deposit'     => PromoCode::where('category', 'deposit_bonus')->count(),
            'risk_free'   => PromoCode::where('category', 'risk_free')->count(),
            'cashback'    => PromoCode::where('category', 'cashback')->count(),
            'turnover'    => PromoCode::where('category', 'turnover_percent')->count(),
            'balance'     => PromoCode::where('category', 'balance_bonus')->count(),
            'points'      => PromoCode::where('category', 'cancel_points')->count(),
        ];

        $categories = PromoCode::categories();

        return view('admin.promo_codes.index', compact('promoCodes', 'stats', 'categories'));
    }

    public function create()
    {
        $this->ensureTableExists();
        $categories = PromoCode::categories();
        return view('admin.promo_codes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->ensureTableExists();

        $request->merge([
            'code' => strtoupper(trim($request->code)),
        ]);

        $validated = $request->validate([
            'code'         => 'required|string|max:50|unique:promo_codes,code',
            'category'     => 'required|in:risk_free,cashback,deposit_bonus,turnover_percent,balance_bonus,cancel_points',
            'reward_type'  => 'required|in:percentage,fixed_amount,points',
            'reward_value' => 'required|numeric|min:0.01',
            'min_deposit'  => 'nullable|numeric|min:0',
            'expires_at'   => 'nullable|date',
            'is_unlimited' => 'nullable|boolean',
            'is_active'    => 'nullable|boolean',
            'description'  => 'nullable|string|max:1000',
            'sort_order'   => 'nullable|integer',
        ]);

        $validated['is_unlimited'] = $request->has('is_unlimited');
        $validated['is_active']    = $request->has('is_active');
        $validated['sort_order']   = $request->sort_order ?? 0;

        if ($validated['is_unlimited']) {
            $validated['expires_at'] = null;
        }

        $promo = PromoCode::create($validated);

        if (class_exists(AdminLog::class)) {
            AdminLog::create([
                'admin_id' => auth()->id() ?? 1,
                'action'   => 'create_promo_code',
                'target'   => "Promo Code: {$promo->code}",
                'details'  => "Created promo code {$promo->code} for category {$promo->category} with value {$promo->reward_value}",
            ]);
        }

        return redirect()->route('admin.promo-codes.index')->with('success', "Promo code '{$promo->code}' created successfully!");
    }

    public function edit(PromoCode $promoCode)
    {
        $this->ensureTableExists();
        $categories = PromoCode::categories();
        return view('admin.promo_codes.edit', compact('promoCode', 'categories'));
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $this->ensureTableExists();

        $request->merge([
            'code' => strtoupper(trim($request->code)),
        ]);

        $validated = $request->validate([
            'code'         => 'required|string|max:50|unique:promo_codes,code,' . $promoCode->id,
            'category'     => 'required|in:risk_free,cashback,deposit_bonus,turnover_percent,balance_bonus,cancel_points',
            'reward_type'  => 'required|in:percentage,fixed_amount,points',
            'reward_value' => 'required|numeric|min:0.01',
            'min_deposit'  => 'nullable|numeric|min:0',
            'expires_at'   => 'nullable|date',
            'is_unlimited' => 'nullable|boolean',
            'is_active'    => 'nullable|boolean',
            'description'  => 'nullable|string|max:1000',
            'sort_order'   => 'nullable|integer',
        ]);

        $validated['is_unlimited'] = $request->has('is_unlimited');
        $validated['is_active']    = $request->has('is_active');
        $validated['sort_order']   = $request->sort_order ?? 0;

        if ($validated['is_unlimited']) {
            $validated['expires_at'] = null;
        }

        $promoCode->update($validated);

        if (class_exists(AdminLog::class)) {
            AdminLog::create([
                'admin_id' => auth()->id() ?? 1,
                'action'   => 'update_promo_code',
                'target'   => "Promo Code: {$promoCode->code}",
                'details'  => "Updated promo code {$promoCode->code}",
            ]);
        }

        return redirect()->route('admin.promo-codes.index')->with('success', "Promo code '{$promoCode->code}' updated successfully!");
    }

    public function toggle(PromoCode $promoCode)
    {
        $this->ensureTableExists();
        $promoCode->update(['is_active' => !$promoCode->is_active]);

        $status = $promoCode->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Promo code '{$promoCode->code}' has been {$status}.");
    }

    public function destroy(PromoCode $promoCode)
    {
        $this->ensureTableExists();
        $code = $promoCode->code;
        $promoCode->delete();

        if (class_exists(AdminLog::class)) {
            AdminLog::create([
                'admin_id' => auth()->id() ?? 1,
                'action'   => 'delete_promo_code',
                'target'   => "Promo Code: {$code}",
                'details'  => "Deleted promo code {$code}",
            ]);
        }

        return redirect()->route('admin.promo-codes.index')->with('success', "Promo code '{$code}' deleted successfully!");
    }
}
