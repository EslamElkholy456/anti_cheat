<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('exam_sessions')->cascadeOnDelete();
            $table->unsignedSmallInteger('total_violations')->default(0);
            $table->unsignedSmallInteger('total_warnings')->default(0);
            $table->unsignedSmallInteger('phone_detected_count')->default(0);
            $table->unsignedSmallInteger('no_face_count')->default(0);
            $table->unsignedSmallInteger('multiple_persons_count')->default(0);
            $table->unsignedSmallInteger('gaze_away_count')->default(0);
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->text('summary')->nullable();
            $table->timestamp('generated_at')->useCurrent();

            $table->unique('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_reports');
    }
};
