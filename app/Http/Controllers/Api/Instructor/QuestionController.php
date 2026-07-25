<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\CreateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Services\ExamService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ExamService              $examService,
        private readonly ExamRepositoryInterface  $examRepository,
    ) {}

    public function index(Request $request, int $examId): JsonResponse
    {
        $exam = $this->examRepository->findById($examId);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        $questions = $exam->questions()->with('choices')->get();

        return $this->success(QuestionResource::collection($questions));
    }

    public function store(CreateQuestionRequest $request, int $examId): JsonResponse
    {
        $exam = $this->examRepository->findById($examId);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        if ($exam->sessions()->whereIn('status', ['active', 'submitted', 'terminated'])->exists()) {
            return $this->error('Cannot modify questions after students have started the exam.', 422);
        }

        $question = $this->examService->addQuestion($exam, $request->validated());

        return $this->created(new QuestionResource($question), 'Question added successfully.');
    }

    public function update(Request $request, int $examId, int $questionId): JsonResponse
    {
        $exam = $this->examRepository->findById($examId);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        if ($exam->sessions()->whereIn('status', ['active', 'submitted', 'terminated'])->exists()) {
            return $this->error('Cannot modify questions after students have started the exam.', 422);
        }

        $question = $exam->questions()->find($questionId);

        if (!$question) {
            return $this->notFound('Question not found.');
        }

        $question = $this->examService->updateQuestion($question, $request->all());

        return $this->success(new QuestionResource($question), 'Question updated successfully.');
    }

    public function destroy(Request $request, int $examId, int $questionId): JsonResponse
    {
        $exam = $this->examRepository->findById($examId);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        if ($exam->sessions()->whereIn('status', ['active', 'submitted', 'terminated'])->exists()) {
            return $this->error('Cannot modify questions after students have started the exam.', 422);
        }

        $question = $exam->questions()->find($questionId);

        if (!$question) {
            return $this->notFound('Question not found.');
        }

        $this->examService->deleteQuestion($question);

        return $this->success(null, 'Question deleted successfully.');
    }
}
