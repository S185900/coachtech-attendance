<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();

            $table->dateTime('corrected_start_time');
            $table->dateTime('corrected_end_time');

            $table->json('corrected_rest_times')->nullable();

            $table->text('reason');
            $table->tinyInteger('status')->default(0); // 0:承認待ち, 1:承認済み
            $table->foreignId('master_id')->nullable()->constrained('masters')->nullOnDelete();
            $table->string('master_comment', 255)->nullable();
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
        Schema::dropIfExists('stamp_correction_requests');
    }
}
