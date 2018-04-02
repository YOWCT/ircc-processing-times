<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParserRunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parser_runs', function (Blueprint $table) {

            $requestTypes = [
                'visitor',
                'study',
                'work',
                'supervisa',
                'refugees_gov',
                'refugees_private',
                'child_dependent',
                'child_adopted',
            ];

            $table->increments('id');
            $table->timestamps();

            $table->smallInteger('file_retrieve_success')->default(0);
            $table->smallInteger('file_parse_success')->default(0);

            // For each of the 8 types
            // (We may not want to assume that these are always properly-formatted dates, hence a string format here.)

            foreach($requestTypes as $requestType) {
                $table->string($requestType . '_last_updated', 10)->index()->nullable();
                $table->smallInteger($requestType . '_is_new')->default(0);
            }
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parser_runs');
    }
}
