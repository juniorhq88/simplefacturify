<?php

namespace Tests\Feature\Api;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_reply_to_a_thread(): void
    {
        [$owner, $replier] = User::factory(2)->create()->all();

        $thread = Thread::factory()->create(['created_by' => $owner->id]);
        $thread->participants()->attach([$owner->id, $replier->id]);

        $response = $this->actingAs($replier)
            ->postJson("/api/threads/{$thread->id}/messages", [
                'body' => 'Here is my reply.',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', 'Here is my reply.')
            ->assertJsonPath('data.sender.id', $replier->id);

        $this->assertDatabaseHas('messages', [
            'thread_id' => $thread->id,
            'sender_id' => $replier->id,
            'body' => 'Here is my reply.',
        ]);
    }

    public function test_reply_updates_thread_last_message_at(): void
    {
        $user = User::factory()->create();
        $thread = Thread::factory()->create(['created_by' => $user->id, 'last_message_at' => null]);
        $thread->participants()->attach($user->id);

        $this->actingAs($user)
            ->postJson("/api/threads/{$thread->id}/messages", ['body' => 'Update timestamp.']);

        $this->assertNotNull($thread->fresh()->last_message_at);
    }

    public function test_reply_creates_notifications_for_other_participants(): void
    {
        [$sender, $receiver] = User::factory(2)->create()->all();

        $thread = Thread::factory()->create(['created_by' => $sender->id]);
        $thread->participants()->attach([$sender->id, $receiver->id]);

        $this->actingAs($sender)
            ->postJson("/api/threads/{$thread->id}/messages", ['body' => 'Notify others.']);

        $this->assertDatabaseHas('inbox_notifications', [
            'user_id' => $receiver->id,
        ]);

        // Sender should NOT receive a notification for their own message
        $this->assertDatabaseMissing('inbox_notifications', [
            'user_id' => $sender->id,
        ]);
    }

    public function test_non_participant_cannot_reply(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();

        $thread = Thread::factory()->create(['created_by' => $owner->id]);
        $thread->participants()->attach($owner->id);

        $this->actingAs($outsider)
            ->postJson("/api/threads/{$thread->id}/messages", ['body' => 'I should not be here.'])
            ->assertForbidden();
    }

    public function test_reply_requires_body(): void
    {
        $user = User::factory()->create();
        $thread = Thread::factory()->create(['created_by' => $user->id]);
        $thread->participants()->attach($user->id);

        $this->actingAs($user)
            ->postJson("/api/threads/{$thread->id}/messages", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);
    }
}
