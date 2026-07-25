<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\CreateExamRequest;
use App\Http\Requests\Instructor\UpdateExamRequest;
use App\Http\Resources\ExamResource;
use App\Http\Resources\ExamSessionResource;
use App\Models\Exam;
use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Services\ExamService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ExamService                $examService,
        private readonly ExamRepositoryInterface   $examRepository,
        private readonly SessionRepositoryInterface $sessionRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $exams = $this->examRepository->getInstructorExams($request->user());

        return $this->success(ExamResource::collection($exams)->response()->getData(true));
    }

    public function store(CreateExamRequest $request): JsonResponse
    {
        $exam = $this->examService->createExam($request->user(), $request->validated());

        return $this->created(new ExamResource($exam), 'Exam created successfully.');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $exam = $this->examRepository->findById($id);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        $exam->load(['questions.choices']);

        return $this->success(new ExamResource($exam));
    }

    public function update(UpdateExamRequest $request, int $id): JsonResponse
    {
        $exam = $this->examRepository->findById($id);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        if ($exam->sessions()->whereIn('status', ['active', 'submitted', 'terminated'])->exists()) {
            return $this->error('Cannot edit an exam that students have already started.', 422);
        }

        $updated = $this->examService->updateExam($exam, $request->validated());

        return $this->success(new ExamResource($updated), 'Exam updated successfully.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $exam = $this->examRepository->findById($id);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        if ($exam->sessions()->whereIn('status', ['active', 'submitted', 'terminated'])->exists()) {
            return $this->error('Cannot delete an exam that students have already started.', 422);
        }

        $this->examService->deleteExam($exam);

        return $this->success(null, 'Exam deleted successfully.');
    }

    public function publish(Request $request, int $id): JsonResponse
    {
        $exam = $this->examRepository->findById($id);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        $exam = $this->examService->publishExam($exam);

        return $this->success(new ExamResource($exam), 'Exam published successfully.');
    }

    public function close(Request $request, int $id): JsonResponse
    {
        $exam = $this->examRepository->findById($id);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        $exam = $this->examService->closeExam($exam);

        return $this->success(new ExamResource($exam), 'Exam closed successfully.');
    }

    public function sessions(Request $request, int $id): JsonResponse
    {
        $exam = $this->examRepository->findById($id);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        $sessions = $this->sessionRepository->getExamSessions(
            $exam->id,
            (int) $request->input('per_page', 15),
            $request->input('search'),
            $request->input('sort'),
        );

        return $this->success(ExamSessionResource::collection($sessions)->response()->getData(true));
    }

    public function sessionDetail(Request $request, int $examId, int $sessionId): JsonResponse
    {
        $exam = $this->examRepository->findById($examId);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        $session = $exam->sessions()
            ->with(['exam.questions', 'student', 'answers.question', 'answers.choice', 'violations', 'warnings.violation', 'aiReport'])
            ->find($sessionId);

        if (!$session) {
            return $this->notFound('Session not found.');
        }

        return $this->success(new ExamSessionResource($session));
    }

    public function stats(Request $request, int $id): JsonResponse
    {
        $exam = $this->examRepository->findById($id);

        if (!$exam || $exam->instructor_id !== $request->user()->id) {
            return $this->notFound('Exam not found.');
        }

        $stats = $this->sessionRepository->getExamStats($exam->id);

        return $this->success($stats, 'Exam statistics retrieved.');
    }
}
