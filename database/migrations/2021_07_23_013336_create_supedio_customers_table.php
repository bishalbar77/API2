<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupedioCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supedio_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supedio_id');
            $table->string('name');
            $table->unsignedBigInteger('customer_number')->unique();
            $table->timestamps();
        });

        Schema::table('supedio_customers',function($table){
            $table->foreign('supedio_id')->references('id')->on('supedios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supedio_customers');
    }
}
