<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\Transaction;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Handles user registration for tournaments.
 *
 * Before this controller existed, registering was pure front-end theatre:
 * the detail page only swapped the modal text to "Registration request
 * submitted..." and never sent anything to the server, so no registration
 * was ever stored and the admin panel had nothing to list.
 */
class TournamentRegistrationController extends Controller
{
    public function register(Request $request, Tournament $tournament)
    {
        $user = Auth::user();

        if (!$tournament->is_active || $tournament->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This tournament is not accepting registrations.',
            ], 422);
        }

        $entryFee = (float) ($tournament->entry_fee ?? 0);

        $existing = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You are already registered for ' . $tournament->title . '.',
            ], 409);
        }

        if (Money::greaterThan($entryFee, $user->live_balance ?? 0)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient live balance. Entry fee is $' . number_format($entryFee, 2)
                    . ' but you have $' . number_format((float) ($user->live_balance ?? 0), 2) . ' available.',
            ], 422);
        }

        $registration = DB::transaction(function () use ($user, $tournament, $entryFee) {
            // Lock the user row so two parallel registrations cannot double-charge.
            $lockedUser = \App\Models\User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if (Money::greaterThan($entryFee, $lockedUser->live_balance ?? 0)) {
                return null;
            }

            if ($entryFee > 0) {
                $lockedUser->live_balance = Money::subtract($lockedUser->live_balance, $entryFee);
                $lockedUser->save();

                Transaction::create([
                    'user_id'      => $lockedUser->id,
                    'account_type' => 'live',
                    'type'         => 'tournament_entry',
                    'amount'       => $entryFee,
                    'method'       => 'tournament',
                    'status'       => 'approved',
                    'processed_at' => now(),
                    'notes'        => 'Entry fee for tournament: ' . $tournament->title,
                ]);
            }

            return TournamentRegistration::create([
                'tournament_id'      => $tournament->id,
                'user_id'            => $lockedUser->id,
                'entry_fee_paid'     => $entryFee,
                'tournament_balance' => 100.00,
                'status'             => 'registered',
            ]);
        });

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient live balance. Entry fee is $' . number_format($entryFee, 2) . '.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration request submitted for ' . $tournament->title . '! Good luck with your trading!',
        ]);
    }
}
