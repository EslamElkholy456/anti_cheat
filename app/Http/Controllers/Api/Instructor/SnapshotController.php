<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Violation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SnapshotController extends Controller
{
    use ApiResponse;

    public function show(Request $request, int $violationId): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        $violation = Violation::with('session.exam')->findOrFail($violationId);
        $user = $request->user();

        // Allow access if user is the instructor of the exam or the student who owns the session
        $isInstructor = $violation->session->exam->instructor_id === $user->id;
        $isStudent    = $violation->session->student_id === $user->id;

        if (!$isInstructor && !$isStudent) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        if (!$violation->snapshot_path || !Storage::disk('local')->exists($violation->snapshot_path)) {
            return response()->json(['success' => false, 'message' => 'Snapshot not found.'], 404);
        }

        $file = Storage::disk('local')->get($violation->snapshot_path);

        return response($file, 200)->header('Content-Type', 'image/jpeg');
    }
}
