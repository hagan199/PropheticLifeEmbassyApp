<?php


namespace Database\Seeders;

use App\Models\Broadcast;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class BroadcastSeeder extends Seeder
{
    public function run(): void
    {
        $prUser = User::where('role', 'pr_follow_up')->first();
        $pastor = User::where('role', 'pastor')->first();
        $departments = Department::all();

        // Sent broadcasts
        $messages = [
            'Reminder: Sunday service starts at 9:00 AM. See you there!',
            'Prayer meeting this Wednesday at 6:00 PM. Don\'t miss it!',
            'Thank you for your faithfulness. God bless you!',
            'Special announcement: Church picnic next Saturday!',
            'Midweek service reminder. Join us for a powerful time of worship.',
        ];

        foreach ($messages as $index => $message) {
            Broadcast::create([
                'recipient_group' => 'all_members',
                'channel' => $index % 2 === 0 ? 'whatsapp' : 'sms',
                'message' => $message,
                'total_recipients' => rand(80, 150),
                'delivered_count' => rand(70, 145),
                'failed_count' => rand(2, 10),
                'delivery_rate' => rand(90, 99),
                'status' => 'sent',
                'sent_at' => now()->subDays($index * 7),
                'sender_id' => $index % 2 === 0 ? $prUser->id : $pastor->id,
            ]);
        }

        // Department-specific broadcasts
        foreach ($departments->take(3) as $department) {
            Broadcast::create([
                'recipient_group' => 'department',
                'department_id' => $department->id,
                'channel' => 'whatsapp',
                'message' => "Meeting for {$department->name} this Saturday at 2 PM.",
                'total_recipients' => rand(10, 30),
                'delivered_count' => rand(8, 28),
                'failed_count' => rand(0, 3),
                'delivery_rate' => rand(85, 100),
                'status' => 'sent',
                'sent_at' => now()->subDays(rand(1, 14)),
                'sender_id' => $prUser->id,
            ]);
        }

        // Scheduled broadcast
        Broadcast::create([
            'recipient_group' => 'all_members',
            'channel' => 'whatsapp',
            'message' => 'Upcoming event reminder: Annual thanksgiving service next Sunday!',
            'total_recipients' => 0,
            'delivered_count' => 0,
            'failed_count' => 0,
            'delivery_rate' => 0,
            'status' => 'scheduled',
            'scheduled_for' => now()->addDays(3),
            'sender_id' => $pastor->id,
        ]);
    }
}