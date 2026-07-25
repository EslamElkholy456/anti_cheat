<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+7 days');
        $end   = fake()->dateTimeBetween($start, '+14 days');

        return [
            'instructor_id'    => User::factory()->instructor(),
            'title'            => fake()->sentence(4),
            'subject'          => fake()->randomElement(['Mathematics', 'Physics', 'Chemistry', 'Biology', 'Computer Science', 'English']),
            'description'      => fake()->paragraph(),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90, 120]),
            'passing_grade'    => fake()->randomElement([50, 60, 70]),
            'start_at'         => $start,
            'end_at'           => $end,
            'exam_code'        => strtoupper(Str::random(6)),
            'status'           => 'published',
            'total_questions'  => 0,
            'max_score'        => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn ($attributes) => ['status' => 'draft']);
    }

    public function published(): static
    {
        return $this->state(fn ($attributes) => ['status' => 'published']);
    }
}
