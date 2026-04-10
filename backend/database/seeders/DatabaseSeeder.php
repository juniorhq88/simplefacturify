<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Deterministic test user ───────────────────────────────────────────
        $alice = User::factory()->create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
        ]);

        $bob = User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
        ]);

        $carol = User::factory()->create([
            'name' => 'Carol',
            'email' => 'carol@example.com',
            'password' => Hash::make('password'),
        ]);

        // ── Thread 1: Alice → Bob (direct) ───────────────────────────────────
        $thread1 = Thread::create([
            'subject' => 'Invoice #1042 question',
            'created_by' => $alice->id,
        ]);
        $thread1->participants()->attach([$alice->id, $bob->id]);
        $thread1->messages()->create(['sender_id' => $alice->id, 'body' => 'Hi Bob, can you check invoice #1042? Something looks off.']);
        $thread1->messages()->create(['sender_id' => $bob->id,   'body' => 'Sure, I will take a look right away.']);
        $thread1->messages()->create(['sender_id' => $alice->id, 'body' => 'Thank you!']);

        // ── Thread 2: Group chat ──────────────────────────────────────────────
        $thread2 = Thread::create([
            'subject' => 'Q3 planning sync',
            'created_by' => $bob->id,
        ]);
        $thread2->participants()->attach([$alice->id, $bob->id, $carol->id]);
        $thread2->messages()->create(['sender_id' => $bob->id,   'body' => 'Let\'s align on Q3 goals. Sharing the doc shortly.']);
        $thread2->messages()->create(['sender_id' => $carol->id, 'body' => 'Great, I have a few items to add.']);
        $thread2->messages()->create(['sender_id' => $alice->id, 'body' => 'Same here. Friday works for me.']);

        // ── Thread 3: Carol solo to Alice ─────────────────────────────────────
        $thread3 = Thread::create([
            'subject' => 'Onboarding checklist',
            'created_by' => $carol->id,
        ]);
        $thread3->participants()->attach([$carol->id, $alice->id]);
        $thread3->messages()->create(['sender_id' => $carol->id, 'body' => 'Alice, here is the onboarding checklist for the new hire.']);

        // ── Extra random threads for Alice ────────────────────────────────────
        Thread::factory(5)->create(['created_by' => $alice->id])->each(function ($thread) use ($alice) {
            $thread->participants()->attach($alice->id);
            Message::factory(rand(1, 4))->create([
                'thread_id' => $thread->id,
                'sender_id' => $alice->id,
            ]);
        });

        $this->command->info('Seeded inbox data. Login with alice@example.com / password');
    }
}
