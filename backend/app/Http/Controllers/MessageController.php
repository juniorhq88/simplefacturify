<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\InboxNotification;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    /**
     * POST /api/threads/{thread}/messages
     * Reply to an existing thread.
     */
    public function store(StoreMessageRequest $request, Thread $thread): JsonResponse
    {
        // Gate: only participants may reply
        abort_unless(
            $thread->participants()->where('user_id', $request->user()->id)->exists(),
            403,
            'You are not a participant of this thread.'
        );

        $message = $thread->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        // Notify other participants
        $thread->participants()
            ->where('user_id', '!=', $request->user()->id)
            ->get()
            ->each(fn ($participant) => InboxNotification::create([
                'user_id' => $participant->id,
                'message_id' => $message->id,
                'thread_id' => $thread->id,
            ]));

        $message->load('sender:id,name,email');

        return response()->json(['data' => new MessageResource($message)], 201);
    }
}
