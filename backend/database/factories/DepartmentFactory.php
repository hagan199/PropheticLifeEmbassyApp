<?php


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        $departments = [
            'Youth Ministry', 'Women\'s Ministry', 'Men\'s Ministry', 
            'Children\'s Ministry', 'Music Ministry', 'Ushering', 
            'Media', 'Protocol', 'Welfare', 'Evangelism'
        ];

        return [
            'name' => fake()->unique()->randomElement($departments),
            'description' => fake()->paragraph(),
            'leader_id' => null,
            'member_count' => fake()->numberBetween(5, 50),
        ];
    }
}