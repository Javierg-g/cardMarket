<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardInCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_in_collection', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("card_id");
            $table->unsignedBigInteger("collection_id");
            $table->foreign('card_id')->references('id')->on('cards');
            $table->foreign('collection_id')->references('id')->on('collection');
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
        Schema::dropIfExists('card_in_collection');
    }
}
