<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rests', function (Blueprint $table) {
            $table->id();
            $table->foreignID('attendance_id')->constrained()->onDelete('cascade');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE rests ADD CONSTRAINT rests_end_time_check CHECK (end_time IS NULL OR end_time > start_time)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rests');
    }
}
