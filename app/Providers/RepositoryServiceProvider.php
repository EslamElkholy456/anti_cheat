<?php

namespace App\Providers;

use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Repositories\ExamRepository;
use App\Repositories\SessionRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ExamRepositoryInterface::class, ExamRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
    }
}
