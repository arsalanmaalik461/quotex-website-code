<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tournaments')) {
            Schema::create('tournaments', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->decimal('prize_pool', 12, 2)->default(1000.00);
                $table->decimal('entry_fee', 10, 2)->default(0.00);
                $table->string('duration')->default('1 day');
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->string('badge_text')->nullable();
                $table->enum('status', ['active', 'completed'])->default('active');
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->text('rules')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
