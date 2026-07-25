<?php

namespace App\Services;

use App\Enums\ViolationType;
use App\Models\ExamSession;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ViolationService
{
    public function __construct(
        private readonly WarningService $warningService,
        private readonly SessionService $sessionService,
    ) {}

    public function handleViolation(array $data): array
    {
        $session       = ExamSession::findOrFail($data['session_id']);
        $violationType = ViolationType::from($data['violation_type']);

        if (!$session->isActive()) {
            return [
                'action'          => 'session_closed',
                'warning_count'   => $session->warning_count,
                'warning_message' => null,
            ];
        }

        return DB::transaction(function () use ($data, $session, $violationType) {
            $snapshotPath = $this->storeSnapshot(
                $data['snapshot_b64'] ?? null,
                $session->id,
            );

            $violation = Violation::create([
                'session_id'       => $session->id,
                'student_id'       => $data['student_id'],
                'violation_type'   => $violationType->value,
                'confidence'       => $data['confidence'],
                'duration_seconds' => $data['duration_seconds'],
                'snapshot_path'    => $snapshotPath,
                'detected_at'      => $data['detected_at'],
                'created_at'       => now(),
            ]);

            // Phone detected = immediate termination (no debounce needed — AI already applies 2s threshold)
            if ($violationType === ViolationType::PhoneDetected) {
                $this->sessionService->terminateExam(
                    $session,
                    'Mobile phone detected during exam.',
                );

                return [
                    'action'          => 'terminate',
                    'warning_count'   => $session->warning_count,
                    'warning_message' => 'Exam terminated: mobile phone detected.',
                ];
            }

            return $this->warningService->processWarning($session, $violation);
        });
    }

    private function storeSnapshot(?string $base64, int $sessionId): ?string
    {
        if (!$base64) {
            return null;
        }

        try {
            $imageData = base64_decode($base64);
            $filename  = "violations/{$sessionId}/" . Str::uuid() . '.jpg';
            Storage::disk('local')->put($filename, $imageData);
            return $filename;
        } catch (\Throwable) {
            return null;
        }
    }
}
