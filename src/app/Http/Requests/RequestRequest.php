<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        $workStartTime = $this->input('start_time');
        $workEndTime = $this->input('end_time');

        return [
            'attendance_id' => ['required', 'exists:attendances,id'],
            'remarks' => ['required','string','max:20'],

            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i','after:start_time'],

            'rests' => ['nullable', 'array'],

            'rests.*.start_time' => [
                'nullable',
                'required_with:rests.*.end_time',
                'date_format:H:i',
                // 休憩開始時間は、勤務開始時間より後
                'after_or_equal:' . $workStartTime,
                // 休憩開始時間は、勤務終了時間より前
                'before_or_equal:' . $workEndTime,
            ],
            'rests.*.end_time' => [
                'nullable',
                'required_with:rests.*.start_time',
                'date_format:H:i',
                // 休憩終了時間は、対応する休憩開始時間より後
                'after:rests.*.start_time',
                 // 休憩終了時間は、勤務終了時間より前
                'before_or_equal:' . $workEndTime,
            ],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください。',
            'start_time.date_format' => '出勤時間は「HH:mm」の形式で入力してください。',

            'end_time.required' => '退勤時間を入力してください。',
            'end_time.date_format' => '退勤時間は「HH:mm」の形式で入力してください。',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です。',

            'remarks.required' => '備考を記入してください。',
            'remarks.max' => '備考は20字以内で入力してください。',

            // 休憩時間用のメッセージ
            'rests.*.start_time.required_with' => '休憩の終了時間を入力する場合、開始時間も必須です。',
            'rests.*.start_time.date_format'   => '休憩の開始時間は「HH:mm」の形式で入力してください。',
            'rests.*.start_time.after_or_equal'  => '休憩時間が勤務時間外です。',
            'rests.*.start_time.before_or_equal' => '休憩時間が勤務時間外です。',

            'rests.*.end_time.required_with' => '休憩の開始時間を入力する場合、終了時間も必須です。',
            'rests.*.end_time.date_format'   => '休憩の終了時間は「HH:mm」の形式で入力してください。',
            'rests.*.end_time.after'         => '休憩開始時間もしくは終了時間が不適切な値です。',
            'rests.*.end_time.before_or_equal' => '休憩時間が勤務時間外です。',
        ];
    }
}