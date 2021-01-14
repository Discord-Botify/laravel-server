<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowedArtistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followed_artists', function (Blueprint $table) {
            $table->string('artist_id')->primary();
            $table->string('artist_name');
            $table->string('artist_href');
            $table->string('artist_uri');
            $table->integer('artist_album_count');
            $table->string('artist_last_album_id');
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
        Schema::dropIfExists('followed_artists');
    }
}
