<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatConversationParticipant;
use App\Models\ChatMessage;
use App\Models\ChatMessageRead;
use App\Models\User;
use App\Events\ChatConversationChanged;
use App\Events\ChatMessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class ChatMessageController extends Controller
{
    private function resolveActorFromBearerToken(Request $request): ?User
    {
        $actor = $request->user('sanctum');
        if ($actor instanceof User) {
            return $actor;
        }

        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);
        $tokenable = $accessToken?->tokenable;

        return $tokenable instanceof User ? $tokenable : null;
    }

    private function findConversationForActor(int $conversationId, int $actorId): ?ChatConversation
    {
        return ChatConversation::query()
            ->where('id_chat_conversation', $conversationId)
            ->whereHas('participants', function ($query) use ($actorId) {
                $query->where('id_utilisateur', $actorId);
            })
            ->first();
    }

    private function mapUser(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        return [
            'id' => (string) $user->id_utilisateur,
            'firstName' => $user->prenom_utilisateur,
            'lastName' => $user->nom_utilisateur,
            'email' => $user->email,
        ];
    }

    private function mapMessage(ChatMessage $message): array
    {
        return [
            'id' => (string) $message->id_chat_message,
            'conversationId' => (string) $message->id_chat_conversation,
            'senderId' => (string) $message->id_sender_utilisateur,
            'type' => $message->type_message,
            'text' => $message->contenu_message,
            'createdAt' => $message->created_at,
            'sender' => $this->mapUser($message->sender),
            'readByUserIds' => $message->reads
                ->pluck('id_utilisateur')
                ->map(fn ($value) => (string) $value)
                ->values(),
        ];
    }

    public function index(Request $request, $conversationId)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if (!$actor) {
            return response()->json(['message' => 'Non authentifie'], 401);
        }

        $actorId = (int) $actor->id_utilisateur;
        $conversation = $this->findConversationForActor((int) $conversationId, $actorId);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation introuvable ou inaccessible.'], 404);
        }

        $messages = ChatMessage::query()
            ->where('id_chat_conversation', (int) $conversation->id_chat_conversation)
            ->with([
                'sender:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
                'reads:id_chat_message_read,id_chat_message,id_utilisateur,read_at',
            ])
            ->orderBy('created_at')
            ->limit(500)
            ->get();

        return response()->json($messages->map(fn (ChatMessage $message) => $this->mapMessage($message))->values());
    }

    public function store(Request $request, $conversationId)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if (!$actor) {
            return response()->json(['message' => 'Non authentifie'], 401);
        }

        $actorId = (int) $actor->id_utilisateur;
        $conversation = $this->findConversationForActor((int) $conversationId, $actorId);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation introuvable ou inaccessible.'], 404);
        }

        $validated = $request->validate([
            'text' => 'required|string|max:2000',
            'type' => 'nullable|string|max:20',
        ]);

        $message = DB::transaction(function () use ($conversation, $actorId, $validated): ChatMessage {
            $next = ChatMessage::create([
                'id_chat_conversation' => (int) $conversation->id_chat_conversation,
                'id_sender_utilisateur' => $actorId,
                'type_message' => $validated['type'] ?? 'text',
                'contenu_message' => $validated['text'],
            ]);

            ChatMessageRead::updateOrCreate(
                [
                    'id_chat_message' => (int) $next->id_chat_message,
                    'id_utilisateur' => $actorId,
                ],
                [
                    'read_at' => now(),
                ]
            );

            ChatConversationParticipant::query()
                ->where('id_chat_conversation', (int) $conversation->id_chat_conversation)
                ->where('id_utilisateur', $actorId)
                ->update(['last_read_at' => now()]);

            $conversation->update(['last_message_at' => now()]);

            return $next;
        });

        $message->load([
            'sender:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'reads:id_chat_message_read,id_chat_message,id_utilisateur,read_at',
        ]);

        $conversation->loadMissing([
            'participants.utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email,role_utilisateur',
            'lastMessage',
        ]);

        broadcast(new ChatMessageSent($message));
        broadcast(new ChatConversationChanged($conversation));

        return response()->json([
            'message' => 'Message envoye.',
            'data' => $this->mapMessage($message),
        ], 201);
    }

    public function markRead(Request $request, $messageId)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if (!$actor) {
            return response()->json(['message' => 'Non authentifie'], 401);
        }

        $actorId = (int) $actor->id_utilisateur;

        $message = ChatMessage::query()
            ->with('conversation')
            ->where('id_chat_message', (int) $messageId)
            ->first();

        if (!$message || !$message->conversation) {
            return response()->json(['message' => 'Message introuvable.'], 404);
        }

        $participant = ChatConversationParticipant::query()
            ->where('id_chat_conversation', (int) $message->id_chat_conversation)
            ->where('id_utilisateur', $actorId)
            ->first();

        if (!$participant) {
            return response()->json(['message' => 'Conversation inaccessible.'], 403);
        }

        ChatMessageRead::updateOrCreate(
            [
                'id_chat_message' => (int) $message->id_chat_message,
                'id_utilisateur' => $actorId,
            ],
            [
                'read_at' => now(),
            ]
        );

        $participant->update(['last_read_at' => now()]);

        return response()->json([
            'message' => 'Message marque comme lu.',
            'id_chat_message' => (int) $message->id_chat_message,
        ]);
    }
}
