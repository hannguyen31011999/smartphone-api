<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->Increments('id');
            $table->char('email',100);
            $table->char('password');
            $table->string('name',100);
            $table->tinyInteger('gender')->nullable();
            $table->dateTime('birth')->nullable();
            $table->char('phone',15);
            $table->string('address');
            $table->tinyInteger('status');
            $table->tinyInteger('role');
            $table->char('remember_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
