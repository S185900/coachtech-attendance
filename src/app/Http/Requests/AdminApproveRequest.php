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
            'reason'     => 'required|string',

            'rests.*.start' => [
                'nullable',
                'required_with:rests.*.end',
                function ($attribute, $value, $fail) {
                    if ($value && ($value < $this->start_time || $value > $this->end_time)) {
                        $fail('休憩時間が不適切な値です');
                    }
                },
            ],

            'rests.*.end' => [
                'nullable',
                'required_with:rests.*.start',
                function ($attribute, $value, $fail) {

                    if ($value && $value > $this->end_time) {
                        $fail('休憩時間もしくは退勤時間が不適切な値です');
                    }

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
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required'   => '退勤時間を入力してください',
            'end_time.after'      => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required'     => '備考を記入してください',
            'reason.max'          => '備考は255文字以内で入力してください',
            'rests.*.start.required_with' => '休憩時間を入力してください',
            'rests.*.end.required_with'   => '休憩時間を入力してください',
        ];
    }
}
