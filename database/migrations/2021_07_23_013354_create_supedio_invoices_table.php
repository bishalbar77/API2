<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupedioInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supedio_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supedio_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('file_name');
            $table->longText('base_64');
            $table->string('pdf_file');
            $table->unsignedBigInteger('orga_number')->unique();
            $table->unsignedBigInteger('process_number')->unique();
            $table->timestamps();
        });

        Schema::table('supedio_invoices',function($table){
            $table->foreign('supedio_id')->references('id')->on('supedios')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('supedio_customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supedio_invoices');
    }
}
