<?php

namespace App\Http\Requests\AI;

use App\Enums\ViolationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ViolationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'session_id'       => ['required', 'integer', 'exists:exam_sessions,id'],
            'student_id'       => ['required', 'integer', 'exists:users,id'],
            'violation_type'   => ['required', new Enum(ViolationType::class)],
            'confidence'       => ['required', 'numeric', 'min:0', 'max:1'],
            'duration_seconds' => ['required', 'integer', 'min:0'],
            'snapshot_b64'     => ['nullable', 'string'],
            'detected_at'      => ['required', 'date'],
        ];
    }
}
