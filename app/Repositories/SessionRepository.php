<?php

namespace App\Repositories;

use App\Models\ExamSession;
use App\Models\User;
use App\Repositories\Contracts\SessionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SessionRepository implements SessionRepositoryInterface
{
    public function findById(int $id): ?ExamSession
    {
        return ExamSession::find($id);
    }

    public function findByExamAndStudent(int $examId, int $studentId): ?ExamSession
    {
        return ExamSession::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->first();
    }

    public function getStudentSessions(User $student, int $perPage = 15): LengthAwarePaginator
    {
        return ExamSession::where('student_id', $student->id)
            ->with(['exam:id,title,subject,duration_minutes,passing_grade,start_at,end_at,total_questions,max_score'])
            ->latest()
            ->paginate($perPage);
    }

    public function getExamSessions(int $examId, int $perPage = 15, ?string $search = null, ?string $sort = null): LengthAwarePaginator
    {
        $query = ExamSession::where('exam_id', $examId)
            ->with(['student:id,name,email,role,created_at', 'aiReport'])
            ->withCount('violations as violations_count');

        // Search by student name or university student ID
        if ($search && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('university_student_id', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        if ($sort === 'score_asc') {
            $query->orderBy('score', 'asc');
        } elseif ($sort === 'score_desc') {
            $query->orderBy('score', 'desc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy(
                ExamSession::select('name')
                    ->from('users')
                    ->whereColumn('users.id', 'exam_sessions.student_id')
                    ->limit(1),
                'desc'
            );
        } elseif ($sort === 'risk_desc') {
            $query->orderBy(
                ExamSession::select('risk_score')
                    ->from('ai_reports')
                    ->whereColumn('ai_reports.session_id', 'exam_sessions.id')
                    ->limit(1),
                'desc'
            );
        } elseif ($sort === 'violations_desc') {
            $query->orderBy(
                ExamSession::selectRaw('COUNT(*)')
                    ->from('violations')
                    ->whereColumn('violations.session_id', 'exam_sessions.id'),
                'desc'
            );
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    public function getExamStats(int $examId): array
    {
        $stats = ExamSession::where('exam_id', $examId)
            ->selectRaw("
                COUNT(*) as total_students,
                SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_count,
                SUM(CASE WHEN passed = 0 AND status IN ('submitted', 'terminated') THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN auto_submitted = 1 THEN 1 ELSE 0 END) as auto_submitted_count,
                SUM(CASE WHEN status = 'terminated' THEN 1 ELSE 0 END) as terminated_count,
                AVG(score) as avg_score,
                MAX(score) as max_score,
                MIN(score) as min_score
            ")
            ->whereIn('status', ['submitted', 'terminated'])
            ->first();

        $avgRisk = DB::table('ai_reports')
            ->join('exam_sessions', 'ai_reports.session_id', '=', 'exam_sessions.id')
            ->where('exam_sessions.exam_id', $examId)
            ->avg('ai_reports.risk_score');

        return [
            'total_students'       => (int) ($stats->total_students ?? 0),
            'passed'               => (int) ($stats->passed_count ?? 0),
            'failed'               => (int) ($stats->failed_count ?? 0),
            'auto_submitted'       => (int) ($stats->auto_submitted_count ?? 0),
            'ai_terminated'        => (int) ($stats->terminated_count ?? 0),
            'avg_score'            => round((float) ($stats->avg_score ?? 0), 1),
            'highest_score'        => round((float) ($stats->max_score ?? 0), 1),
            'lowest_score'         => round((float) ($stats->min_score ?? 0), 1),
            'avg_risk_score'       => round((float) ($avgRisk ?? 0), 1),
        ];
    }

    public function create(array $data): ExamSession
    {
        return ExamSession::create($data);
    }

    public function update(ExamSession $session, array $data): ExamSession
    {
        $session->update($data);
        return $session->fresh();
    }
}
