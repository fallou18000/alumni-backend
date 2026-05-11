<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;

class ConversationController extends Controller
{
    //
public function index()
{
    $userId = auth()->id();

    $conversations = Conversation::where('user1_id', $userId)
        ->orWhere('user2_id', $userId)
        ->with(['user1', 'user2'])
        ->get()
        ->map(function ($conv) use ($userId) {

            // 🔥 LAST MESSAGE CORRECT
            $lastMessage = Message::where('conversation_id', $conv->id)
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc') // IMPORTANT
                ->first();

            // 🔥 UNREAD COUNT
            $unread = Message::where('conversation_id', $conv->id)
                ->where('sender_id', '!=', $userId)
                ->whereNull('read_at')
                ->count();

            return [
                'id' => $conv->id,
                'user1_id' => $conv->user1_id,
                'user2_id' => $conv->user2_id,
                'user1' => $conv->user1,
                'user2' => $conv->user2,
                'last_message' => $lastMessage, // 🔥 IMPORTANT
                'unread' => $unread,
            ];
        });

    return response()->json($conversations);
}
}
