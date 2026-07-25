<?php

namespace App\Enums;

enum ViolationType: string
{
    case NoFace          = 'no_face';
    case MultiplePersons = 'multiple_persons';
    case LookLeft        = 'look_left';
    case LookRight       = 'look_right';
    case LookUp          = 'look_up';
    case LookDown        = 'look_down';
    case PhoneDetected   = 'phone_detected';

    public function isCritical(): bool
    {
        return $this === self::PhoneDetected || $this === self::MultiplePersons;
    }

    public function warningMessage(): string
    {
        return match($this) {
            self::NoFace          => 'No face detected. Please ensure your face is visible.',
            self::MultiplePersons => 'Multiple persons detected. Only one person is allowed.',
            self::LookLeft        => 'Please look at the screen.',
            self::LookRight       => 'Please look at the screen.',
            self::LookUp          => 'Please look at the screen.',
            self::LookDown        => 'Please look at the screen.',
            self::PhoneDetected   => 'Mobile phone detected. Exam will be terminated.',
        };
    }
}
