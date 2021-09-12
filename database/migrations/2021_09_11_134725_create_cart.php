<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart', function (Blueprint $table) {
            $table->Increments('id');
            $table->integer('sku_id');
            $table->string('name');
            $table->float('unit_price');
            $table->float('promotion_price');
            $table->chart('color',50);
            $table->char('slug');
            $table->float('discount');
            $table->integer('qty');
            $table->char('image',100);
            $table->char('address_ip');
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
        Schema::dropIfExists('cart');
    }
}
