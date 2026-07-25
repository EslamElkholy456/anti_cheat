<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class JoinExamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'exam_code'              => ['required', 'string', 'size:6'],
            'university_student_id'  => ['required', 'string', 'max:50'],
        ];
    }
}
