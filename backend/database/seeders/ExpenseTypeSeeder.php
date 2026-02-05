<?php


namespace Database\Seeders;

use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet bills'],
            ['name' => 'Maintenance', 'description' => 'Building and equipment maintenance'],
            ['name' => 'Supplies', 'description' => 'Office and church supplies'],
            ['name' => 'Staff Salary', 'description' => 'Staff wages and allowances'],
            ['name' => 'Transport', 'description' => 'Transportation and fuel costs'],
            ['name' => 'Events', 'description' => 'Event organization expenses'],
            ['name' => 'Equipment', 'description' => 'Equipment purchases'],
            ['name' => 'Welfare', 'description' => 'Member welfare support'],
            ['name' => 'Miscellaneous', 'description' => 'Other expenses'],
        ];

        foreach ($types as $type) {
            ExpenseType::create($type);
        }
    }
}