<?php

namespace App\Services;

use App\Enums\SessionStatus;
use App\Exceptions\ExamException;
use App\Jobs\GenerateAiReport;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\User;
use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\Contracts\SessionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SessionService
{
    public function __construct(
        private readonly ExamRepositoryInterface   $examRepository,
        private readonly SessionRepositoryInterface $sessionRepository,
    ) {}

    public function joinExam(User $student, string $examCode, string $universityStudentId): ExamSession
    {
        $exam = $this->examRepository->findByCode($examCode);

        if (!$exam) {
            throw ExamException::invalidCode();
        }

        if (!$exam->isAvailable()) {
            throw ExamException::notAvailable();
        }

        // Check if this university_student_id already exists for this exam
        $existingByStudentId = ExamSession::where('exam_id', $exam->id)
            ->where('university_student_id', $universityStudentId)
            ->first();

        if ($existingByStudentId) {
            throw ExamException::duplicateStudentId();
        }

        // Check existing session for this user
        $existing = $this->sessionRepository->findByExamAndStudent($exam->id, $student->id);

        if ($existing) {
            if (in_array($existing->status, [SessionStatus::Submitted->value, SessionStatus::Terminated->value])) {
                throw ExamException::sessionAlreadySubmitted();
            }
            return $existing->load(['exam.questions.choices']);
        }

        return DB::transaction(function () use ($exam, $student, $universityStudentId) {
            $session = $this->sessionRepository->create([
                'exam_id'               => $exam->id,
                'student_id'            => $student->id,
                'university_student_id' => $universityStudentId,
                'status'                => SessionStatus::Active->value,
                'started_at'            => now(),
                'ip_address'            => request()->ip(),
                'device_info'           => request()->userAgent(),
            ]);

            return $session->load(['exam.questions.choices']);
        });
    }

    public function saveAnswer(ExamSession $session, int $questionId, int $choiceId): Answer
    {
        $this->assertSessionActive($session);

        $question = Question::where('exam_id', $session->exam_id)->findOrFail($questionId);
        $choice   = $question->choices()->findOrFail($choiceId);

        return Answer::updateOrCreate(
            ['session_id' => $session->id, 'question_id' => $question->id],
            ['choice_id'  => $choice->id],
        );
    }

    public function submitExam(ExamSession $session, bool $autoSubmitted = false): ExamSession
    {
        $this->assertSessionActive($session);

        return DB::transaction(function () use ($session, $autoSubmitted) {
            $result = $this->calculateResult($session);

            $this->sessionRepository->update($session, [
                'status'         => SessionStatus::Submitted->value,
                'submitted_at'   => now(),
                'score'          => $result['score'],
                'passed'         => $result['passed'],
                'auto_submitted' => $autoSubmitted,
            ]);

            GenerateAiReport::dispatch($session->id)->afterCommit();

            return $session->fresh(['exam', 'answers']);
        });
    }

    public function terminateExam(ExamSession $session, string $reason): ExamSession
    {
        if ($session->status === SessionStatus::Submitted->value ||
            $session->status === SessionStatus::Terminated->value) {
            return $session;
        }

        return DB::transaction(function () use ($session, $reason) {
            $result = $this->calculateResult($session);

            $this->sessionRepository->update($session, [
                'status'               => SessionStatus::Terminated->value,
                'submitted_at'         => now(),
                'score'                => $result['score'],
                'passed'               => false,
                'termination_reason'   => $reason,
                'auto_submitted'       => false,
            ]);

            GenerateAiReport::dispatch($session->id)->afterCommit();

            return $session->fresh(['exam', 'answers']);
        });
    }

    private function calculateResult(ExamSession $session): array
    {
        $session->load(['exam.questions.choices', 'answers']);
        $exam = $session->exam;

        $totalPoints  = 0;
        $earnedPoints = 0;

        foreach ($exam->questions as $question) {
            $totalPoints += $question->points;
            $answer = $session->answers->firstWhere('question_id', $question->id);

            if (!$answer) {
                continue;
            }

            $correctChoiceId = $question->choices->firstWhere('is_correct', true)?->id;

            if ($answer->choice_id === $correctChoiceId) {
                $earnedPoints += $question->points;
                $answer->update(['is_correct' => true]);
            } else {
                $answer->update(['is_correct' => false]);
            }
        }

        $score  = $earnedPoints;
        $passed = $totalPoints > 0 && ($earnedPoints / $totalPoints) * 100 >= $exam->passing_grade;

        return ['score' => $score, 'passed' => $passed];
    }

    private function assertSessionActive(ExamSession $session): void
    {
        if ($session->status === SessionStatus::Submitted->value) {
            throw ExamException::sessionAlreadySubmitted();
        }

        if ($session->status === SessionStatus::Terminated->value) {
            throw ExamException::sessionAlreadySubmitted();
        }

        if ($session->status !== SessionStatus::Active->value) {
            throw ExamException::sessionNotActive();
        }
    }
}
