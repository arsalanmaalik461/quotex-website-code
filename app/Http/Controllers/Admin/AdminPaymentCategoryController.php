<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\PaymentCategory;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminPaymentCategoryController extends Controller
{
    public function index()
    {
        $categories = PaymentCategory::withCount('paymentMethods')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.payment-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.payment-categories.create', [
            'category' => new PaymentCategory(['is_active' => true, 'show_on_deposit' => true]),
            'methods' => $this->availablePaymentMethods(),
            'assigned' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCategory($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['show_on_deposit'] = $request->boolean('show_on_deposit');
        $data['image_path'] = $this->storeImage($request->file('image'));

        $category = PaymentCategory::create($data);
        $this->syncMethods($category, $request);

        AdminLog::record('payment_category_create', "Created payment category: {$category->name} (#{$category->id})");

        return redirect()->route('admin.payment-categories.index')->with('success', 'Payment category created.');
    }

    public function edit(PaymentCategory $paymentCategory)
    {
        return view('admin.payment-categories.edit', [
            'category' => $paymentCategory->load('paymentMethods'),
            'methods' => $this->availablePaymentMethods(),
            'assigned' => $paymentCategory->paymentMethods->keyBy('id'),
        ]);
    }

    public function update(Request $request, PaymentCategory $paymentCategory)
    {
        $data = $this->validateCategory($request, $paymentCategory);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $paymentCategory->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['show_on_deposit'] = $request->boolean('show_on_deposit');

        if ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        }

        if ($path = $this->storeImage($request->file('image'))) {
            $data['image_path'] = $path;
        }

        $paymentCategory->update($data);
        $this->syncMethods($paymentCategory, $request);

        AdminLog::record('payment_category_update', "Updated payment category: {$paymentCategory->name} (#{$paymentCategory->id})");

        return redirect()->route('admin.payment-categories.index')->with('success', 'Payment category updated.');
    }

    public function destroy(PaymentCategory $paymentCategory)
    {
        $name = $paymentCategory->name;
        $count = $paymentCategory->paymentMethods()->count();
        $paymentCategory->paymentMethods()->detach();
        $paymentCategory->delete();

        AdminLog::record('payment_category_delete', "Deleted payment category: {$name}; detached {$count} payment methods.");

        return redirect()->route('admin.payment-categories.index')->with('success', "Payment category '{$name}' deleted. Payment methods were not deleted.");
    }

    public function toggle(PaymentCategory $paymentCategory)
    {
        $paymentCategory->update(['is_active' => !$paymentCategory->is_active]);

        return back()->with('success', "Payment category '{$paymentCategory->name}' has been " . ($paymentCategory->is_active ? 'enabled.' : 'disabled.'));
    }

    private function validateCategory(Request $request, ?PaymentCategory $category = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'remove_image' => 'nullable|boolean',
            'short_label' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
            'country' => 'nullable|string|max:80',
            'is_active' => 'nullable|boolean',
            'show_on_deposit' => 'nullable|boolean',
            'methods' => 'array',
            'methods.*' => 'integer|exists:payment_methods,id',
            'method_sort' => 'array',
            'method_sort.*' => 'nullable|integer',
        ]);
    }

    private function syncMethods(PaymentCategory $category, Request $request): void
    {
        $selected = collect($request->input('methods', []))->map(fn ($id) => (int) $id)->unique();
        $sort = $request->input('method_sort', []);

        $payload = $selected->mapWithKeys(function (int $methodId) use ($sort) {
            return [$methodId => ['sort_order' => (int) ($sort[$methodId] ?? 0)]];
        })->all();

        $category->paymentMethods()->sync($payload);
    }

    private function availablePaymentMethods()
    {
        return PaymentMethod::orderBy('sort_order')->orderBy('name')->get();
    }

    private function storeImage(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        $uploadDir = public_path('uploads/payment-categories');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $filename = 'category_' . Str::random(16) . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);

        return 'uploads/payment-categories/' . $filename;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'category';
        $slug = $base;
        $counter = 2;

        while (PaymentCategory::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
