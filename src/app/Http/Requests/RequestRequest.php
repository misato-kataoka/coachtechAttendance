<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class RequestRequest extends FormRequest
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
        $rules = [
            'attendance_id' => ['required', 'exists:attendances,id'],
            'remarks' => ['required','string','max:20'],

            //'rests' => ['sometimes', 'array'],
            //'rests.*.start_time' => ['nullable','date_format:H:i'],
            //'rests.*.end_time' => ['nullable','date_format:H:i','after:rests.*.start_time'],

            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ];

            $rules['rests'][] = function ($attribute, $value, $fail) {
                    $workStartTime = $this->input('start_time');
                    $workEndTime = $this->input('end_time');

                    if (!$workStartTime || !$workEndTime) {
                        return;
                    }

                    foreach ($value as $index => $rest) {
                        $restStart = $rest['start_time'] ?? null;
                        $restEnd = $rest['end_time'] ?? null;

                        if ($restStart && $restEnd) {
                            if ($restStart < $workStartTime || $restEnd > $workEndTime || $restEnd < $restStart) {
                                $fail('休憩時間が勤務時間外です');
                                return;
                            }
                        }
                    }
                };

                return $rules;
            }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');

            if ($startTime && $endTime && $startTime >= $endTime) {
                $validator->errors()->add(
                    'start_time',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }
        });
    }

    public function messages(){
        return [
            'start_time.required'=>'勤務時間を入力してください',
            'end_time.required' =>'退勤時間を入力してください',
            'remarks.required'=>'備考を記入してください',
            'remarks.max' => '備考は20字以内で入力してください',
        ];
    }
}