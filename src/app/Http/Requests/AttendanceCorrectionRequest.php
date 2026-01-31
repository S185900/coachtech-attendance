<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
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
                $this->validateRestStart(),
            ],
            'rests.*.end' => [
                'nullable',
                'required_with:rests.*.start',
                $this->validateRestEnd(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required' => '退勤時間を入力してください',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
            'reason.max' => '備考は255文字以内で入力してください',
        ];
    }

    private function validateRestStart(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if (!$value || !$this->start_time || !$this->end_time) {
                return;
            }

            if ($value < $this->start_time || $value > $this->end_time) {
                $fail('休憩時間が不適切な値です');
            }
        };
    }

    private function validateRestEnd(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if (!$value) {
                return;
            }

            if ($value > $this->end_time) {
                $fail('休憩時間もしくは退勤時間が不適切な値です');
                return;
            }

            $restIndex = explode('.', $attribute)[1];
            $restStart = $this->input("rests.{$restIndex}.start");

            if ($restStart && $value <= $restStart) {
                $fail('休憩時間が不適切な値です');
            }
        };
    }
}