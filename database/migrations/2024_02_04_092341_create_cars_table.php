<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->increments('id');

            $table->string('license', 255)->charset('utf8')->nullable();
            $table->string('mile', 255)->charset('utf8')->nullable();

            $table->integer('brand_model_id')->unsigned()->index();
            $table->foreign('brand_model_id')->references('id')->on('brand_models')->onDelete('cascade');

            $table->string('color', 255)->charset('utf8')->nullable();


            $table->enum('type', ['Rent', 'Other'])->charset('utf8')->default('Other');

            $table->enum('status', ['Available', 'Unavailable'])->charset('utf8')->default('Available');

            $table->string('create_by', 100)->charset('utf8')->nullable();
            $table->string('update_by', 100)->charset('utf8')->nullable();
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
        Schema::dropIfExists('cars');
    }
}
