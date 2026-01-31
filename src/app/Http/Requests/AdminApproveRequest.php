<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reason' => 'required|string|max:255',
            'rests.*.start' => [
                'nullable',
                'required_with:rests.*.end',
                $this->validateRestStartInWorkTime(),
            ],
            'rests.*.end' => [
                'nullable',
                'required_with:rests.*.start',
                $this->validateRestEndInWorkTime(),
            ],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required' => '退勤時間を入力してください',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
            'reason.max' => '備考は255文字以内で入力してください',
            'rests.*.start.required_with' => '休憩時間を入力してください',
            'rests.*.end.required_with' => '休憩時間を入力してください',
        ];
    }

    private function validateRestStartInWorkTime(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if ($value && ($value < $this->start_time || $value > $this->end_time)) {
                $fail('休憩時間が不適切な値です');
            }
        };
    }

    private function validateRestEndInWorkTime(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if (!$value) {
                return;
            }

            if ($value > $this->end_time) {
                $fail('休憩時間もしくは退勤時間が不適切な値です');
            }

            $index = explode('.', $attribute)[1];
            $restStart = $this->input("rests.{$index}.start");

            if ($restStart && $value < $restStart) {
                $fail('休憩時間が不適切な値です');
            }
        };
    }
}

