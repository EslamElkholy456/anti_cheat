<?php

namespace App\Services;

use App\Enums\ViolationType;
use App\Models\AiReport;
use App\Models\ExamSession;

class ReportService
{
    public function generateReport(ExamSession $session): AiReport
    {
        $session->load('violations', 'warnings');

        $violations = $session->violations;

        $phoneCount    = $violations->where('violation_type', ViolationType::PhoneDetected->value)->count();
        $noFaceCount   = $violations->where('violation_type', ViolationType::NoFace->value)->count();
        $multipleCount = $violations->where('violation_type', ViolationType::MultiplePersons->value)->count();
        $gazeCount     = $violations->whereIn('violation_type', [
            ViolationType::LookLeft->value,
            ViolationType::LookRight->value,
            ViolationType::LookUp->value,
            ViolationType::LookDown->value,
        ])->count();

        $totalViolations = $violations->count();
        $totalWarnings   = $session->warnings->count();

        $riskScore = $this->calculateRiskScore(
            $totalViolations,
            $totalWarnings,
            $phoneCount,
            $multipleCount,
            $session->status,
        );

        $summary = $this->buildSummary($session, $totalViolations, $totalWarnings, $riskScore);

        return AiReport::updateOrCreate(
            ['session_id' => $session->id],
            [
                'total_violations'       => $totalViolations,
                'total_warnings'         => $totalWarnings,
                'phone_detected_count'   => $phoneCount,
                'no_face_count'          => $noFaceCount,
                'multiple_persons_count' => $multipleCount,
                'gaze_away_count'        => $gazeCount,
                'risk_score'             => $riskScore,
                'summary'                => $summary,
                'generated_at'           => now(),
            ],
        );
    }

    private function calculateRiskScore(
        int $totalViolations,
        int $totalWarnings,
        int $phoneCount,
        int $multipleCount,
        string $status,
    ): int {
        $score = 0;
        $score += min($totalViolations * 2, 40);
        $score += min($totalWarnings * 10, 30);
        $score += min($phoneCount * 20, 20);
        $score += min($multipleCount * 10, 10);

        if ($status === 'terminated') {
            $score = min($score + 20, 100);
        }

        return min($score, 100);
    }

    private function buildSummary(
        ExamSession $session,
        int $totalViolations,
        int $totalWarnings,
        int $riskScore,
    ): string {
        $status = match($session->status) {
            'terminated' => "The exam was terminated. Reason: {$session->termination_reason}",
            'submitted'  => 'The student completed the exam normally.',
            default      => 'Exam session status: ' . $session->status,
        };

        return "Risk Score: {$riskScore}/100. {$totalViolations} violation(s) and {$totalWarnings} warning(s) recorded. {$status}";
    }
}
