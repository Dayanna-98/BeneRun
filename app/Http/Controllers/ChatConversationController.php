<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatConversationParticipant;
use App\Events\ChatConversationChanged;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class ChatConversationController extends Controller
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
            'role' => $user->role_utilisateur,
        ];
    }

    private function mapConversation(ChatConversation $conversation): array
    {
        $participants = $conversation->participants
            ->map(fn (ChatConversationParticipant $participant) => $this->mapUser($participant->utilisateur))
            ->filter()
            ->values();

        return [
            'id' => (string) $conversation->id_chat_conversation,
            'type' => $conversation->type_conversation,
            'name' => $conversation->titre_conversation,
            'missionId' => $conversation->id_mission ? (string) $conversation->id_mission : null,
            'memberIds' => $participants->pluck('id')->values(),
            'members' => $participants,
            'lastMessagePreview' => $conversation->lastMessage?->contenu_message,
            'lastMessageAt' => $conversation->last_message_at,
            'updatedAt' => $conversation->updated_at,
            'createdAt' => $conversation->created_at,
            'unreadCount' => (int) ($conversation->unread_count ?? 0),
        ];
    }

    private function loadConversationForActor(ChatConversation $conversation, int $actorId): ChatConversation
    {
        return ChatConversation::query()
            ->where('id_chat_conversation', $conversation->id_chat_conversation)
            ->with([
                'participants.utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email,role_utilisateur',
                'lastMessage:id_chat_message,id_chat_conversation,id_sender_utilisateur,contenu_message,created_at',
            ])
            ->withCount([
                'messages as unread_count' => function ($query) use ($actorId) {
                    $query
                        ->where('id_sender_utilisateur', '!=', $actorId)
                        ->whereDoesntHave('reads', function ($subQuery) use ($actorId) {
                            $subQuery->where('id_utilisateur', $actorId);
                        });
                },
            ])
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if (!$actor) {
            return response()->json(['message' => 'Non authentifie'], 401);
        }

        $actorId = (int) $actor->id_utilisateur;

        $conversations = ChatConversation::query()
            ->whereHas('participants', function ($query) use ($actorId) {
                $query->where('id_utilisateur', $actorId);
            })
            ->with([
                'participants.utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email,role_utilisateur',
                'lastMessage:id_chat_message,id_chat_conversation,id_sender_utilisateur,contenu_message,created_at',
            ])
            ->withCount([
                'messages as unread_count' => function ($query) use ($actorId) {
                    $query
                        ->where('id_sender_utilisateur', '!=', $actorId)
                        ->whereDoesntHave('reads', function ($subQuery) use ($actorId) {
                            $subQuery->where('id_utilisateur', $actorId);
                        });
                },
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json($conversations->map(fn (ChatConversation $conversation) => $this->mapConversation($conversation))->values());
    }

    public function storeDirect(Request $request)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if (!$actor) {
            return response()->json(['message' => 'Non authentifie'], 401);
        }

        $validated = $request->validate([
            'other_user_id' => 'required|integer|exists:users,id_utilisateur',
        ]);

        $actorId = (int) $actor->id_utilisateur;
        $otherUserId = (int) $validated['other_user_id'];

        if ($actorId === $otherUserId) {
            return response()->json(['message' => 'Impossible de creer une conversation avec soi-meme.'], 422);
        }

        $existing = ChatConversation::query()
            ->where('type_conversation', 'direct')
            ->whereHas('participants', function ($query) use ($actorId) {
                $query->where('id_utilisateur', $actorId);
            })
            ->whereHas('participants', function ($query) use ($otherUserId) {
                $query->where('id_utilisateur', $otherUserId);
            })
            ->withCount('participants')
            ->get()
            ->first(fn (ChatConversation $conversation) => (int) $conversation->participants_count === 2);

        $created = false;
        $conversation = $existing;

        if (!$conversation) {
            $created = true;

            $conversation = DB::transaction(function () use ($actorId, $otherUserId): ChatConversation {
                $next = ChatConversation::create([
                    'type_conversation' => 'direct',
                    'titre_conversation' => null,
                    'id_mission' => null,
                    'created_by_utilisateur_id' => $actorId,
                ]);

                ChatConversationParticipant::create([
                    'id_chat_conversation' => (int) $next->id_chat_conversation,
                    'id_utilisateur' => $actorId,
                    'joined_at' => now(),
                ]);

                ChatConversationParticipant::create([
                    'id_chat_conversation' => (int) $next->id_chat_conversation,
                    'id_utilisateur' => $otherUserId,
                    'joined_at' => now(),
                ]);

                return $next;
            });
        }

        $loaded = $this->loadConversationForActor($conversation, $actorId);

        broadcast(new ChatConversationChanged($loaded));

        return response()->json([
            'message' => $created ? 'Conversation privee creee.' : 'Conversation privee existante.',
            'conversation' => $this->mapConversation($loaded),
        ], $created ? 201 : 200);
    }

    public function storeOrGetMissionGroup(Request $request)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if (!$actor) {
            return response()->json(['message' => 'Non authentifie'], 401);
        }

        $validated = $request->validate([
            'mission_id' => 'required|integer|exists:missions,id_mission',
            'name' => 'nullable|string|max:255',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'integer|exists:users,id_utilisateur',
        ]);

        $actorId = (int) $actor->id_utilisateur;
        $missionId = (int) $validated['mission_id'];

        $mission = Mission::query()
            ->with('evenement:id_evenement,nom_evenement')
            ->find($missionId);

        if (!$mission) {
            return response()->json(['message' => 'Mission introuvable.'], 404);
        }

        $defaultName = sprintf(
            '%s • %s',
            (string) ($mission->titre_mission ?? ('Mission #' . $missionId)),
            (string) ($mission->evenement?->nom_evenement ?? 'Événement')
        );

        $conversation = ChatConversation::query()
            ->where('type_conversation', 'group')
            ->where('id_mission', $missionId)
            ->first();

        $created = false;

        if (!$conversation) {
            $created = true;

            $conversation = ChatConversation::create([
                'type_conversation' => 'group',
                'titre_conversation' => trim((string) ($validated['name'] ?? '')) ?: $defaultName,
                'id_mission' => $missionId,
                'created_by_utilisateur_id' => $actorId,
            ]);
        } elseif (!empty($validated['name'])) {
            $conversation->update([
                'titre_conversation' => trim((string) $validated['name']),
            ]);
        }

        $participantIds = collect($validated['participant_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->push($actorId)
            ->unique()
            ->values();

        foreach ($participantIds as $participantId) {
            ChatConversationParticipant::updateOrCreate(
                [
                    'id_chat_conversation' => (int) $conversation->id_chat_conversation,
                    'id_utilisateur' => $participantId,
                ],
                [
                    'joined_at' => now(),
                ]
            );
        }

        $loaded = $this->loadConversationForActor($conversation, $actorId);

        broadcast(new ChatConversationChanged($loaded));

        return response()->json([
            'message' => $created ? 'Groupe mission cree.' : 'Groupe mission mis a jour.',
            'conversation' => $this->mapConversation($loaded),
        ], $created ? 201 : 200);
    }

    public function addParticipant(Request $request, $conversationId)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if (!$actor) {
            return response()->json(['message' => 'Non authentifie'], 401);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id_utilisateur',
        ]);

        $actorId = (int) $actor->id_utilisateur;
        $targetUserId = (int) $validated['user_id'];

        $conversation = ChatConversation::query()->find((int) $conversationId);
        if (!$conversation) {
            return response()->json(['message' => 'Conversation introuvable.'], 404);
        }

        if ($conversation->type_conversation !== 'group') {
            return response()->json(['message' => 'Ajout de participant reserve aux groupes.'], 422);
        }

        $actorParticipant = ChatConversationParticipant::query()
            ->where('id_chat_conversation', (int) $conversation->id_chat_conversation)
            ->where('id_utilisateur', $actorId)
            ->exists();

        if (!$actorParticipant) {
            return response()->json(['message' => 'Conversation inaccessible.'], 403);
        }

        ChatConversationParticipant::updateOrCreate(
            [
                'id_chat_conversation' => (int) $conversation->id_chat_conversation,
                'id_utilisateur' => $targetUserId,
            ],
            [
                'joined_at' => now(),
            ]
        );

        $loaded = $this->loadConversationForActor($conversation, $actorId);

        broadcast(new ChatConversationChanged($loaded));

        return response()->json([
            'message' => 'Participant ajoute au groupe.',
            'conversation' => $this->mapConversation($loaded),
        ]);
    }
}
