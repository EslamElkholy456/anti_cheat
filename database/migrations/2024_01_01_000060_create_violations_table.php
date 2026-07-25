<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('exam_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('violation_type', [
                'no_face',
                'multiple_persons',
                'look_left',
                'look_right',
                'look_up',
                'look_down',
                'phone_detected',
            ]);
            $table->decimal('confidence', 5, 4);
            $table->unsignedSmallInteger('duration_seconds')->default(0);
            $table->string('snapshot_path')->nullable();
            $table->timestamp('detected_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index('session_id');
            $table->index('student_id');
            $table->index('violation_type');
            $table->index('detected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
