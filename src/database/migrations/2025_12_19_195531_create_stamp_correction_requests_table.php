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

            $table->dateTime('corrected_start_time'); // 修正後の出勤時間 [cite: 13, 16]
            $table->dateTime('corrected_end_time');   // 修正後の退勤時間

            // ★休憩時間の修正内容をJSON形式で保存（複数対応）
            // 例: [{"rest_id":1, "start":"12:00", "end":"13:00"}, ...]
            $table->json('corrected_rest_times')->nullable();

            $table->text('reason'); // 修正理由（FN029備考欄など） [cite: 14]
            // 0:承認待ち, 1:承認済み [cite: 6, 7]
            $table->tinyInteger('status')->default(0); 
            // 承認した管理者ID（申請時はNULL）
            $table->foreignId('master_id')->nullable()->constrained('masters')->nullOnDelete();
            $table->string('master_comment', 255)->nullable(); // 管理者コメント
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
