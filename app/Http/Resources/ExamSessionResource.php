<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $timeTakenSeconds = null;
        if ($this->submitted_at && $this->started_at) {
            $timeTakenSeconds = $this->submitted_at->diffInSeconds($this->started_at);
        }

        return [
            'id'                      => $this->id,
            'status'                  => $this->status,
            'university_student_id'   => $this->university_student_id,
            'auto_submitted'          => (bool) $this->auto_submitted,
            'started_at'              => $this->started_at?->toISOString(),
            'submitted_at'            => $this->submitted_at?->toISOString(),
            'time_taken_seconds'      => $timeTakenSeconds,
            'score'                   => $this->score,
            'passed'                  => $this->passed,
            'warning_count'           => $this->warning_count,
            'termination_reason'      => $this->termination_reason,
            'exam'                    => new ExamResource($this->whenLoaded('exam')),
            'student'                 => new UserResource($this->whenLoaded('student')),
            'answers'                 => AnswerResource::collection($this->whenLoaded('answers')),
            'violations'              => ViolationResource::collection($this->whenLoaded('violations')),
            'violations_count'        => $this->violations_count ?? $this->when($this->relationLoaded('violations'), fn() => $this->violations->count()),
            'warnings'                => WarningResource::collection($this->whenLoaded('warnings')),
            'ai_report'               => new AiReportResource($this->whenLoaded('aiReport')),
            'created_at'              => $this->created_at?->toISOString(),
        ];
    }
}
