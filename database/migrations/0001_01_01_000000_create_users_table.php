<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Accounts and password reset tokens. Staff create every account (no sign-up).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedTinyInteger('role')->default(1)->comment('0 admin (Staff), 1 editor (Architect)');
            $table->rememberToken();
            $table->timestamps();
        });

        // config/auth.php points the password broker at this table.
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
};
