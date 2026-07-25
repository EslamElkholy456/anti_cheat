<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuestionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'body'                       => ['required', 'string', 'max:2000'],
            'type'                       => ['required', 'in:mcq,true_false'],
            'points'                     => ['required', 'integer', 'min:1', 'max:10'],
            'choices'                    => ['required', 'array', 'min:2', 'max:4'],
            'choices.*.body'             => ['required', 'string', 'max:500'],
            'choices.*.is_correct'       => ['required', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $choices = $this->input('choices', []);
            $correctCount = collect($choices)->where('is_correct', true)->count();

            if ($correctCount !== 1) {
                $validator->errors()->add('choices', 'Exactly one choice must be marked as correct.');
            }

            if ($this->input('type') === 'true_false' && count($choices) !== 2) {
                $validator->errors()->add('choices', 'True/False questions must have exactly 2 choices.');
            }
        });
    }
}
