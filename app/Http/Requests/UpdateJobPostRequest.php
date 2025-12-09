<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Code;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobPostRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを実行する権限があるか判定
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを取得
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 雇用形態の有効なtype_idを取得
        $validEmploymentTypes = Code::query()->where('type', 1)->pluck('type_id')->toArray();

        // 勤務形態の有効なtype_idを取得
        $validWorkStyles = Code::query()->where('type', 2)->pluck('type_id')->toArray();

        // 業種の有効なtype_idを取得
        $validIndustries = Code::query()->where('type', 3)->pluck('type_id')->toArray();

        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'employment_type_id' => ['required', 'integer', Rule::in($validEmploymentTypes)],
            'work_style_id' => ['required', 'integer', Rule::in($validWorkStyles)],
            'industry_id' => ['required', 'integer', Rule::in($validIndustries)],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'working_hours' => ['required', 'string', 'max:100'],
            'salary' => ['required', 'integer', 'min:0'],
            'number_of_positions' => ['required', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }

    /**
     * バリデーションエラーメッセージをカスタマイズ
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => '求人タイトルを入力してください。',
            'title.max' => '求人タイトルは100文字以内で入力してください。',
            'description.required' => '詳細内容を入力してください。',
            'description.max' => '詳細内容は1000文字以内で入力してください。',
            'employment_type_id.required' => '雇用形態を選択してください。',
            'employment_type_id.in' => '有効な雇用形態を選択してください。',
            'work_style_id.required' => '勤務形態を選択してください。',
            'work_style_id.in' => '有効な勤務形態を選択してください。',
            'industry_id.required' => '業種を選択してください。',
            'industry_id.in' => '有効な業種を選択してください。',
            'location_id.required' => '勤務地を選択してください。',
            'location_id.exists' => '有効な勤務地を選択してください。',
            'working_hours.required' => '勤務時間を入力してください。',
            'working_hours.max' => '勤務時間は100文字以内で入力してください。',
            'salary.required' => '給与を入力してください。',
            'salary.integer' => '給与は数値で入力してください。',
            'salary.min' => '給与は0以上で入力してください。',
            'number_of_positions.required' => '募集人数を入力してください。',
            'number_of_positions.integer' => '募集人数は数値で入力してください。',
            'number_of_positions.min' => '募集人数は1以上で入力してください。',
            'expires_at.date' => '募集期限は有効な日付で入力してください。',
            'expires_at.after' => '募集期限は今日より後の日付を指定してください。',
        ];
    }

    /**
     * バリデーション属性名をカスタマイズ
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => '求人タイトル',
            'description' => '詳細内容',
            'employment_type_id' => '雇用形態',
            'work_style_id' => '勤務形態',
            'industry_id' => '業種',
            'location_id' => '勤務地',
            'working_hours' => '勤務時間',
            'salary' => '給与',
            'number_of_positions' => '募集人数',
            'expires_at' => '募集期限',
        ];
    }
}
