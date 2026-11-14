<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('signals')) {
            Schema::create('signals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('asset_id')->nullable()->constrained('assets')->onDelete('cascade');
                $table->string('pair_name');
                $table->enum('direction', ['call', 'put']);
                $table->string('duration')->default('15:00'); // Display string e.g. 15:00, 10:00, 04:00:00
                $table->integer('duration_seconds')->default(900); // 900s = 15m
                $table->timestamp('start_time')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->decimal('payout_percent', 5, 2)->default(85.00);
                $table->boolean('is_active')->default(true);
                $table->enum('status', ['active', 'expired', 'win', 'loss'])->default('active');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('signals');
    }
};
