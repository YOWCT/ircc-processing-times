<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountryProcessingTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('country_processing_times', function (Blueprint $table) {
            $table->increments('id');

            $table->string('request_type', 24)->index()->nullable();
            $table->string('country_abbr', 2)->index()->nullable();
            $table->integer('weeks')->nullable();

            $table->string('last_updated', 10)->index()->nullable();
            $table->integer('parser_run_id')->nullable();

            $table->timestamps();

            $table->foreign('parser_run_id')->references('id')->on('parser_runs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('country_processing_times');
    }
}
