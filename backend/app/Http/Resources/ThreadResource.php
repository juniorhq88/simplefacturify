<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'last_message_at' => $this->last_message_at,
            'messages_count' => $this->whenCounted('messages'),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'participants' => UserResource::collection($this->whenLoaded('participants')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'latest_message' => new MessageResource($this->whenLoaded('latestMessage')),
            'created_at' => $this->created_at,
        ];
    }
}
