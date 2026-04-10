<?php

namespace Tests\Feature\Api;

use App\Models\InboxNotification;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeNotification(User $user): InboxNotification
    {
        $thread = Thread::factory()->create(['created_by' => $user->id]);
        $message = Message::factory()->create(['thread_id' => $thread->id, 'sender_id' => $user->id]);

        return InboxNotification::factory()->create([
            'user_id' => $user->id,
            'thread_id' => $thread->id,
            'message_id' => $message->id,
            'read_at' => null,
        ]);
    }

    public function test_user_can_list_their_unread_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->makeNotification($user);
        $this->makeNotification($user);
        $this->makeNotification($other); // Should not appear

        $this->actingAs($user)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_read_notifications_are_excluded(): void
    {
        $user = User::factory()->create();
        $notification = $this->makeNotification($user);
        $notification->update(['read_at' => now()]);

        $this->actingAs($user)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = $this->makeNotification($user);

        $this->actingAs($user)
            ->patchJson("/api/notifications/{$notification->id}/read")
            ->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $notification = $this->makeNotification($other);

        $this->actingAs($user)
            ->patchJson("/api/notifications/{$notification->id}/read")
            ->assertForbidden();
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();

        $this->makeNotification($user);
        $this->makeNotification($user);

        $this->actingAs($user)
            ->postJson('/api/notifications/read-all')
            ->assertOk();

        $this->assertEquals(
            0,
            InboxNotification::where('user_id', $user->id)->unread()->count()
        );
    }
}
