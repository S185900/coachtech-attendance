<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => 'required',
            'end_time'   => 'required',
            'reason'     => 'required|string|max:255',
            'rests.*.start' => 'nullable',
            'rests.*.end'   => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required'   => '退勤時間を入力してください',
            'reason.required'     => '備考を記入してください',
            'reason.max'          => '備考は255文字以内で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time');
            $end = $this->input('end_time');

            if ($start && $end && $start >= $end) {
                $validator->errors()->add('end_time', '出勤時間もしくは退勤時間が不適切な値です');
                return;
            }

            $rests = $this->input('rests', []);
            foreach ($rests as $index => $rest) {
                $restStart = $rest['start'] ?? null;
                $restEnd = $rest['end'] ?? null;

                if ($restStart && $restEnd) {
                    if ($restStart > $end) {
                        $validator->errors()->add("rests.{$index}.start", '休憩時間が不適切な値です');
                    }
                    elseif ($restEnd > $end) {
                        $validator->errors()->add("rests.{$index}.end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                    elseif ($restStart >= $restEnd || $restStart < $start) {
                        $validator->errors()->add("rests.{$index}.start", '休憩時間が勤務時間外です');
                    }
                }
            }
        });
    }
}