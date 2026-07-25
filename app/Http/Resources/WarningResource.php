<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'warning_number' => $this->warning_number,
            'message'        => $this->message,
            'violation'      => new ViolationResource($this->whenLoaded('violation')),
            'created_at'     => $this->created_at->toISOString(),
        ];
    }
}
