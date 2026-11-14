<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_controls', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false)->index();
            $table->string('mode', 50)->default('normal')->index();
            $table->string('scope', 50)->default('all')->index();
            $table->json('asset_ids')->nullable();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('correlation_id', 80)->nullable()->index();
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();
        });

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE assets MODIFY COLUMN control_mode ENUM('auto','pump','crash','force_win','force_loss') NOT NULL DEFAULT 'auto'");
            DB::statement("ALTER TABLE trades MODIFY COLUMN result ENUM('win','loss','tie','pending') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('global_controls');
    }
};
