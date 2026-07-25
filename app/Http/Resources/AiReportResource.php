<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'total_violations'       => $this->total_violations,
            'total_warnings'         => $this->total_warnings,
            'phone_detected_count'   => $this->phone_detected_count,
            'no_face_count'          => $this->no_face_count,
            'multiple_persons_count' => $this->multiple_persons_count,
            'gaze_away_count'        => $this->gaze_away_count,
            'risk_score'             => $this->risk_score,
            'summary'                => $this->summary,
            'generated_at'           => $this->generated_at->toISOString(),
        ];
    }
}
