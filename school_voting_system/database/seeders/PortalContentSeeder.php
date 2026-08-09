<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class PortalContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->where('role', UserRole::Admin)->first()
            ?? User::query()->where('role', UserRole::SuperAdmin)->first()
            ?? User::query()->first();

        if (! $author) {
            return;
        }

        $announcements = [
            [
                'title' => 'Audience voting closes tomorrow at 8 PM',
                'slug' => 'audience-voting-closes-tomorrow',
                'summary' => 'Make sure your votes are submitted before the deadline to support your favorite performers.',
                'body' => 'Voting for Rosemont Idol audience choice closes tomorrow at 8:00 PM. Log in and cast your votes before the window closes.',
            ],
            [
                'title' => 'New fundraising campaign launched',
                'slug' => 'new-fundraising-campaign',
                'summary' => 'Join the campaign and help make this year’s performance unforgettable.',
                'body' => 'A new fundraiser is now open for Rosemont Idol set design. Visit the Fundraising section to contribute.',
            ],
            [
                'title' => 'Student Council election schedule posted',
                'slug' => 'student-council-election-schedule',
                'summary' => 'Review the official election timeline and voting categories.',
                'body' => 'The student council election schedule is now available. Check the Voting section for open ballots.',
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::query()->firstOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'is_published' => true,
                    'published_at' => now()->subDays(rand(1, 5)),
                    'created_by' => $author->id,
                ],
            );
        }

        $notifications = [
            ['title' => 'New event announced', 'message' => 'Spring Arts Showcase has been added to the events calendar.', 'type' => 'event'],
            ['title' => 'Voting reminder', 'message' => 'Rosemont Idol audience voting ends soon.', 'type' => 'voting'],
            ['title' => 'Results published', 'message' => 'Debate Tournament results are now available.', 'type' => 'results'],
            ['title' => 'Fundraising update', 'message' => 'Library renovation drive is now over 60% funded.', 'type' => 'fundraising'],
        ];

        foreach ($notifications as $data) {
            PortalNotification::query()->firstOrCreate(
                ['title' => $data['title'], 'message' => $data['message']],
                [
                    ...$data,
                    'user_id' => null,
                    'created_by' => $author->id,
                ],
            );
        }
    }
}
