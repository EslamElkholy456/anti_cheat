<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warning extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'violation_id',
        'warning_number',
        'message',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'warning_number' => 'integer',
            'created_at'     => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'session_id');
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }
}
