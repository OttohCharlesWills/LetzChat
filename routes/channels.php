<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Each conversation gets its own private channel. A user may only listen
| on it if they're an active (non-left) participant of that conversation.
|
*/

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return Conversation::where('id', $conversationId)
        ->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->whereNull('conversation_participants.left_at');
        })
        ->exists();
});