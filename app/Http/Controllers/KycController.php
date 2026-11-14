<?php

namespace App\Http\Controllers;

use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class KycController extends Controller
{
    private function ensureAuth(): User
    {
        return Auth::user();
    }

    private function ensureTableExists(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('kyc_verifications')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
            }
        }
    }

    public function index()
    {
        $this->ensureTableExists();
        $user = $this->ensureAuth();
        $kyc = \Illuminate\Support\Facades\Schema::hasTable('kyc_verifications') ? $user->kyc : null;

        return view('account.kyc', compact('user', 'kyc'));
    }

    public function submit(Request $request)
    {
        $this->ensureTableExists();
        $user = $this->ensureAuth();
        $existingKyc = $user->kyc;

        // Custom validation
        $rules = [
            'full_name' => 'required|string|min:2|max:255',
            'phone' => 'required|string|min:7|max:30',
        ];

        // If no existing approved/pending KYC, files are strictly required
        $hasFront = $existingKyc && $existingKyc->id_card_front;
        $hasBack = $existingKyc && $existingKyc->id_card_back;
        $hasSelfie = $existingKyc && $existingKyc->live_selfie;

        if (!$hasFront || $request->hasFile('id_card_front')) {
            $rules['id_card_front'] = 'required|image|mimes:jpeg,png,jpg,webp,heic|max:10240';
        }

        if (!$hasBack || $request->hasFile('id_card_back')) {
            $rules['id_card_back'] = 'required|image|mimes:jpeg,png,jpg,webp,heic|max:10240';
        }

        if (!$hasSelfie && !$request->filled('live_selfie_data') && !$request->hasFile('live_selfie')) {
            $rules['live_selfie'] = 'required';
        }

        $request->validate($rules, [
            'full_name.required' => 'Please enter your full name as shown on your ID card.',
            'phone.required' => 'Please provide a valid phone number.',
            'id_card_front.required' => 'Please upload the front side of your ID card.',
            'id_card_front.image' => 'The front of your ID card must be a valid image file.',
            'id_card_back.required' => 'Please upload the back side of your ID card.',
            'id_card_back.image' => 'The back of your ID card must be a valid image file.',
            'live_selfie.required' => 'Please capture a live selfie using your front camera.',
        ]);

        $uploadDir = public_path('uploads/kyc');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        // 1. Process ID Card Front
        $frontPath = $existingKyc ? $existingKyc->id_card_front : null;
        if ($request->hasFile('id_card_front')) {
            $frontFile = $request->file('id_card_front');
            $frontFilename = 'id_front_' . $user->id . '_' . Str::random(10) . '.' . $frontFile->getClientOriginalExtension();
            $frontFile->move($uploadDir, $frontFilename);
            $frontPath = 'uploads/kyc/' . $frontFilename;
        }

        // 2. Process ID Card Back
        $backPath = $existingKyc ? $existingKyc->id_card_back : null;
        if ($request->hasFile('id_card_back')) {
            $backFile = $request->file('id_card_back');
            $backFilename = 'id_back_' . $user->id . '_' . Str::random(10) . '.' . $backFile->getClientOriginalExtension();
            $backFile->move($uploadDir, $backFilename);
            $backPath = 'uploads/kyc/' . $backFilename;
        }

        // 3. Process Live Selfie (from front camera canvas base64 or file upload)
        $selfiePath = $existingKyc ? $existingKyc->live_selfie : null;
        if ($request->filled('live_selfie_data')) {
            $base64Data = $request->input('live_selfie_data');
            // Remove data URI scheme prefix if present
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($type[1]); // jpg, png, etc.
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $ext = 'jpg';
                }
            } else {
                $ext = 'jpg';
            }

            $decoded = base64_decode($base64Data);
            if ($decoded !== false) {
                $selfieFilename = 'selfie_' . $user->id . '_' . Str::random(10) . '.' . $ext;
                file_put_contents($uploadDir . '/' . $selfieFilename, $decoded);
                $selfiePath = 'uploads/kyc/' . $selfieFilename;
            }
        } elseif ($request->hasFile('live_selfie')) {
            $selfieFile = $request->file('live_selfie');
            $selfieFilename = 'selfie_' . $user->id . '_' . Str::random(10) . '.' . $selfieFile->getClientOriginalExtension();
            $selfieFile->move($uploadDir, $selfieFilename);
            $selfiePath = 'uploads/kyc/' . $selfieFilename;
        }

        if (!$frontPath || !$backPath || !$selfiePath) {
            return back()->withInput()->withErrors([
                'live_selfie' => 'Please capture your live selfie and ensure all ID card images are uploaded.',
            ]);
        }

        // Create or update KYC record (one row per user: resubmission after
        // a rejection updates the existing record instead of piling up
        // duplicate rows for the admin to sort through).
        KycVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'id_card_front' => $frontPath,
                'id_card_back' => $backPath,
                'live_selfie' => $selfiePath,
                'status' => 'pending',
                'rejection_reason' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        // Update user fields
        $user->update([
            'name' => $request->full_name,
            'phone' => $request->phone,
            'kyc_status' => 'pending',
        ]);

        return redirect()->route('kyc.index')->with('success', 'Your KYC verification request has been submitted successfully and is pending admin approval.');
    }
}
