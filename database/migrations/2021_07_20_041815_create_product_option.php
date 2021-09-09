<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_option', function (Blueprint $table) {
            $table->Increments('id');
            $table->integer('product_id')->unsigned();
            $table->string('screen')->nullable();
            $table->string('screen_resolution')->nullable();
            $table->string('operating_system',100)->nullable();
            $table->string('cpu',100)->nullable();
            $table->string('gpu',100)->nullable();
            $table->string('ram')->nullable();
            $table->string('camera_fr')->nullable();
            $table->string('camera_ba')->nullable();
            $table->tinyInteger('pin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_option');
    }
}
