<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\User;
use App\Repositories\Contracts\ExamRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ExamRepository implements ExamRepositoryInterface
{
    public function findById(int $id): ?Exam
    {
        return Exam::find($id);
    }

    public function findByCode(string $code): ?Exam
    {
        return Exam::where('exam_code', strtoupper($code))->first();
    }

    public function getInstructorExams(User $instructor, int $perPage = 15): LengthAwarePaginator
    {
        return Exam::where('instructor_id', $instructor->id)
            ->withCount('questions')
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Exam
    {
        return Exam::create($data);
    }

    public function update(Exam $exam, array $data): Exam
    {
        $exam->update($data);
        return $exam->fresh();
    }

    public function delete(Exam $exam): bool
    {
        return $exam->delete();
    }
}
