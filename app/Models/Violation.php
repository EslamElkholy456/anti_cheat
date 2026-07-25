<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Violation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'student_id',
        'violation_type',
        'confidence',
        'duration_seconds',
        'snapshot_path',
        'detected_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence'   => 'float',
            'detected_at'  => 'datetime',
            'created_at'   => 'datetime',
            'duration_seconds' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function warning(): HasOne
    {
        return $this->hasOne(Warning::class);
    }
}
