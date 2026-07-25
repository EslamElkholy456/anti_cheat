<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->string('university_student_id', 50)->after('student_id');
            $table->boolean('auto_submitted')->default(false)->after('submitted_at');

            $table->unique(['exam_id', 'university_student_id'], 'exam_student_unique');
        });
    }

    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropUnique('exam_student_unique');
            $table->dropColumn('auto_submitted');
            $table->dropColumn('university_student_id');
        });
    }
};
