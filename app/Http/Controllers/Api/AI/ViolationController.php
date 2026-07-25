<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\ViolationRequest;
use App\Services\ViolationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ViolationController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ViolationService $violationService) {}

    public function store(ViolationRequest $request): JsonResponse
    {
        $result = $this->violationService->handleViolation($request->validated());

        return $this->success($result, 'Violation processed.');
    }
}
