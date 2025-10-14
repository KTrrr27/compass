<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
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

    //バリデーション前に日付をまとめる
    protected function prepareForValidation()
    {
        $this->merge([
            'birth_day' => sprintf('%04d-%02d-%02d', $this->old_year, $this->old_month, $this->old_day),
        ]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    //バリデーションルール
    public function rules()
    {
        return [
            //
            'over_name' => ['required', 'string', 'max:10'],
            'under_name' => ['required', 'string', 'max:10'],
            'over_name_kana' => ['required', 'string', 'regex:/^[ア-ン゛゜ァ-ォャ-ョー]+$/u', 'max:30'],
            'under_name_kana' => ['required', 'string', 'regex:/^[ア-ン゛゜ァ-ォャ-ョー]+$/u', 'max:30'],
            'mail_address' => ['required', 'email', Rule::unique('users', 'mail_address'), 'max:100'],
            'sex' => ['required', 'in:1,2,3'],
            'birth_day' => ['required', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:' . now()->format('Y-m-d')],
            'role' => ['required', 'in:1,2,3,4'],
            'password' => ['required', 'min:8', 'max:30', 'confirmed']
        ];
    }

    //エラーメッセージの項目名
    public function attributes(): array
    {
        return [
            'over_name' => '姓',
            'under_name' => '名',
            'over_name_kana' => 'セイ',
            'under_name_kana' => 'メイ',
            'mail_address' => 'メールアドレス',
            'sex' => '性別',
            'birth_day' => '生年月日',
            'role' => '役職',
            'password' => 'パスワード',
            'password_confirmation' => '確認用パスワード',
        ];
    }
}
