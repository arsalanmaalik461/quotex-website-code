<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\AppSetting;
use App\Models\PaymentCategory;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminPaymentMethodController extends Controller
{
    private function ensureTableExists(): void
    {
        if (!Schema::hasTable('payment_methods')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
            }
        }

        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('payment_methods') && !Schema::hasColumn('payment_methods', 'logo_path')) {
            Schema::table('payment_methods', function ($table) {
                $table->string('logo_path')->nullable()->after('icon');
            });
        }
    }

    private function storeLogoUpload(Request $request, string $field = 'logo'): ?string
    {
        return $this->storeUploadedLogo($request->file($field));
    }

    private function storeUploadedLogo(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        $uploadDir = public_path('uploads/payment-methods');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $filename = 'payment_' . Str::random(16) . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);

        return 'uploads/payment-methods/' . $filename;
    }

    public function index(Request $request)
    {
        $this->ensureTableExists();

        $hasProviderColumn = Schema::hasColumn('payment_methods', 'provider');
        $query = PaymentMethod::query();

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->where('type', $request->type)->orWhere('type', 'both');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($hasProviderColumn) {
            $query->where(function ($q) {
                $q->where('provider', '!=', 'lgpay')->orWhereNull('provider');
            });
        }

        $methods = $query->with('categories')->orderBy('sort_order', 'asc')->paginate(20)->withQueryString();
        $lgPayEnabled = Schema::hasTable('app_settings') && AppSetting::get('lgpay.enabled', '0') === '1';
        $lgPayMethods = $hasProviderColumn
            ? PaymentMethod::where('provider', 'lgpay')->with('categories')->orderBy('sort_order')->get()
            : collect();
        $categories = Schema::hasTable('payment_categories')
            ? PaymentCategory::orderBy('sort_order')->orderBy('name')->get()
            : collect();

        return view('admin.payment-methods.index', compact('methods', 'lgPayEnabled', 'lgPayMethods', 'categories'));
    }

    public function create()
    {
        $this->ensureTableExists();
        $categories = Schema::hasTable('payment_categories')
            ? PaymentCategory::orderBy('sort_order')->orderBy('name')->get()
            : collect();

        return view('admin.payment-methods.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:deposit,withdrawal,both',
            'currency'        => 'required|string|max:20',
            'conversion_rate' => 'required|numeric|min:0.0001',
            'wallet_address'  => 'nullable|string',
            'description'     => 'nullable|string',
            'min_amount'      => 'required|numeric|min:1',
            'max_amount'      => 'required|numeric|gte:min_amount',
            'icon'            => 'nullable|string|max:255',
            'logo'            => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'sort_order'      => 'nullable|integer',
            'trade_type'      => 'nullable|string|max:50',
            'category_ids'    => 'array',
            'category_ids.*'  => 'integer|exists:payment_categories,id',
        ]);

        $data = [
            'name'            => $request->name,
            'type'            => $request->type,
            'currency'        => strtoupper($request->currency),
            'conversion_rate' => $request->conversion_rate,
            'wallet_address'  => $request->wallet_address,
            'description'     => $request->description,
            'min_amount'      => $request->min_amount,
            'max_amount'      => $request->max_amount,
            'icon'            => $request->icon ?? '💳',
            'is_active'       => $request->has('is_active'),
            'sort_order'      => $request->sort_order ?? 0,
        ];

        if (Schema::hasColumn('payment_methods', 'provider')) {
            $data['provider'] = 'manual';
        }
        if (Schema::hasColumn('payment_methods', 'logo_path')) {
            $data['logo_path'] = $this->storeLogoUpload($request);
        }

        $method = PaymentMethod::create($data);
        $this->syncCategories($method, $request->input('category_ids', []));

        AdminLog::record('payment_method_create', "Created payment method: {$method->name} (#{$method->id})");

        return redirect()->route('admin.payment-methods.index')->with('success', "Payment method '{$method->name}' created successfully.");
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        $categories = Schema::hasTable('payment_categories')
            ? PaymentCategory::orderBy('sort_order')->orderBy('name')->get()
            : collect();
        $paymentMethod->load('categories');

        return view('admin.payment-methods.edit', compact('paymentMethod', 'categories'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:deposit,withdrawal,both',
            'currency'        => 'required|string|max:20',
            'conversion_rate' => 'required|numeric|min:0.0001',
            'wallet_address'  => 'nullable|string',
            'description'     => 'nullable|string',
            'min_amount'      => 'required|numeric|min:1',
            'max_amount'      => 'required|numeric|gte:min_amount',
            'icon'            => 'nullable|string|max:255',
            'logo'            => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'remove_logo'     => 'nullable|boolean',
            'sort_order'      => 'nullable|integer',
            'trade_type'      => 'nullable|string|max:50',
            'category_ids'    => 'array',
            'category_ids.*'  => 'integer|exists:payment_categories,id',
        ]);

        $data = [
            'name'            => $request->name,
            'type'            => $request->type,
            'currency'        => strtoupper($request->currency),
            'conversion_rate' => $request->conversion_rate,
            'wallet_address'  => $request->wallet_address,
            'description'     => $request->description,
            'min_amount'      => $request->min_amount,
            'max_amount'      => $request->max_amount,
            'icon'            => $request->icon ?? '💳',
            'is_active'       => $request->has('is_active'),
            'sort_order'      => $request->sort_order ?? 0,
        ];

        if (Schema::hasColumn('payment_methods', 'logo_path')) {
            if ($request->boolean('remove_logo')) {
                $data['logo_path'] = null;
            }

            if ($path = $this->storeLogoUpload($request)) {
                $data['logo_path'] = $path;
            }
        }

        $paymentMethod->update($data);
        $this->syncCategories($paymentMethod, $request->input('category_ids', []));

        AdminLog::record('payment_method_update', "Updated payment method: {$paymentMethod->name} (#{$paymentMethod->id})");

        return redirect()->route('admin.payment-methods.index')->with('success', "Payment method '{$paymentMethod->name}' updated successfully.");
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $name = $paymentMethod->name;
        $paymentMethod->delete();

        AdminLog::record('payment_method_delete', "Deleted payment method: {$name}");

        return redirect()->route('admin.payment-methods.index')->with('success', "Payment method '{$name}' deleted.");
    }

    public function toggle(PaymentMethod $paymentMethod)
    {
        $paymentMethod->is_active = !$paymentMethod->is_active;
        $paymentMethod->save();

        $status = $paymentMethod->is_active ? 'enabled' : 'disabled';
        return redirect()->back()->with('success', "Payment method '{$paymentMethod->name}' has been {$status}.");
    }

    public function updateLGPay(Request $request)
    {
        $this->ensureTableExists();

        if (!Schema::hasColumn('payment_methods', 'provider')) {
            return back()->withErrors([
                'methods' => 'Online payment database columns are missing. Please run the online payment migration first.',
            ]);
        }

        $request->validate([
            'lgpay_enabled' => 'nullable|boolean',
            'methods' => 'array',
            'methods.*.name' => 'required|string|max:255',
            'methods.*.icon' => 'nullable|string|max:255',
            'methods.*.logo' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'methods.*.remove_logo' => 'nullable|boolean',
            'methods.*.conversion_rate' => 'required|numeric|min:0.0001',
            'methods.*.trade_type' => 'required|string|max:50',
            'methods.*.min_amount' => 'required|numeric|min:1',
            'methods.*.max_amount' => 'required|numeric',
            'methods.*.sort_order' => 'nullable|integer',
            'methods.*.is_active' => 'nullable|boolean',
            'methods.*.category_ids' => 'array',
            'methods.*.category_ids.*' => 'integer|exists:payment_categories,id',
        ]);

        AppSetting::put('lgpay.enabled', $request->boolean('lgpay_enabled') ? '1' : '0');

        foreach ($request->input('methods', []) as $id => $data) {
            if ((float) $data['max_amount'] < (float) $data['min_amount']) {
                return back()->withErrors(['methods' => 'Maximum amount must be greater than or equal to minimum amount.'])->withInput();
            }

            $method = PaymentMethod::where('provider', 'lgpay')->findOrFail($id);
            $updates = [
                'name' => $data['name'],
                'icon' => $data['icon'] ?? $method->icon,
                'conversion_rate' => $data['conversion_rate'],
                'trade_type' => $data['trade_type'],
                'min_amount' => $data['min_amount'],
                'max_amount' => $data['max_amount'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => !empty($data['is_active']),
                'type' => 'deposit',
                'currency' => 'PKR',
            ];

            if (Schema::hasColumn('payment_methods', 'logo_path')) {
                if (!empty($data['remove_logo'])) {
                    $updates['logo_path'] = null;
                }

                if ($path = $this->storeUploadedLogo($request->file("methods.{$id}.logo"))) {
                    $updates['logo_path'] = $path;
                }
            }

            $method->update($updates);
            $this->syncCategories($method, $data['category_ids'] ?? []);
        }

        AdminLog::record('lgpay_settings_update', 'Updated online auto payment settings.');

        return redirect()->route('admin.payment-methods.index')->with('success', 'Online payment settings saved.');
    }

    private function syncCategories(PaymentMethod $method, array $categoryIds): void
    {
        if (!Schema::hasTable('payment_category_method')) {
            return;
        }

        $method->categories()->sync(
            collect($categoryIds)
                ->mapWithKeys(fn ($id) => [(int) $id => ['sort_order' => 0]])
                ->all()
        );
    }
}
