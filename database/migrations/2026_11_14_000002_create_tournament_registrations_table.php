<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tournament_registrations')) {
            Schema::create('tournament_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                // Entry fee actually charged at registration (0 for free tournaments).
                $table->decimal('entry_fee_paid', 10, 2)->default(0);
                // Starting balance credited to the special tournament account ($100 per tournament rules).
                $table->decimal('tournament_balance', 12, 2)->default(100.00);
                // registered = entry accepted; add more states (e.g. disqualified) later if needed.
                $table->string('status')->default('registered');
                $table->timestamps();

                $table->unique(['tournament_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_registrations');
    }
};
