<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdCharacteristicTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prod_characteristic', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('prod_id')->unsigned();
            $table->bigInteger('characteristic_id')->unsigned();
            $table->string('value');
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
        Schema::dropIfExists('prod_characteristic');
    }
}
