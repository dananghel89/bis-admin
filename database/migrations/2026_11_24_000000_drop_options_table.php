<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Site settings now live in config/project.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('options');
    }

    public function down(): void
    {
        Schema::create('options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('value');
        });
    }
};
