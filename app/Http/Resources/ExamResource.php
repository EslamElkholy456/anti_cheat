<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'subject'          => $this->subject,
            'description'      => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'passing_grade'    => $this->passing_grade,
            'start_at'         => $this->start_at?->toISOString(),
            'end_at'           => $this->end_at?->toISOString(),
            'exam_code'        => $this->when($request->user()?->isInstructor(), $this->exam_code),
            'status'           => $this->status,
            'total_questions'  => $this->total_questions,
            'max_score'        => $this->max_score,
            'is_available'     => $this->isAvailable(),
            'instructor'       => new UserResource($this->whenLoaded('instructor')),
            'questions'        => QuestionResource::collection($this->whenLoaded('questions')),
            'questions_count'  => $this->whenCounted('questions'),
            'created_at'       => $this->created_at?->toISOString(),
        ];
    }
}
