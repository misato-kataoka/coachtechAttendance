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
        return true; // ここはtrueのままでOKです
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'remarks' => ['required', 'string', 'max:20'],
            'rests' => ['nullable', 'array'],
            'rests.*.id' => ['nullable', 'integer', 'exists:rests,id'],
            'rests.*.start_time' => [
                'nullable',
                'required_with:rests.*.end_time', // 終了時間が入力されていれば必須
                'date_format:H:i'
            ],
            'rests.*.end_time' => [
                'nullable',
                'required_with:rests.*.start_time', //開始時間が入力されていれば必須
                'date_format:H:i',
                'after:rests.*.start_time' // 対応する開始時間より後であること
            ],
        ];

        if (!$this->route('attendance')) {
            // どの勤怠に対する申請なのかを特定する必要があるため、'attendance_id' を必須ルールに追加
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
        return [
            // ★★★【修正】★★★ messagesも新しいルールキーに合わせます
            'start_time.required' => '出勤時間を入力してください。',
            'start_time.date_format' => '出勤時間は「HH:mm」の形式で入力してください。',

            'end_time.required' => '退勤時間を入力してください。',
            'end_time.date_format' => '退勤時間は「HH:mm」の形式で入力してください。',
            'end_time.after' => '退勤時間は、出勤時間より後の時刻を指定してください。',

            'remarks.required' => '備考欄は必須です',
            'remarks.string' => '備考欄は文字列で入力してください。',
            'remarks.max' => '備考欄は255文字以内で入力してください。',

            // 休憩時間（配列）用のメッセージ
            // ワイルドカード(*)により、どの休憩時間でエラーが起きてもこのメッセージが使われます
            'rests.*.start_time.required_with' => '休憩の終了時間を入力する場合は、開始時間も入力してください。',
            'rests.*.start_time.date_format' => '休憩の開始時間は「HH:mm」の形式で入力してください。',

            'rests.*.end_time.required_with' => '休憩の開始時間を入力する場合は、終了時間も入力してください。',
            'rests.*.end_time.date_format' => '休憩の終了時間は「HH:mm」の形式で入力してください。',
            'rests.*.end_time.after' => '休憩の終了時間は、その休憩の開始時間より後の時刻を指定してください。',
        ];
    }
}