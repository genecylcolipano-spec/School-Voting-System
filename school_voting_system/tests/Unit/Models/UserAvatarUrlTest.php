<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserAvatarUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_avatar_path(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'avatar_path'));
    }

    public function test_avatar_url_uses_the_public_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/face.jpg', 'fake');

        $user = User::factory()->create([
            'avatar_path' => 'avatars/face.jpg',
        ]);

        $url = $user->avatarUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('avatars/face.jpg', $url);
        $this->assertSame(
            Storage::disk('public')->url('avatars/face.jpg').'?v='.$user->updated_at->getTimestamp(),
            $url,
        );
    }

    public function test_avatar_url_is_null_without_a_path(): void
    {
        $user = User::factory()->create(['avatar_path' => null]);

        $this->assertNull($user->avatarUrl());
    }
}
