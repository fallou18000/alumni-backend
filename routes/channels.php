<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{id}', function ($user, $id) {

    $conversation = Conversation::find($id);

    if (!$conversation) return false;

    return $conversation->user1_id === $user->id
        || $conversation->user2_id === $user->id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});