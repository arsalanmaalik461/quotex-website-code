<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->enum('admin_override', ['none', 'force_win', 'force_loss'])
                  ->default('none')
                  ->after('closed_at');
            $table->boolean('spike_injected')->default(false)->after('admin_override');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn(['admin_override', 'spike_injected']);
        });
    }
};
