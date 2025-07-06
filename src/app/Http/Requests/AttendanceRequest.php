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
        return [
            'attendance'=>['required', 'date_format:H:i','start_time:before:end_time'],
            'rest'=>['nullable', 'date_format:H:i', 'start_time:before:end_time'],
            'remarks'=>['required','string'],
        ];
    }

    public function messages(){
        return [
            'attendance.required'=>'出勤時間・退勤時間を入力してください',
            'attendance.date_format'=>'出勤時間は「時:分」の形式で入力してください',
            'attendance.start_time.before'=>'出勤時間もしくは退勤時間が不適切な値です',
            'rest.date_format'=>'休憩時間は「時:分」の形式で入力してください',
            'rest.start_time.before'=>'休憩時間が不適切な値です',
            'remarks.required' => '備考を記入してください',
        ];
    }
}

