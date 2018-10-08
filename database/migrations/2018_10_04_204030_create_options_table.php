<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('value');
        });

        DB::table('options')->insert([
            [
                'name' => 'site_title',
                'value' => 'BIS',
            ],
            [
                'name' => 'tagline',
                'value' => 'Architecture Web Application',
            ],
            [
                'name' => 'email_address',
                'value' => 'dev@dananghel.com',
            ],
            [
                'name' => 'membership',
                'value' => '1',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('options');
    }
}
