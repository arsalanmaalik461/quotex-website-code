<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_methods')) {
            return;
        }

        Schema::table('payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_methods', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('icon');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payment_methods')) {
            return;
        }

        Schema::table('payment_methods', function (Blueprint $table) {
            if (Schema::hasColumn('payment_methods', 'logo_path')) {
                $table->dropColumn('logo_path');
            }
        });
    }
};
