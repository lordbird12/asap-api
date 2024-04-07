<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketToppicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_toppics', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('ticket_id')->unsigned()->index();
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');

            $table->enum('status', ['อุบัติเหตุ', 'รถเสียฉุกเฉิน', 'ติดตามรถทดแทน', 'ติดตามงานบริหารรถยนต์', 'อื่นๆ'])->charset('utf8')->default('อุบัติเหตุ');


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
        Schema::dropIfExists('ticket_toppics');
    }
}
