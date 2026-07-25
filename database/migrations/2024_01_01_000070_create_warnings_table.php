<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('exam_sessions')->cascadeOnDelete();
            $table->foreignId('violation_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('warning_number');
            $table->string('message');
            $table->timestamp('created_at')->useCurrent();

            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warnings');
    }
};
