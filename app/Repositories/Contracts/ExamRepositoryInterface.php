<?php

namespace App\Repositories\Contracts;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface ExamRepositoryInterface
{
    public function findById(int $id): ?Exam;
    public function findByCode(string $code): ?Exam;
    public function getInstructorExams(User $instructor, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Exam;
    public function update(Exam $exam, array $data): Exam;
    public function delete(Exam $exam): bool;
}
