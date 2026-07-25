<?php

use App\Http\Controllers\Api\AI\ViolationController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Instructor\ExamController as InstructorExamController;
use App\Http\Controllers\Api\Instructor\QuestionController;
use App\Http\Controllers\Api\Instructor\SnapshotController;
use App\Http\Controllers\Api\Student\ExamSessionController;
use Illuminate\Support\Facades\Route;

/*
  Auth Routes
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });
});

/*
 Instructor Routes
*/
Route::prefix('instructor')
    ->middleware(['auth:sanctum', 'role:instructor'])
    ->group(function () {

    // Exams
    Route::get('/exams', [InstructorExamController::class, 'index']);
    Route::post('/exams',          [InstructorExamController::class, 'store']);
    Route::get('/exams/{id}',      [InstructorExamController::class, 'show']);
    Route::put('/exams/{id}',      [InstructorExamController::class, 'update']);
    Route::delete('/exams/{id}',   [InstructorExamController::class, 'destroy']);
    Route::patch('/exams/{id}/publish', [InstructorExamController::class, 'publish']);
    Route::patch('/exams/{id}/close',      [InstructorExamController::class, 'close']);

    // Exam sessions (results)
    Route::get('/exams/{id}/sessions',     [InstructorExamController::class, 'sessions']);
    Route::get('/exams/{examId}/sessions/{sessionId}',  [InstructorExamController::class, 'sessionDetail']);

    // Exam statistics
    Route::get('/exams/{id}/stats',[InstructorExamController::class, 'stats']);

    // Questions
    Route::get('/exams/{examId}/questions',[QuestionController::class, 'index']);
    Route::post('/exams/{examId}/questions',            [QuestionController::class, 'store']);
    Route::put('/exams/{examId}/questions/{questionId}',[QuestionController::class, 'update']);
    Route::delete('/exams/{examId}/questions/{questionId}', [QuestionController::class, 'destroy']);
});

/*
 Student Routes
*/
Route::prefix('student')
    ->middleware(['auth:sanctum', 'role:student'])
    ->group(function () {

    Route::post('/exams/join',  [ExamSessionController::class, 'join']);
    Route::get('/sessions/{id}/questions', [ExamSessionController::class, 'questions']);
    Route::post('/sessions/{id}/answers',        [ExamSessionController::class, 'saveAnswer']);
    Route::post('/sessions/{id}/submit',         [ExamSessionController::class, 'submit']);
    Route::get('/results',  [ExamSessionController::class, 'myResults']);
    Route::get('/results/{id}',     [ExamSessionController::class, 'resultDetail']);
});

/*
 Shared Authenticated Routes (instructor + student)
*/
Route::middleware('auth:sanctum')->group(function () {
    // Violation snapshots — accessible by both instructors and session owners
    Route::get('/violations/{violationId}/snapshot', [SnapshotController::class, 'show'])
        ->name('violations.snapshot');
});

/*
 AI Service Routes (protected by X-AI-Key header)
*/
Route::prefix('ai')
    ->middleware('ai.auth')
    ->group(function () {
    Route::post('/violations', [ViolationController::class, 'store']);
});





