<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('control_anchor_price', 20, 8)->nullable()->after('control_expires_at');
            $table->timestamp('control_activated_at')->nullable()->after('control_anchor_price');
            $table->unsignedInteger('control_version')->default(0)->after('control_activated_at');
        });

        Schema::create('asset_market_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 20);
            $table->decimal('anchor_price', 20, 8);
            $table->timestamp('activated_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->index(['asset_id', 'activated_at']);
            $table->index(['asset_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_market_states');

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'control_anchor_price',
                'control_activated_at',
                'control_version',
            ]);
        });
    }
};
