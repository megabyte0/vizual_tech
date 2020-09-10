<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prods', function (Blueprint $table) {
            //$table->id();
            // https://stackoverflow.com/a/45351580
            $table->bigInteger('id')->unsigned();
            $table->primary('id');
            $table->string('name');
            $table->decimal('price', 15, 5);
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prods');
    }
}
