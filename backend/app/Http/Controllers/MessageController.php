<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\InboxNotification;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * POST /api/threads/{thread}/messages
     * Reply to an existing thread.
     * Supports numeric thread ID or participant email/username.
     */
    public function store(StoreMessageRequest $request, string $thread): JsonResponse
    {
        $threadModel = $this->resolveThread($request, $thread);

        // Gate: only participants may reply
        abort_unless(
            $threadModel->participants()->where('user_id', $request->user()->id)->exists(),
            403,
            'You are not a participant of this thread.'
        );

        $message = $threadModel->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        // Notify other participants
        $threadModel->participants()
            ->where('user_id', '!=', $request->user()->id)
            ->get()
            ->each(fn ($participant) => InboxNotification::create([
                'user_id' => $participant->id,
                'message_id' => $message->id,
                'thread_id' => $threadModel->id,
            ]));

        $message->load('sender:id,name,email');

        return response()->json(['data' => new MessageResource($message)], 201);
    }

    private function resolveThread(Request $request, string $identifier): Thread
    {
        // Try to find by numeric ID first
        if (is_numeric($identifier)) {
            $thread = Thread::find($identifier);
            if ($thread) {
                return $thread;
            }
        }

        // Try to find user by email or name
        $user = User::where('email', 'like', "%{$identifier}%")
            ->orWhere('name', 'like', "%{$identifier}%")
            ->first();

        if (!$user) {
            abort(404, 'User not found: ' . $identifier);
        }

        // Find thread with this user (where current user is also a participant)
        $thread = Thread::whereHas('participants', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();

        if (!$thread) {
            abort(404, 'No thread found with user: ' . $identifier);
        }

        return $thread;
    }
}
