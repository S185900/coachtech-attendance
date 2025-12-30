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
            'start_time' => ['required'], // 出勤は基本必須
            'end_time'   => ['nullable', 'after:start_time'], // null(空)を許容し、入力時のみ比較
            'reason'     => ['required', 'string', 'max:255'],
            'rests.*.start' => ['required'],
            'rests.*.end'   => ['nullable', 'after:rests.*.start'],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間は必須です。',
            'end_time.required' => '退勤時間は必須です。',
            'end_time.after' => '退勤時間は出勤時間より後の時間を指定してください。',
            'rests.*.start.required' => '休憩開始時間は必須です。',
            'rests.*.end.required' => '休憩終了時間は必須です。',
            'rests.*.end.after' => '休憩終了時間は休憩開始時間より後の時間を指定してください。',
            'reason.required' => '備考を入力してください。',
            'reason.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
