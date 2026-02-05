<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Youth Ministry', 'description' => 'Ministry for young adults aged 18-35'],
            ['name' => 'Women\'s Ministry', 'description' => 'Ministry focused on women\'s spiritual growth'],
            ['name' => 'Men\'s Ministry', 'description' => 'Ministry focused on men\'s spiritual growth'],
            ['name' => 'Children\'s Ministry', 'description' => 'Sunday school and children\'s programs'],
            ['name' => 'Music Ministry', 'description' => 'Choir and worship team'],
            ['name' => 'Ushering', 'description' => 'Welcome and ushering team'],
            ['name' => 'Media', 'description' => 'Audio, video, and social media team'],
            ['name' => 'Protocol', 'description' => 'VIP reception and protocol'],
            ['name' => 'Welfare', 'description' => 'Member welfare and support'],
            ['name' => 'Evangelism', 'description' => 'Outreach and evangelism team'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}