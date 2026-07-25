<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViolationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'violation_type'   => $this->violation_type,
            'confidence'       => $this->confidence,
            'duration_seconds' => $this->duration_seconds,
            'snapshot_url'     => $this->snapshot_path
                ? url(route('violations.snapshot', $this->id, false))
                : null,
            'detected_at'      => $this->detected_at->toISOString(),
        ];
    }
}
