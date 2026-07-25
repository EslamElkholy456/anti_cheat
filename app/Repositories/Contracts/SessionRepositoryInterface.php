<?php

namespace App\Repositories\Contracts;

use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface SessionRepositoryInterface
{
    public function findById(int $id): ?ExamSession;
    public function findByExamAndStudent(int $examId, int $studentId): ?ExamSession;
    public function getStudentSessions(User $student, int $perPage = 15): LengthAwarePaginator;
    public function getExamSessions(int $examId, int $perPage = 15, ?string $search = null, ?string $sort = null): LengthAwarePaginator;
    public function getExamStats(int $examId): array;
    public function create(array $data): ExamSession;
    public function update(ExamSession $session, array $data): ExamSession;
}
