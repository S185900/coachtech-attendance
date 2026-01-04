<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminApproveRequest extends FormRequest
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
            'end_time'   => 'required|after:start_time',
            'reason'     => 'required|string', // FN039-4: 備考は必須

            'rests.*.start' => [
                'nullable',
                'required_with:rests.*.end',
                function ($attribute, $value, $fail) {
                    // FN039-2: 休憩開始が出勤前、または退勤後
                    if ($value && ($value < $this->start_time || $value > $this->end_time)) {
                        $fail('休憩時間が不適切な値です');
                    }
                },
            ],

            'rests.*.end' => [
                'nullable',
                'required_with:rests.*.start',
                function ($attribute, $value, $fail) {
                    // FN039-3: 休憩終了が退勤後
                    if ($value && $value > $this->end_time) {
                        $fail('休憩時間もしくは退勤時間が不適切な値です');
                    }
                    // 追加：休憩終了が休憩開始より前の場合（一般的な不備として）
                    $index = explode('.', $attribute)[1];
                    $restStart = $this->input("rests.{$index}.start");
                    if ($value && $restStart && $value < $restStart) {
                        $fail('休憩時間が不適切な値です');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            // FN039-1: 出勤・退勤の不備
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required'   => '退勤時間を入力してください',
            'end_time.after'      => '出勤時間もしくは退勤時間が不適切な値です',

            // FN039-4: 備考の未入力
            'reason.required'     => '備考を記入してください',
            'reason.max'          => '備考は255文字以内で入力してください',

            // 休憩に関するメッセージ
            // ※休憩の「不適切な値」メッセージは、rules()内のクロージャ（$fail）で
            // 直接指定するため、ここには書かなくても要件通りの文言が出力されます。
            'rests.*.start.required_with' => '休憩時間を入力してください',
            'rests.*.end.required_with'   => '休憩時間を入力してください',
        ];
    }
}
