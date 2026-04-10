<?php

namespace Tests\Feature\Api;

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThreadTest extends TestCase
{
    use RefreshDatabase;

    // ─── index ───────────────────────────────────────────────────────────────

    public function test_user_can_list_their_threads(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $myThread = Thread::factory()->create([
            'created_by' => $user->id,
            'last_message_at' => now()->subDay(),
        ]);
        $myThread->participants()->attach($user->id);

        $otherThread = Thread::factory()->create([
            'created_by' => $other->id,
            'last_message_at' => now(),
        ]);
        $otherThread->participants()->attach($other->id);

        $response = $this->actingAs($user)->getJson('/api/threads');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $myThread->id);
    }

    public function test_threads_can_be_filtered_by_search_term(): void
    {
        $user = User::factory()->create();

        $match = Thread::factory()->create([
            'subject' => 'Invoice question',
            'created_by' => $user->id,
            'last_message_at' => now()->subDay(),
        ]);
        $match->participants()->attach($user->id);

        $noMatch = Thread::factory()->create([
            'subject' => 'Unrelated topic',
            'created_by' => $user->id,
            'last_message_at' => now(),
        ]);
        $noMatch->participants()->attach($user->id);

        $this->actingAs($user)
            ->getJson('/api/threads?search=invoice')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $match->id);
    }

    public function test_threads_list_is_paginated(): void
    {
        $user = User::factory()->create();

        Thread::factory(20)->create(['created_by' => $user->id])->each(function ($thread) use ($user) {
            $thread->participants()->attach($user->id);
        });

        $this->actingAs($user)
            ->getJson('/api/threads?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    // ─── store ───────────────────────────────────────────────────────────────

    public function test_user_can_create_a_thread_with_first_message(): void
    {
        $user = User::factory()->create();
        $participant = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/threads', [
            'subject' => 'Hello there',
            'body' => 'This is the first message.',
            'participant_ids' => [$participant->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.subject', 'Hello there')
            ->assertJsonCount(1, 'data.messages');

        $this->assertDatabaseHas('threads', ['subject' => 'Hello there']);
        $this->assertDatabaseHas('messages', ['body' => 'This is the first message.']);
        $this->assertDatabaseHas('thread_participants', ['user_id' => $participant->id]);
    }

    public function test_create_thread_requires_subject_and_body(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/threads', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['subject', 'body']);
    }

    public function test_participant_ids_must_exist_in_users_table(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/threads', [
                'subject' => 'Test',
                'body' => 'Body',
                'participant_ids' => [99999],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['participant_ids.0']);
    }

    // ─── show ────────────────────────────────────────────────────────────────

    public function test_participant_can_view_thread_with_messages(): void
    {
        $user = User::factory()->create();
        $thread = Thread::factory()->create(['created_by' => $user->id]);
        $thread->participants()->attach($user->id);
        Message::factory(3)->create(['thread_id' => $thread->id, 'sender_id' => $user->id]);

        $this->actingAs($user)
            ->getJson("/api/threads/{$thread->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $thread->id)
            ->assertJsonCount(3, 'data.messages');
    }

    public function test_non_participant_cannot_view_thread(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();

        $thread = Thread::factory()->create(['created_by' => $owner->id]);
        $thread->participants()->attach($owner->id);

        $this->actingAs($outsider)
            ->getJson("/api/threads/{$thread->id}")
            ->assertForbidden();
    }
}
