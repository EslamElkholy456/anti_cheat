<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'university_student_id',
        'status',
        'started_at',
        'submitted_at',
        'auto_submitted',
        'score',
        'passed',
        'warning_count',
        'termination_reason',
        'ip_address',
        'device_info',
    ];

    protected function casts(): array
    {
        return [
            'started_at'     => 'datetime',
            'submitted_at'   => 'datetime',
            'auto_submitted' => 'boolean',
            'score'          => 'float',
            'passed'         => 'boolean',
            'warning_count'  => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'session_id');
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'session_id');
    }

    public function warnings(): HasMany
    {
        return $this->hasMany(Warning::class, 'session_id');
    }

    public function aiReport(): HasOne
    {
        return $this->hasOne(AiReport::class, 'session_id');
    }
}
