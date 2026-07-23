<?php

namespace Database\Factories;

use App\Domains\Test\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Test>
 */
class TestFactory extends Factory
{
    protected $model = Test::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['active', 'draft', 'archived']);

        return [
            'name' => fake()->unique()->sentence(3),
            'email' => fake()->safeEmail(),
            'description' => fake()->paragraph(),
            'status' => $status,
            'category' => fake()->randomElement(['general', 'billing', 'support', 'product']),
            'priority' => fake()->numberBetween(1, 5),
            'score' => fake()->numberBetween(0, 100),
            'is_featured' => fake()->boolean(30),
            'is_public' => fake()->boolean(70),
            'tags' => fake()->randomElements(['alpha', 'beta', 'urgent', 'internal', 'demo'], fake()->numberBetween(1, 3)),
            'metadata' => [
                'source' => fake()->randomElement(['web', 'api', 'import']),
                'owner' => fake()->userName(),
                'flags' => [
                    'reviewed' => fake()->boolean(),
                    'synced' => fake()->boolean(),
                ],
            ],
            'published_at' => $status === 'active' ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
