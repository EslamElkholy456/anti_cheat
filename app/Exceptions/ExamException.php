<?php

namespace App\Exceptions;

use RuntimeException;

class ExamException extends RuntimeException
{
    public function __construct(string $message, private readonly int $statusCode = 422)
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public static function notAvailable(): self
    {
        return new self('This exam is not currently available.', 422);
    }

    public static function alreadyJoined(): self
    {
        return new self('You have already joined this exam.', 422);
    }

    public static function sessionNotActive(): self
    {
        return new self('Exam session is not active.', 422);
    }

    public static function sessionAlreadySubmitted(): self
    {
        return new self('Exam has already been submitted.', 422);
    }

    public static function invalidCode(): self
    {
        return new self('Invalid exam code. Please check and try again.', 404);
    }

    public static function duplicateStudentId(): self
    {
        return new self('This university student ID is already registered for this exam.', 409);
    }
}
