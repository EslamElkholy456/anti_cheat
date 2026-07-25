<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'body'    => $this->body,
            'type'    => $this->type,
            'points'  => $this->points,
            'order'   => $this->order,
            'choices' => ChoiceResource::collection($this->whenLoaded('choices')),
        ];
    }
}
