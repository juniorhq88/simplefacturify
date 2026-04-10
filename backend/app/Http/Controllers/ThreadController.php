<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreThreadRequest;
use App\Http\Resources\ThreadResource;
use App\Models\InboxNotification;
use App\Models\Message;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ThreadController extends Controller
{
    /**
     * GET /api/threads
     * Paginated list of threads for the authenticated user.
     * Supports: ?search=term  ?per_page=15
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $threads = Thread::query()
            ->forUser($request->user()->id)
            ->search($request->query('search'))
            ->with(['creator:id,name,email', 'latestMessage.sender:id,name'])
            ->withCount(['messages'])
            ->orderByDesc('last_message_at')
            ->paginate($request->integer('per_page', 15));

        return ThreadResource::collection($threads);
    }

    /**
     * GET /api/threads/{thread}
     * Thread details with all messages.
     */
    public function show(Request $request, Thread $thread): JsonResponse
    {
        // Gate: only participants may read the thread
        $this->authorizeParticipant($request, $thread);

        // Mark notifications as read for this user
        $thread->messages()
            ->whereHas('notifications', fn ($q) => $q
                ->where('user_id', $request->user()->id)
                ->whereNull('read_at')
            )
            ->get()
            ->each(fn ($msg) => $msg->notifications()
                ->where('user_id', $request->user()->id)
                ->update(['read_at' => now()])
            );

        // Update participant last_read_at
        $thread->participants()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        $thread->load(['creator:id,name,email', 'participants:id,name,email', 'messages.sender:id,name,email']);

        return response()->json([
            'data' => new ThreadResource($thread),
        ]);
    }

    /**
     * POST /api/threads
     * Create a new thread with its first message.
     */
    public function store(StoreThreadRequest $request): JsonResponse
    {
        $thread = Thread::create([
            'subject' => $request->subject,
            'created_by' => $request->user()->id,
        ]);

        // Creator is automatically a participant
        $participantIds = array_unique(
            array_merge([$request->user()->id], $request->participant_ids ?? [])
        );
        $thread->participants()->attach($participantIds);

        // First message
        $message = $thread->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        // Notify other participants
        $this->dispatchNotifications($thread, $message, $request->user()->id);

        $thread->load(['creator:id,name,email', 'participants:id,name,email', 'messages.sender:id,name,email']);

        return response()->json(['data' => new ThreadResource($thread)], 201);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function authorizeParticipant(Request $request, Thread $thread): void
    {
        abort_unless(
            $thread->participants()->where('user_id', $request->user()->id)->exists(),
            403,
            'You are not a participant of this thread.'
        );
    }

    private function dispatchNotifications(Thread $thread, Message $message, int $senderId): void
    {
        $thread->participants()
            ->where('user_id', '!=', $senderId)
            ->get()
            ->each(fn ($participant) => InboxNotification::create([
                'user_id' => $participant->id,
                'message_id' => $message->id,
                'thread_id' => $thread->id,
            ]));
    }
}
