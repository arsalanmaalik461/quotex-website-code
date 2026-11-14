<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Profile page form collects Last Name, Date of birth, Address,
     * Nickname and the two 2FA toggles, but the users table had no columns
     * for any of them, so Save silently discarded everything except name
     * and country. These columns make the form fields persistable.
     * (AccountController::updateProfile still needs the matching patch
     * to actually save them — see the parent agent's report.)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
            $table->date('birthday')->nullable()->after('last_name');
            $table->string('address')->nullable()->after('birthday');
            $table->string('nickname')->nullable()->after('address');
            $table->boolean('login_2fa')->default(false)->after('nickname');
            $table->boolean('withdrawal_2fa')->default(false)->after('login_2fa');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'birthday', 'address', 'nickname', 'login_2fa', 'withdrawal_2fa']);
        });
    }
};
