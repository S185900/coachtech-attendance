<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // ユーザーとの紐付け
            $table->date('date'); // 勤怠日 [cite: 5]
            $table->dateTime('start_time'); // 出勤時刻
            $table->dateTime('end_time')->nullable(); // 退勤時刻（退勤前はNULL）
            // 0:勤務外, 1:勤務中, 2:休憩中, 3:退勤済 [cite: 4, 10]
            $table->tinyInteger('status')->default(0); 
            $table->boolean('is_corrected')->default(false); // 修正済みフラグ
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
        Schema::dropIfExists('attendances');
    }
}
