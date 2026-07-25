<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedTinyInteger('passing_grade')->default(50);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('exam_code', 8)->unique();
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedSmallInteger('max_score')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('instructor_id');
            $table->index('status');
            $table->index('exam_code');
            $table->index('start_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
