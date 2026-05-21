<?php

namespace Modules\Loan\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Loan\App\Models\Loan;
use Modules\User\App\Models\User;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'interest' => fake()->randomFloat(2, 1, 25),
            'term' => fake()->numberBetween(6, 120),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function paid(): static
    {
        return $this->state(['status' => 'paid']);
    }

    public function rejected(): static
    {
        return $this->state(['status' => 'rejected']);
    }

    public function forUser(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }
}
