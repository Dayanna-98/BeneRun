<?php

namespace App\Services;

use App\Models\Affectation;
use App\Models\ChatConversation;
use App\Models\ChatConversationParticipant;
use App\Models\Mission;
use App\Models\Postulation;
use Illuminate\Support\Collection;

class MissionConversationService
{
    public function syncAssignedParticipants(int $missionId, ?int $creatorUserId = null): ?ChatConversation
    {
        $conversation = $this->ensureMissionGroup($missionId, $creatorUserId);
        if (! $conversation) {
            return null;
        }

        $participantIds = $this->eligibleMissionParticipantIds($missionId);

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

        $participantsQuery = ChatConversationParticipant::query()
            ->where('id_chat_conversation', (int) $conversation->id_chat_conversation);

        if ($participantIds->isEmpty()) {
            $participantsQuery->delete();
        } else {
            $participantsQuery
                ->whereNotIn('id_utilisateur', $participantIds->all())
                ->delete();
        }

        return $conversation;
    }

    private function eligibleMissionParticipantIds(int $missionId): Collection
    {
        $assignmentIds = Affectation::query()
            ->where('id_mission', $missionId)
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->pluck('id_utilisateur')
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->values();

        $registrationIds = Postulation::query()
            ->where('id_mission', $missionId)
            ->whereIn('statut_postulation', ['en_attente', 'accepte'])
            ->pluck('id_utilisateur')
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->values();

        $responsableId = Mission::query()
            ->where('id_mission', $missionId)
            ->value('responsable_utilisateur_id');

        return collect([$responsableId])
            ->merge($assignmentIds)
            ->merge($registrationIds)
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values();
    }

    private function ensureMissionGroup(int $missionId, ?int $creatorUserId = null): ?ChatConversation
    {
        $mission = Mission::query()
            ->with('evenement:id_evenement,nom_evenement')
            ->find($missionId);

        if (! $mission) {
            return null;
        }

        $defaultName = sprintf(
            '%s • %s',
            (string) ($mission->titre_mission ?? ('Mission #'.$missionId)),
            (string) ($mission->evenement?->nom_evenement ?? 'Événement')
        );

        $conversation = ChatConversation::query()
            ->where('type_conversation', 'group')
            ->where('id_mission', $missionId)
            ->first();

        if (! $conversation) {
            $conversation = ChatConversation::create([
                'type_conversation' => 'group',
                'titre_conversation' => $defaultName,
                'id_mission' => $missionId,
                'created_by_utilisateur_id' => $creatorUserId,
            ]);

            return $conversation;
        }

        if (! is_string($conversation->titre_conversation) || trim($conversation->titre_conversation) === '') {
            $conversation->update([
                'titre_conversation' => $defaultName,
            ]);
        }

        if (! $conversation->created_by_utilisateur_id && $creatorUserId) {
            $conversation->update([
                'created_by_utilisateur_id' => $creatorUserId,
            ]);
        }

        return $conversation;
    }
}
