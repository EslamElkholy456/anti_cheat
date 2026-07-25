<?php

namespace App\Services;

use App\Models\Choice;
use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use App\Repositories\Contracts\ExamRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExamService
{
    public function __construct(
        private readonly ExamRepositoryInterface $examRepository,
    ) {}

    public function createExam(User $instructor, array $data): Exam
    {
        $data['instructor_id'] = $instructor->id;
        $data['exam_code']     = $this->generateUniqueCode();

        return $this->examRepository->create($data);
    }

    public function updateExam(Exam $exam, array $data): Exam
    {
        return $this->examRepository->update($exam, $data);
    }

    public function publishExam(Exam $exam): Exam
    {
        if ($exam->total_questions === 0) {
            throw new \RuntimeException('Cannot publish an exam with no questions.');
        }

        return $this->examRepository->update($exam, ['status' => 'published']);
    }

    public function closeExam(Exam $exam): Exam
    {
        return $this->examRepository->update($exam, ['status' => 'closed']);
    }

    public function deleteExam(Exam $exam): bool
    {
        return $this->examRepository->delete($exam);
    }

    public function addQuestion(Exam $exam, array $data): Question
    {
        return DB::transaction(function () use ($exam, $data) {
            $order = $exam->questions()->max('order') + 1;

            $question = Question::create([
                'exam_id' => $exam->id,
                'body'    => $data['body'],
                'type'    => $data['type'],
                'points'  => $data['points'],
                'order'   => $order,
            ]);

            foreach ($data['choices'] as $choiceData) {
                Choice::create([
                    'question_id' => $question->id,
                    'body'        => $choiceData['body'],
                    'is_correct'  => $choiceData['is_correct'],
                ]);
            }

            $totalPoints = $exam->questions()->sum('points');
            $exam->update([
                'total_questions' => $exam->questions()->count(),
                'max_score'       => $totalPoints,
            ]);

            return $question->load('choices');
        });
    }

    public function updateQuestion(Question $question, array $data): Question
    {
        return DB::transaction(function () use ($question, $data) {
            $question->update([
                'body'   => $data['body'] ?? $question->body,
                'points' => $data['points'] ?? $question->points,
            ]);

            if (isset($data['choices'])) {
                $question->choices()->delete();
                foreach ($data['choices'] as $choiceData) {
                    Choice::create([
                        'question_id' => $question->id,
                        'body'        => $choiceData['body'],
                        'is_correct'  => $choiceData['is_correct'],
                    ]);
                }
            }

            $exam = $question->exam;
            $exam->update([
                'total_questions' => $exam->questions()->count(),
                'max_score'       => $exam->questions()->sum('points'),
            ]);

            return $question->load('choices');
        });
    }

    public function deleteQuestion(Question $question): bool
    {
        return DB::transaction(function () use ($question) {
            $exam = $question->exam;
            $question->delete();

            $exam->update([
                'total_questions' => $exam->questions()->count(),
                'max_score'       => $exam->questions()->sum('points'),
            ]);

            return true;
        });
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (Exam::where('exam_code', $code)->exists());

        return $code;
    }
}
