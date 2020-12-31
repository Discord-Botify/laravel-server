<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('email');
            $table->string('spotify_access_token');
            $table->dateTime('spotify_access_token_expiration');
            $table->string('spotify_refresh_token');
            $table->string('spotify_refresh_token_expiration');
            $table->string('spotify_user_name');
            $table->string('spotify_id');
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
        Schema::dropIfExists('users');
    }
}
