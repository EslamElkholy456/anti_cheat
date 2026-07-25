<?php

namespace App\Services;

use App\Models\ExamSession;
use App\Models\Violation;
use App\Models\Warning;
use App\Repositories\Contracts\SessionRepositoryInterface;

class WarningService
{
    private const MAX_WARNINGS = 5;

    public function __construct(
        private readonly SessionRepositoryInterface $sessionRepository,
    ) {}

    private function sessionService(): SessionService
    {
        return app(SessionService::class);
    }

    public function processWarning(ExamSession $session, Violation $violation): array
    {
        $session->refresh();
        $newWarningCount = $session->warning_count + 1;

        $this->sessionRepository->update($session, [
            'warning_count' => $newWarningCount,
        ]);

        $warning = Warning::create([
            'session_id'     => $session->id,
            'violation_id'   => $violation->id,
            'warning_number' => $newWarningCount,
            'message'        => $violation->violation_type instanceof \App\Enums\ViolationType
                ? $violation->violation_type->warningMessage()
                : \App\Enums\ViolationType::from($violation->violation_type)->warningMessage(),
            'created_at'     => now(),
        ]);

        if ($newWarningCount >= self::MAX_WARNINGS) {
            $this->sessionService()->terminateExam(
                $session,
                "Exam terminated after {$newWarningCount} warnings.",
            );

            return [
                'action'          => 'terminate',
                'warning_count'   => $newWarningCount,
                'warning_message' => 'Exam terminated due to repeated violations.',
            ];
        }

        return [
            'action'          => 'warn',
            'warning_count'   => $newWarningCount,
            'warning_message' => $warning->message,
        ];
    }
}
