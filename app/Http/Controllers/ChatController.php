<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User; // Asegúrate de importar User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Encuentra o crea un chat con otro usuario y devuelve los mensajes.
     * Endpoint: GET /api/chat/{user_id}
     */
    public function getChat(User $user)
    {
        $currentUserId = Auth::id();

        if ($currentUserId === $user->id) {
            return response()->json(['message' => 'No puedes chatear contigo mismo.'], 400);
        }

        // 1. Encontrar el chat (independientemente del orden de user1_id y user2_id)
        $chat = Chat::where(function ($query) use ($currentUserId, $user) {
            $query->where('user1_id', $currentUserId)
                  ->where('user2_id', $user->id);
        })->orWhere(function ($query) use ($currentUserId, $user) {
            $query->where('user1_id', $user->id)
                  ->where('user2_id', $currentUserId);
        })->first();

        // 2. Crear el chat si no existe
        if (!$chat) {
            // Se normaliza el orden para la unicidad (ID menor en user1_id)
            $user1Id = min($currentUserId, $user->id);
            $user2Id = max($currentUserId, $user->id);

            $chat = Chat::create([
                'user1_id' => $user1Id,
                'user2_id' => $user2Id,
            ]);
        }
        
        // 3. Obtener los mensajes del chat, ordenados por tiempo de creación
        $messages = Message::where('chat_id', $chat->id)
                           ->with('sender:id,name') // Cargamos quién lo envió
                           ->orderBy('created_at', 'asc')
                           ->get();

        // 4. Marcar mensajes como leídos (opcional, pero buena práctica)
        Message::where('chat_id', $chat->id)
               ->where('read_at', null)
               ->where('sender_id', '!=', $currentUserId)
               ->update(['read_at' => now()]);

        return response()->json([
            'chat_id' => $chat->id,
            'partner' => ['id' => $user->id, 'name' => $user->name],
            'messages' => $messages,
        ]);
    }

    /**
     * Envía un nuevo mensaje al chat.
     * Endpoint: POST /api/chat/{chat_id}
     */
    public function sendMessage(Request $request, Chat $chat)
    {
        $currentUserId = Auth::id();

        // 1. Verificar si el usuario actual es participante del chat
        if ($chat->user1_id !== $currentUserId && $chat->user2_id !== $currentUserId) {
            return response()->json(['message' => 'No eres participante de este chat.'], 403);
        }

        // 2. Validación
        $request->validate(['content' => 'required|string|max:1000']);

        // 3. Crear el mensaje
        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $currentUserId,
            'content' => $request->content,
        ]);
        
        $message->load('sender:id,name'); // Recargamos el mensaje con el nombre del remitente

        return response()->json([
            'message_sent' => $message,
        ], 201);
    }
}