<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each user's own mailbox settings for Alerts: IMAP to read, SMTP to send.
 * The password is stored encrypted (MailAccount casts it).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('username')->nullable()->comment('defaults to the email');
            $table->text('password');
            $table->string('imap_host');
            $table->unsignedSmallInteger('imap_port')->default(993);
            $table->string('imap_encryption', 10)->default('ssl')->comment('ssl | starttls | none');
            $table->string('smtp_host');
            $table->unsignedSmallInteger('smtp_port')->default(465);
            $table->string('smtp_encryption', 10)->default('ssl')->comment('ssl | starttls | none');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_accounts');
    }
};
