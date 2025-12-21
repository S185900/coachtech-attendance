<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_times', function (Blueprint $table) {
            $table->id();
            // 勤怠テーブル（attendances）との紐付け
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_time')->comment('休憩開始');
            $table->dateTime('end_time')->nullable()->comment('休憩終了');
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
        Schema::dropIfExists('rest_times');
    }
}
