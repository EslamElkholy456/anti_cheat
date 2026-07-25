<?php

namespace App\Jobs;

use App\Models\ExamSession;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAiReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public readonly int $sessionId) {}

    public function handle(ReportService $reportService): void
    {
        $session = ExamSession::find($this->sessionId);

        if (!$session) {
            return;
        }

        $reportService->generateReport($session);
    }
}
