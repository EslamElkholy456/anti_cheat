<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiReport extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'total_violations',
        'total_warnings',
        'phone_detected_count',
        'no_face_count',
        'multiple_persons_count',
        'gaze_away_count',
        'risk_score',
        'summary',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'risk_score'   => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'session_id');
    }
}
