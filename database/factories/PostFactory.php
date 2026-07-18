<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 100000),
            'body' => fake()->paragraphs(3, true),
            'published' => fake()->boolean(80),
            'user_id' => User::factory(),
            'category_id' => null,
        ];
    }
}
