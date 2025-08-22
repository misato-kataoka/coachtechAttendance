<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
            'start_time' => ['required', 'date_format:H:i', 'before:end_time'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'remarks' => ['required', 'string', 'max:255'],
            'rests' => ['nullable', 'array'],
            'rests.*.id' => ['nullable', 'integer', 'exists:rests,id'],
            'rests.*.start_time' => [
                'nullable',
                'required_with:rests.*.end_time',
                'date_format:H:i',
                'after:start_time',
                'before:end_time',
            ],
            'rests.*.end_time' => [
                'nullable',
                'required_with:rests.*.start_time',
                'date_format:H:i',
                'after:rests.*.start_time',
                'before:end_time',
            ],
        ];

        if (!$this->route('attendance')) {
            $rules['attendance_id'] = ['required', 'integer', 'exists:attendances,id'];
        }

        return $rules;
    }

    /**
     * Get the custom validation messages for the defined rules.
     *
     * @return array
     */
    public function messages()
    {
        if ($this->routeIs('admin.*')) {
            // 管理者用の統一されたエラーメッセージを返す
            $message = '出勤時間もしくは退勤時間が不適切な値です';
            return [
                'end_time.after'            => $message,
                'rests.*.start_time.after'  => $message,
                'rests.*.start_time.before' => $message,
                'rests.*.end_time.before'   => $message,
            ];
        }

        return [
            'start_time.required' => '出勤時間を入力してください。',
            'start_time.date_format' => '出勤時間は「HH:mm」の形式で入力してください。',

            'end_time.required' => '退勤時間を入力してください。',
            'end_time.date_format' => '退勤時間は「HH:mm」の形式で入力してください。',
            'end_time.after' => '退勤時間は、出勤時間より後の時刻を指定してください。',

            'remarks.required' => '備考欄は必須です',
            'remarks.string' => '備考欄は文字列で入力してください。',
            'remarks.max' => '備考欄は255文字以内で入力してください。',

            'rests.*.start_time.required_with' => '休憩の終了時間を入力する場合は、開始時間も入力してください。',
            'rests.*.start_time.date_format' => '休憩の開始時間は「HH:mm」の形式で入力してください。',
            'rests.*.start_time.after'  => '休憩時間が不適切な値です。',
            'rests.*.start_time.before' => '休憩時間が不適切な値です。',

            'rests.*.end_time.before'   => '休憩時間は勤務時間内に設定してください。',
            'rests.*.end_time.required_with' => '休憩の開始時間を入力する場合は、終了時間も入力してください。',
            'rests.*.end_time.date_format' => '休憩の終了時間は「HH:mm」の形式で入力してください。',
            'rests.*.end_time.after' => '出勤時間もしくは退勤時間が不適切な値です。',
        ];
    }
}