<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\AppSetting;
use App\Services\AppDownloadService;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminEmailSettingsController extends Controller
{
    public function edit()
    {
        $settings = [];
        foreach (array_merge(EmailTemplateService::defaults(), AppDownloadService::defaults()) as $key => $default) {
            $settings[$key] = AppSetting::get($key, $default);
        }

        return view('admin.email-settings.edit', [
            'settings' => $settings,
            'placeholders' => EmailTemplateService::placeholders(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'from_address' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'site_name' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'logo_url' => 'nullable|string|max:500',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'header_color' => 'required|string|max:20',
            'background_color' => 'required|string|max:20',
            'text_color' => 'required|string|max:20',
            'button_color' => 'required|string|max:20',
            'button_text_color' => 'required|string|max:20',
            'withdrawal_approved_enabled' => 'nullable|boolean',
            'withdrawal_approved_subject' => 'required|string|max:255',
            'withdrawal_approved_header' => 'required|string|max:255',
            'withdrawal_approved_body' => 'required|string',
            'withdrawal_approved_button_text' => 'required|string|max:255',
            'withdrawal_approved_button_url' => 'required|string|max:500',
            'withdrawal_approved_footer' => 'required|string',
            'withdrawal_rejected_subject' => 'required|string|max:255',
            'withdrawal_rejected_header' => 'required|string|max:255',
            'withdrawal_rejected_default_reason' => 'required|string|max:1000',
            'withdrawal_rejected_body' => 'required|string',
            'withdrawal_rejected_footer' => 'required|string',
            'app_download_popup_enabled' => 'nullable|boolean',
            'app_download_popup_title' => 'required|string|max:120',
            'app_download_popup_description' => 'required|string|max:255',
            'app_download_button_text' => 'required|string|max:80',
            'app_download_apk_path' => ['required', 'string', 'max:255', 'regex:/^(?!.*\.\.)(?!\/)(?!.*\\\\).+\.apk$/i'],
            'app_download_popup_logo_url' => 'nullable|string|max:500',
        ]);

        $logoUrl = $validated['logo_url'] ?? '';
        if ($request->hasFile('logo')) {
            $dir = public_path('uploads/email');
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $file = $request->file('logo');
            $name = 'email_logo_' . Str::random(12) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $name);
            $logoUrl = 'uploads/email/' . $name;
        }

        $map = [
            'email.smtp_host' => $validated['smtp_host'] ?? '',
            'email.smtp_port' => (string) ($validated['smtp_port'] ?? ''),
            'email.smtp_username' => $validated['smtp_username'] ?? '',
            'email.smtp_encryption' => $validated['smtp_encryption'] ?? '',
            'email.from_address' => $validated['from_address'],
            'email.from_name' => $validated['from_name'],
            'email.site_name' => $validated['site_name'],
            'email.support_email' => $validated['support_email'],
            'email.logo_url' => $logoUrl,
            'email.primary_color' => $validated['primary_color'],
            'email.secondary_color' => $validated['secondary_color'],
            'email.header_color' => $validated['header_color'],
            'email.background_color' => $validated['background_color'],
            'email.text_color' => $validated['text_color'],
            'email.button_color' => $validated['button_color'],
            'email.button_text_color' => $validated['button_text_color'],
            'email.withdrawal_approved_enabled' => $request->boolean('withdrawal_approved_enabled') ? '1' : '0',
            'email.withdrawal_approved_subject' => $validated['withdrawal_approved_subject'],
            'email.withdrawal_approved_header' => $validated['withdrawal_approved_header'],
            'email.withdrawal_approved_body' => $validated['withdrawal_approved_body'],
            'email.withdrawal_approved_button_text' => $validated['withdrawal_approved_button_text'],
            'email.withdrawal_approved_button_url' => $validated['withdrawal_approved_button_url'],
            'email.withdrawal_approved_footer' => $validated['withdrawal_approved_footer'],
            'email.withdrawal_rejected_subject' => $validated['withdrawal_rejected_subject'],
            'email.withdrawal_rejected_header' => $validated['withdrawal_rejected_header'],
            'email.withdrawal_rejected_default_reason' => $validated['withdrawal_rejected_default_reason'],
            'email.withdrawal_rejected_body' => $validated['withdrawal_rejected_body'],
            'email.withdrawal_rejected_footer' => $validated['withdrawal_rejected_footer'],
            'app_download.popup_enabled' => $request->boolean('app_download_popup_enabled') ? '1' : '0',
            'app_download.popup_title' => $validated['app_download_popup_title'],
            'app_download.popup_description' => $validated['app_download_popup_description'],
            'app_download.button_text' => $validated['app_download_button_text'],
            'app_download.apk_path' => ltrim(str_replace('\\', '/', $validated['app_download_apk_path']), '/'),
            'app_download.popup_logo_url' => $validated['app_download_popup_logo_url'] ?? '',
        ];

        if ($request->filled('smtp_password')) {
            $map['email.smtp_password'] = $validated['smtp_password'];
        }

        foreach ($map as $key => $value) {
            AppSetting::put($key, $value);
        }

        AdminLog::record('email_settings_update', 'Updated email SMTP, withdrawal email template, and app download settings.');

        return back()->with('success', 'Email settings saved.');
    }
}
