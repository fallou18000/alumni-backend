<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\Conversation;
use App\Events\MessageSent;
use App\Events\MessageDeleted;

class MessageController extends Controller
{
    /**
     * 📩 GET OR CREATE CONVERSATION + MESSAGES
     */
public function getConversation($userId)
{
    $authId = Auth::id();

    $conversation = Conversation::where(function ($q) use ($authId, $userId) {
        $q->where('user1_id', $authId)
          ->where('user2_id', $userId);
    })->orWhere(function ($q) use ($authId, $userId) {
        $q->where('user1_id', $userId)
          ->where('user2_id', $authId);
    })->with(['user1', 'user2'])
      ->first();

    if (!$conversation) {
        $conversation = Conversation::create([
            'user1_id' => min($authId, $userId),
            'user2_id' => max($authId, $userId),
        ]);

        $conversation->load(['user1', 'user2']);
    }

    // ================= 🔥 DELIVERED AUTO =================
    Message::where('conversation_id', $conversation->id)
        ->where('sender_id', '!=', $authId)
        ->whereNull('delivered_at')
        ->update([
            'delivered_at' => now()
        ]);

    // ================= LOAD MESSAGES =================
$messages = Message::where('conversation_id', $conversation->id)
    ->with('sender')
    ->where(function ($q) {
        $q->whereNull('deleted_at')
          ->where('deleted_for_everyone', false);
    })
    ->orderBy('created_at', 'asc')
    ->get();

    // ================= OTHER USER =================
    $otherUser = $authId == $conversation->user1_id
        ? $conversation->user2
        : $conversation->user1;

    return response()->json([
        'conversation' => $conversation,
        'messages' => $messages,
        'user' => $otherUser
    ]);
}
public function send(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|exists:conversations,id',
        'content' => 'nullable|string|max:2000',
        'file' => 'nullable|file|max:20480'
    ]);

    $authId = Auth::id();

    $conversation = Conversation::findOrFail($request->conversation_id);

    if (
        $conversation->user1_id !== $authId &&
        $conversation->user2_id !== $authId
    ) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // FILE
    $fileUrl = null;
    $fileType = 'text';

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('messages', 'public');

        $mime = $file->getMimeType();

        if (str_starts_with($mime, 'image/')) $fileType = 'image';
        elseif (str_starts_with($mime, 'video/')) $fileType = 'video';
        elseif (str_starts_with($mime, 'audio/')) $fileType = 'audio';
        else $fileType = 'file';

        $fileUrl = asset('storage/' . $path);
    }

    $receiverId = $conversation->user1_id === $authId
        ? $conversation->user2_id
        : $conversation->user1_id;

    /**
     * 🔥 IMPORTANT LOGIC
     * delivered = receiver a ping récemment (online)
     * read = jamais ici
     */

    $isOnline = cache()->has("user_online_" . $receiverId);

    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $authId,
        'content' => $request->content,
        'file_path' => $fileUrl,
        'file_type' => $fileType,
        'type' => $fileType,

        // ✔ delivered seulement si online
        'delivered_at' => $isOnline ? now() : null,

        // ❌ jamais ici
        'read_at' => null,
    ]);

    broadcast(new MessageSent($message))->toOthers();

    return response()->json($message);
}

public function sendMedia(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|exists:conversations,id',
        'file' => 'required|file|max:20480'
    ]);

    $authId = auth()->id();
    $conversation = Conversation::findOrFail($request->conversation_id);

    $file = $request->file('file');
    $path = $file->store('media', 'public');
    $mime = $file->getMimeType();

    if (str_starts_with($mime, 'image/')) $type = 'image';
elseif (str_starts_with($mime, 'video/')) $type = 'video';
elseif (str_starts_with($mime, 'audio/')) $type = 'audio';
else $type = 'file';

    $receiverId = $conversation->user1_id === $authId
        ? $conversation->user2_id
        : $conversation->user1_id;

    $isOnline = cache()->has("user_online_" . $receiverId);

    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $authId,
        'type' => $type,
         'content' => asset('storage/' . $path),
    'file_path' => asset('storage/' . $path),
        'delivered_at' => $isOnline ? now() : null,
        'read_at' => null,
    ]);

    broadcast(new MessageSent($message))->toOthers();

    return response()->json($message);
}

public function sendAudio(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|exists:conversations,id',
        'audio' => 'required|file|max:10240'
    ]);

    $authId = auth()->id();
    $conversation = Conversation::findOrFail($request->conversation_id);

    $receiverId = $conversation->user1_id === $authId
        ? $conversation->user2_id
        : $conversation->user1_id;

    $path = $request->file('audio')->store('audios', 'public');

    $isOnline = cache()->has("user_online_" . $receiverId);

    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $authId,
        'type' => 'audio',
        'content' => asset('storage/' . $path),
        'delivered_at' => $isOnline ? now() : null,
        'read_at' => null,
    ]);

    broadcast(new MessageSent($message))->toOthers();

    return response()->json($message);
}
   public function getConversations()
{
    $authId = Auth::id();

    $conversations = Conversation::where('user1_id', $authId)
        ->orWhere('user2_id', $authId)
        ->with(['user1', 'user2'])
        ->get()
        ->map(function ($conv) use ($authId) {

            $otherUser = $conv->user1_id == $authId
                ? $conv->user2
                : $conv->user1;

          $lastMessage = Message::where('conversation_id', $conv->id)
    ->where(function ($q) {
        $q->whereNotNull('content')
          ->orWhereNotNull('file_path');
    })
    ->latest()
    ->first();

            $unread = Message::where('conversation_id', $conv->id)
                ->whereNull('read_at')
                ->where('sender_id', '!=', $authId)
                ->count();

            return [
                'id' => $conv->id,
                'user' => $otherUser,
                'last_message' => $lastMessage,
                'unread' => $unread,
                'updated_at' => $lastMessage?->created_at
            ];
        })
       ->sortByDesc(function ($conv) {
    return Message::where('conversation_id', $conv->id)
        ->latest('created_at')
        ->value('created_at');
})
        ->values();

    return response()->json($conversations);
}

    /**
     * 🗑 DELETE MESSAGE (WHATSAPP STYLE)
     */
   public function delete(Request $request, $id)
{
    $message = Message::findOrFail($id);

    if ($message->sender_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $mode = $request->input('mode');

    if (!in_array($mode, ['me', 'everyone'])) {
        return response()->json(['message' => 'Invalid mode'], 400);
    }

    $conversationId = $message->conversation_id;

    // 👤 delete for me
    if ($mode === "me") {
        $message->delete(); // soft delete
        return response()->json(['id' => $id]);
    }

    // 🌍 delete for everyone
    if ($mode === "everyone") {
       $message->update([
    'content' => '',
    'file_path' => null,
    'file_type' => null,
    'deleted_for_everyone' => true,
]);

        broadcast(new MessageDeleted(
            $message->id,
            $conversationId,
            
        ))->toOthers();

        return response()->json(['id' => $id]);
    }
}
    /**
     * 👀 MARK AS READ (DOUBLE TICK)
     */
public function markAsRead($conversationId)
{
    $userId = auth()->id();

    // 🔥 IMPORTANT : doit être dans le chat
    if (!cache()->has("user_in_chat_{$conversationId}_{$userId}")) {
        return response()->json(['skip' => true]);
    }

    Message::where('conversation_id', $conversationId)
        ->where('sender_id', '!=', $userId)
        ->whereNull('read_at')
        ->update([
            'delivered_at' => now(), // important
            'read_at' => now()
        ]);

    return response()->json(['ok' => true]);
}

public function markAsDelivered($conversationId)
{
    $userId = auth()->id();

    if (!$userId) {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    $updated = Message::where('conversation_id', $conversationId)
        ->where('sender_id', '!=', $userId)
        ->update([
            'delivered_at' => now()
        ]);

    return response()->json([
        'ok' => true,
        'updated' => $updated
    ]);
}


public function pingOnline()
{
    $userId = auth()->id();

    cache()->put(
        "user_online_" . $userId,
        true,
        now()->addSeconds(60)
    );

    return response()->json(['online' => true]);
}

public function enterChat($conversationId)
{
    $userId = auth()->id();

    if (!$userId) {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    // 🔥 Vérifie que la conversation existe
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return response()->json(['error' => 'Conversation not found'], 404);
    }

    // 💬 user est DANS le chat
    cache()->put(
        "user_in_chat_{$conversationId}_{$userId}",
        true
    );

    return response()->json([
        'ok' => true,
        'in_chat' => true
    ]);
}

public function leaveChat($conversationId)
{
    $userId = auth()->id();

    if (!$userId) {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    cache()->forget(
        "user_in_chat_{$conversationId}_{$userId}"
    );

    return response()->json([
        'ok' => true,
        'in_chat' => false
    ]);
}
    









}