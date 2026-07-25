<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'instructor_id',
        'title',
        'subject',
        'description',
        'duration_minutes',
        'passing_grade',
        'start_at',
        'end_at',
        'exam_code',
        'qr_code_path',
        'status',
        'total_questions',
        'max_score',
    ];

    protected function casts(): array
    {
        return [
            'start_at'   => 'datetime',
            'end_at'     => 'datetime',
            'passing_grade' => 'integer',
            'duration_minutes' => 'integer',
            'total_questions' => 'integer',
            'max_score' => 'integer',
        ];
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'published'
            && now()->between($this->start_at, $this->end_at);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function getMaxScoreAttribute(): int
    {
        if ($this->relationLoaded('questions') && $this->questions->isNotEmpty()) {
            return $this->questions->sum('points');
        }
        return (int) ($this->attributes['max_score'] ?? 0);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }
}
