<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'question_id' => $this->question_id,
            'choice_id'   => $this->choice_id,
            'is_correct'  => $this->when(
                $this->is_correct !== null,
                $this->is_correct,
            ),
            'question'    => $this->when($this->relationLoaded('question'), function () {
                return [
                    'id'     => $this->question->id,
                    'body'   => $this->question->body,
                    'type'   => $this->question->type,
                    'points' => $this->question->points,
                    'order'  => $this->question->order,
                ];
            }),
            'selected_choice' => $this->when($this->relationLoaded('choice'), function () {
                return [
                    'id'   => $this->choice->id,
                    'body' => $this->choice->body,
                ];
            }),
        ];
    }
}
