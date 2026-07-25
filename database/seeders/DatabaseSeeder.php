<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Fixed demo accounts
        $instructor = User::create([
            'name'              => 'Dr. Ahmed Hassan',
            'email'             => 'instructor@demo.com',
            'password'          => Hash::make('password'),
            'role'              => 'instructor',
            'email_verified_at' => now(),
        ]);

        $student = User::create([
            'name'              => 'Mohamed Ali',
            'email'             => 'student@demo.com',
            'password'          => Hash::make('password'),
            'role'              => 'student',
            'email_verified_at' => now(),
        ]);

        // Additional random users
        User::factory()->instructor()->count(3)->create();
        User::factory()->student()->count(10)->create();

        // Create a sample published exam with questions
        $exam = Exam::create([
            'instructor_id'    => $instructor->id,
            'title'            => 'Introduction to Computer Science',
            'subject'          => 'Computer Science',
            'description'      => 'A sample exam covering basic CS concepts.',
            'duration_minutes' => 60,
            'passing_grade'    => 60,
            'start_at'         => now()->subHour(),
            'end_at'           => now()->addDays(7),
            'exam_code'        => 'CS1001',
            'status'           => 'published',
            'total_questions'  => 0,
            'max_score'        => 0,
        ]);

        $questionsData = [
            [
                'body'    => 'What does CPU stand for?',
                'choices' => [
                    ['body' => 'Central Processing Unit',    'is_correct' => true],
                    ['body' => 'Computer Personal Unit',     'is_correct' => false],
                    ['body' => 'Central Program Utility',    'is_correct' => false],
                    ['body' => 'Core Processing Utility',    'is_correct' => false],
                ],
            ],
            [
                'body'    => 'Which data structure uses LIFO order?',
                'choices' => [
                    ['body' => 'Queue',   'is_correct' => false],
                    ['body' => 'Stack',   'is_correct' => true],
                    ['body' => 'Tree',    'is_correct' => false],
                    ['body' => 'Graph',   'is_correct' => false],
                ],
            ],
            [
                'body'    => 'What is the time complexity of binary search?',
                'choices' => [
                    ['body' => 'O(n)',      'is_correct' => false],
                    ['body' => 'O(n²)',     'is_correct' => false],
                    ['body' => 'O(log n)',  'is_correct' => true],
                    ['body' => 'O(1)',      'is_correct' => false],
                ],
            ],
            [
                'body'    => 'HTML stands for HyperText Markup Language.',
                'type'    => 'true_false',
                'choices' => [
                    ['body' => 'True',  'is_correct' => true],
                    ['body' => 'False', 'is_correct' => false],
                ],
            ],
            [
                'body'    => 'Which language is primarily used for styling web pages?',
                'choices' => [
                    ['body' => 'JavaScript', 'is_correct' => false],
                    ['body' => 'Python',     'is_correct' => false],
                    ['body' => 'CSS',        'is_correct' => true],
                    ['body' => 'PHP',        'is_correct' => false],
                ],
            ],
        ];

        $totalPoints = 0;
        foreach ($questionsData as $order => $qData) {
            $question = Question::create([
                'exam_id' => $exam->id,
                'body'    => $qData['body'],
                'type'    => $qData['type'] ?? 'mcq',
                'points'  => 1,
                'order'   => $order + 1,
            ]);

            foreach ($qData['choices'] as $choiceData) {
                Choice::create([
                    'question_id' => $question->id,
                    'body'        => $choiceData['body'],
                    'is_correct'  => $choiceData['is_correct'],
                ]);
            }

            $totalPoints++;
        }

        $exam->update([
            'total_questions' => count($questionsData),
            'max_score'       => $totalPoints,
        ]);
    }
}
