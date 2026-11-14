<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    public static function defaults(): array
    {
        return [
            'email.smtp_host' => '',
            'email.smtp_port' => '587',
            'email.smtp_username' => '',
            'email.smtp_password' => '',
            'email.smtp_encryption' => 'tls',
            'email.from_address' => config('mail.from.address'),
            'email.from_name' => config('app.name'),
            'email.site_name' => config('app.name'),
            'email.support_email' => config('mail.from.address'),
            'email.logo_url' => '',
            'email.primary_color' => '#026FD3',
            'email.secondary_color' => '#16a34a',
            'email.header_color' => '#0f172a',
            'email.background_color' => '#f4f7fb',
            'email.text_color' => '#1f2937',
            'email.button_color' => '#026FD3',
            'email.button_text_color' => '#ffffff',
            'email.withdrawal_approved_enabled' => '1',
            'email.withdrawal_approved_subject' => 'Withdrawal Approved - {site_name}',
            'email.withdrawal_approved_header' => 'Withdrawal Request Approved',
            'email.withdrawal_approved_body' => "Hello {user_name},\n\nYour withdrawal request has been reviewed and approved.\n\nPlease review the transaction details below. If you have any questions, contact our support team.",
            'email.withdrawal_approved_button_text' => 'View Account',
            'email.withdrawal_approved_button_url' => '{account_url}',
            'email.withdrawal_approved_footer' => "{site_name}\n{support_email}",
            'email.withdrawal_rejected_subject' => 'Withdrawal Request Update - {site_name}',
            'email.withdrawal_rejected_header' => 'Withdrawal Request Rejected',
            'email.withdrawal_rejected_default_reason' => 'Your withdrawal request could not be processed because the submitted withdrawal details could not be verified. Please review your withdrawal information and submit a new request.',
            'email.withdrawal_rejected_body' => "Hello {user_name},\n\nWe have reviewed your withdrawal request and unfortunately it could not be processed at this time.\n\nPlease review the transaction details and the reason provided below. If you believe this decision was made in error, contact our support team.",
            'email.withdrawal_rejected_footer' => "{site_name}\n{support_email}",
        ];
    }

    public static function get(string $key): string
    {
        return (string) AppSetting::get($key, self::defaults()[$key] ?? '');
    }

    public static function sendWithdrawalRejected(Transaction $withdrawal, string $reason): void
    {
        $withdrawal->loadMissing('user');
        $user = $withdrawal->user;
        if (!$user || empty($user->email)) {
            throw new \RuntimeException('User email address is missing.');
        }

        self::configureMailer();

        $variables = self::withdrawalVariables($withdrawal, $reason);
        $subject = self::render(self::get('email.withdrawal_rejected_subject'), $variables);
        $header = self::render(self::get('email.withdrawal_rejected_header'), $variables);
        $body = self::render(self::get('email.withdrawal_rejected_body'), $variables);
        $footer = self::render(self::get('email.withdrawal_rejected_footer'), $variables);
        $html = self::html($header, $body, $footer, $variables, 'rejected', $reason);

        Mail::html($html, function ($message) use ($user, $subject) {
            $message->to($user->email, $user->name)->subject($subject);
        });
    }

    public static function approvalEmailsEnabled(): bool
    {
        return self::get('email.withdrawal_approved_enabled') === '1';
    }

    public static function sendWithdrawalApproved(Transaction $withdrawal): void
    {
        $withdrawal->loadMissing('user');
        $user = $withdrawal->user;
        if (!$user || empty($user->email)) {
            throw new \RuntimeException('User email address is missing.');
        }

        self::configureMailer();

        $variables = self::withdrawalVariables($withdrawal);
        $subject = self::render(self::get('email.withdrawal_approved_subject'), $variables);
        $header = self::render(self::get('email.withdrawal_approved_header'), $variables);
        $body = self::render(self::get('email.withdrawal_approved_body'), $variables);
        $buttonText = self::render(self::get('email.withdrawal_approved_button_text'), $variables);
        $buttonUrl = self::render(self::get('email.withdrawal_approved_button_url'), $variables);
        $footer = self::render(self::get('email.withdrawal_approved_footer'), $variables);
        $html = self::html($header, $body, $footer, $variables, 'approved', '', $buttonText, $buttonUrl);

        Mail::html($html, function ($message) use ($user, $subject) {
            $message->to($user->email, $user->name)->subject($subject);
        });
    }

    public static function render(string $template, array $variables): string
    {
        return strtr($template, $variables);
    }

    public static function placeholders(): array
    {
        return [
            '{user_name}',
            '{user_email}',
            '{site_name}',
            '{site_logo}',
            '{withdrawal_id}',
            '{withdrawal_amount}',
            '{withdrawal_method}',
            '{withdrawal_status}',
            '{withdrawal_date}',
            '{rejection_reason}',
            '{support_email}',
            '{account_url}',
        ];
    }

    private static function configureMailer(): void
    {
        $encryption = self::get('email.smtp_encryption');
        $scheme = match ($encryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => null,
        };

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', self::get('email.smtp_host') ?: config('mail.mailers.smtp.host'));
        Config::set('mail.mailers.smtp.port', (int) (self::get('email.smtp_port') ?: config('mail.mailers.smtp.port')));
        Config::set('mail.mailers.smtp.username', self::get('email.smtp_username') ?: null);
        Config::set('mail.mailers.smtp.password', self::get('email.smtp_password') ?: null);
        Config::set('mail.mailers.smtp.scheme', $scheme);
        Config::set('mail.from.address', self::get('email.from_address'));
        Config::set('mail.from.name', self::get('email.from_name'));
        Mail::purge('smtp');
    }

    private static function withdrawalVariables(Transaction $withdrawal, string $reason = ''): array
    {
        $logo = self::get('email.logo_url');
        if ($logo !== '' && !str_starts_with($logo, 'http://') && !str_starts_with($logo, 'https://')) {
            $logo = asset(ltrim($logo, '/'));
        }

        return [
            '{user_name}' => $withdrawal->user?->name ?? 'Trader',
            '{user_email}' => $withdrawal->user?->email ?? '',
            '{site_name}' => self::get('email.site_name'),
            '{site_logo}' => $logo,
            '{withdrawal_id}' => (string) $withdrawal->id,
            '{withdrawal_amount}' => '$' . number_format((float) $withdrawal->amount, 2),
            '{withdrawal_method}' => (string) $withdrawal->method,
            '{withdrawal_status}' => ucfirst((string) $withdrawal->status),
            '{withdrawal_date}' => $withdrawal->created_at?->format('M d, Y H:i') ?? '',
            '{rejection_reason}' => $reason,
            '{support_email}' => self::get('email.support_email'),
            '{account_url}' => route('withdrawal'),
        ];
    }

    private static function html(
        string $header,
        string $body,
        string $footer,
        array $variables,
        string $state = 'rejected',
        string $reason = '',
        string $buttonText = '',
        string $buttonUrl = ''
    ): string
    {
        $logo = self::get('email.logo_url');
        if ($logo !== '' && !str_starts_with($logo, 'http://') && !str_starts_with($logo, 'https://')) {
            $logo = asset(ltrim($logo, '/'));
        }

        $primary = e(self::get('email.primary_color'));
        $secondary = e(self::get('email.secondary_color'));
        $headerColor = e(self::get('email.header_color'));
        $background = e(self::get('email.background_color'));
        $text = e(self::get('email.text_color'));
        $buttonColor = e(self::get('email.button_color'));
        $buttonTextColor = e(self::get('email.button_text_color'));
        $muted = '#64748b';
        $border = '#e5e7eb';
        $isApproved = $state === 'approved';
        $statusColor = $isApproved ? $secondary : '#b42318';
        $statusBg = $isApproved ? '#ecfdf3' : '#fff1f0';
        $statusBorder = $isApproved ? '#bbf7d0' : '#fecdca';
        $statusLabel = $isApproved ? 'APPROVED' : 'REJECTED';
        $siteName = e(self::get('email.site_name'));

        $detailRow = function (string $label, string $value, bool $badge = false) use ($muted, $statusColor, $statusBg, $statusBorder): string {
            $displayValue = $badge
                ? '<span style="display:inline-block;background:' . $statusBg . ';border:1px solid ' . $statusBorder . ';border-radius:999px;color:' . $statusColor . ';font-size:12px;font-weight:700;letter-spacing:.5px;padding:5px 11px;">' . e(strtoupper($value)) . '</span>'
                : '<span style="color:#0f172a;font-weight:700;">' . e($value) . '</span>';

            return '<tr>'
                . '<td style="padding:12px 0;color:' . $muted . ';font-size:13px;border-bottom:1px solid #f1f5f9;">' . e($label) . '</td>'
                . '<td align="right" style="padding:12px 0;font-size:14px;border-bottom:1px solid #f1f5f9;">' . $displayValue . '</td>'
                . '</tr>';
        };

        return '<!doctype html><html><body style="margin:0;padding:0;background:' . $background . ';font-family:Arial,Helvetica,sans-serif;color:' . $text . ';">'
            . '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:' . $background . ';padding:28px 12px;"><tr><td align="center">'
            . '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:640px;background:#ffffff;border:1px solid ' . $border . ';border-radius:14px;overflow:hidden;box-shadow:0 12px 32px rgba(15,23,42,0.08);">'
            . '<tr><td style="background:#ffffff;padding:28px 32px 18px;text-align:center;border-top:5px solid ' . $primary . ';">'
            . ($logo ? '<img src="' . e($logo) . '" alt="' . $siteName . '" style="max-height:52px;max-width:220px;margin:0 auto 18px;display:block;">' : '<div style="font-size:18px;font-weight:800;color:' . $headerColor . ';margin-bottom:18px;">' . $siteName . '</div>')
            . '<div style="font-size:12px;color:' . $muted . ';font-weight:700;letter-spacing:1.4px;text-transform:uppercase;">Withdrawal Request</div>'
            . '<h1 style="margin:8px 0 0;color:' . $headerColor . ';font-size:28px;line-height:1.2;font-weight:800;">' . e($header) . '</h1>'
            . '<div style="margin:16px auto 0;display:inline-block;background:' . $statusBg . ';border:1px solid ' . $statusBorder . ';border-radius:999px;color:' . $statusColor . ';font-size:12px;font-weight:800;letter-spacing:.8px;padding:7px 14px;">' . $statusLabel . '</div>'
            . '</td></tr>'
            . '<tr><td style="padding:26px 32px 8px;font-size:15px;line-height:1.7;white-space:pre-line;">' . nl2br(e($body)) . '</td></tr>'
            . '<tr><td style="padding:18px 32px 8px;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border:1px solid ' . $border . ';border-radius:12px;padding:6px 18px;background:#fbfdff;">'
            . '<tr><td colspan="2" style="padding:16px 0 8px;color:#0f172a;font-size:16px;font-weight:800;">Withdrawal Details</td></tr>'
            . $detailRow('Amount', $variables['{withdrawal_amount}'] ?? '')
            . $detailRow('Withdrawal Method', $variables['{withdrawal_method}'] ?? '')
            . $detailRow('Request ID', '#' . ($variables['{withdrawal_id}'] ?? ''))
            . $detailRow('Requested At', $variables['{withdrawal_date}'] ?? '')
            . $detailRow('Status', $variables['{withdrawal_status}'] ?? $statusLabel, true)
            . '</table></td></tr>'
            . ($isApproved || $reason === '' ? '' : '<tr><td style="padding:18px 32px 8px;">'
                . '<div style="color:' . $statusColor . ';font-size:15px;font-weight:800;margin-bottom:8px;">Rejection Reason</div>'
                . '<div style="background:' . $statusBg . ';border:1px solid ' . $statusBorder . ';border-left:5px solid ' . $statusColor . ';border-radius:12px;color:#7a271a;font-size:14px;line-height:1.7;padding:16px 18px;white-space:pre-line;">' . nl2br(e($reason)) . '</div>'
                . '</td></tr>')
            . ($buttonText !== '' && $buttonUrl !== '' ? '<tr><td align="center" style="padding:24px 32px 8px;">'
                . '<a href="' . e($buttonUrl) . '" style="display:inline-block;background:' . $buttonColor . ';color:' . $buttonTextColor . ';text-decoration:none;border-radius:10px;padding:13px 22px;font-size:14px;font-weight:800;">' . e($buttonText) . '</a>'
                . '</td></tr>' : '')
            . '<tr><td style="padding:22px 32px 30px;color:' . $muted . ';font-size:13px;line-height:1.7;white-space:pre-line;border-top:1px solid #f1f5f9;">' . nl2br(e($footer)) . '</td></tr>'
            . '</table></td></tr></table></body></html>';
    }
}
