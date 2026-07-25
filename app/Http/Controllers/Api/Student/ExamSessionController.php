<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\JoinExamRequest;
use App\Http\Requests\Student\SaveAnswerRequest;
use App\Http\Requests\Student\SubmitExamRequest;
use App\Http\Resources\ExamSessionResource;
use App\Http\Resources\QuestionResource;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Services\SessionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamSessionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SessionService             $sessionService,
        private readonly SessionRepositoryInterface $sessionRepository,
    ) {}

    public function join(JoinExamRequest $request): JsonResponse
    {
        $session = $this->sessionService->joinExam(
            $request->user(),
            $request->input('exam_code'),
            $request->input('university_student_id'),
        );

        return $this->created(new ExamSessionResource($session), 'Exam joined successfully.');
    }

    public function questions(Request $request, int $sessionId): JsonResponse
    {
        $session = $this->sessionRepository->findById($sessionId);

        if (!$session || $session->student_id !== $request->user()->id) {
            return $this->notFound('Session not found.');
        }

        $questions = $session->exam->questions()
            ->with('choices')
            ->orderBy('order')
            ->get();

        return $this->success([
            'session_id'       => $session->id,
            'exam_title'       => $session->exam->title,
            'duration_minutes' => $session->exam->duration_minutes,
            'started_at'       => $session->started_at->toISOString(),
            'questions'        => QuestionResource::collection($questions),
        ]);
    }

    public function saveAnswer(SaveAnswerRequest $request, int $sessionId): JsonResponse
    {
        $session = $this->sessionRepository->findById($sessionId);

        if (!$session || $session->student_id !== $request->user()->id) {
            return $this->notFound('Session not found.');
        }

        $answer = $this->sessionService->saveAnswer(
            $session,
            $request->input('question_id'),
            $request->input('choice_id'),
        );

        return $this->success([
            'question_id' => $answer->question_id,
            'choice_id'   => $answer->choice_id,
        ], 'Answer saved.');
    }

    public function submit(SubmitExamRequest $request, int $sessionId): JsonResponse
    {
        $session = $this->sessionRepository->findById($sessionId);

        if (!$session || $session->student_id !== $request->user()->id) {
            return $this->notFound('Session not found.');
        }

        $autoSubmitted = (bool) $request->input('auto_submitted', false);
        $session = $this->sessionService->submitExam($session, $autoSubmitted);

        return $this->success(new ExamSessionResource($session->load('exam')), 'Exam submitted successfully.');
    }

    public function myResults(Request $request): JsonResponse
    {
        $sessions = $this->sessionRepository->getStudentSessions($request->user());

        return $this->success(ExamSessionResource::collection($sessions)->response()->getData(true));
    }

    public function resultDetail(Request $request, int $sessionId): JsonResponse
    {
        $session = $this->sessionRepository->findById($sessionId);

        if (!$session || $session->student_id !== $request->user()->id) {
            return $this->notFound('Result not found.');
        }

        $session->load([
            'exam.questions.choices',
            'answers.question',
            'answers.choice',
            'violations',
            'warnings.violation',
            'aiReport',
        ]);

        return $this->success(new ExamSessionResource($session));
    }
}
