<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_time' => 'required',
            'end_time'   => 'required',
            'reason'     => 'required|string|max:255',
            'rests.*.start' => 'nullable|required_with:rests.*.end',
            'rests.*.end'   => 'nullable|required_with:rests.*.start',
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required'   => '退勤時間を入力してください',
            'reason.required'     => '備考を入力してください',
            'reason.max'          => '備考は255文字以内で入力してください',
            'rests.*.start.required' => '休憩開始時間を入力してください',
            'rests.*.end.required'   => '休憩終了時間を入力してください',
        ];
    }

    /**
     * カスタムバリデーション（時間の整合性チェック）
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time');
            $end = $this->input('end_time');

            // 1. 出勤・退勤の前後関係のみをチェック
            // 片方でも未入力なら不適切（要件FN029）
            if (!$start || !$end || $start >= $end) {
                $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
                // 出勤/退勤が不正な場合は、休憩のチェックをスキップしてメッセージの重複を防ぐ
                return;
            }

            // 2. 出勤/退勤が正常な場合のみ、休憩がその範囲内かチェック
            $rests = $this->input('rests', []);
            foreach ($rests as $index => $rest) {
                $restStart = $rest['start'];
                $restEnd = $rest['end'];

                if ($restStart && $restEnd) {
                    // 休憩自体の前後関係
                    if ($restStart >= $restEnd) {
                        $validator->errors()->add("rests.{$index}.start", '休憩時間もしくは休憩終了時間が不適切な値です');
                    }
                    // 勤務時間外のチェック
                    elseif ($restStart < $start || $restEnd > $end) {
                        $validator->errors()->add("rests.{$index}.start", '休憩時間が勤務時間外です');
                    }
                }
            }
        });
    }
}
