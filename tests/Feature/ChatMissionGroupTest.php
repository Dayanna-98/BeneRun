<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\ChatConversation;
use App\Models\ChatConversationParticipant;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatMissionGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_create_mission_group(): void
    {
        $mission = Mission::factory()->create();

        $this->postJson('/api/conversations/mission', [
            'mission_id' => $mission->id_mission,
        ])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_user_can_create_mission_group_with_default_name(): void
    {
        $actor = User::factory()->create();
        $participant = User::factory()->create();
        $mission = Mission::factory()->create([
            'titre_mission' => 'Mission Eau',
            'responsable_utilisateur_id' => $actor->id_utilisateur,
        ]);

        Affectation::factory()->create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $participant->id_utilisateur,
            'statut_affectation' => 'assigne',
            'est_responsable' => false,
        ]);

        $response = $this->actingAs($actor, 'sanctum')
            ->postJson('/api/conversations/mission', [
                'mission_id' => $mission->id_mission,
            ])
            ->assertStatus(201)
            ->assertJsonPath('conversation.type', 'group')
            ->assertJsonPath('conversation.missionId', (string) $mission->id_mission);

        $conversationId = (int) $response->json('conversation.id');
        $conversationName = (string) $response->json('conversation.name');

        $this->assertStringContainsString('Mission Eau', $conversationName);

        $this->assertDatabaseHas('chat_conversations', [
            'id_chat_conversation' => $conversationId,
            'type_conversation' => 'group',
            'id_mission' => $mission->id_mission,
            'created_by_utilisateur_id' => $actor->id_utilisateur,
        ]);

        $this->assertDatabaseHas('chat_conversation_participants', [
            'id_chat_conversation' => $conversationId,
            'id_utilisateur' => $actor->id_utilisateur,
        ]);

        $this->assertDatabaseHas('chat_conversation_participants', [
            'id_chat_conversation' => $conversationId,
            'id_utilisateur' => $participant->id_utilisateur,
        ]);
    }

    public function test_mission_group_is_reused_and_name_can_be_updated(): void
    {
        $actor = User::factory()->create();
        $mission = Mission::factory()->create([
            'titre_mission' => 'Mission Logistique',
            'responsable_utilisateur_id' => $actor->id_utilisateur,
        ]);

        $first = $this->actingAs($actor, 'sanctum')
            ->postJson('/api/conversations/mission', [
                'mission_id' => $mission->id_mission,
            ])
            ->assertStatus(201);

        $conversationId = (int) $first->json('conversation.id');

        $this->actingAs($actor, 'sanctum')
            ->postJson('/api/conversations/mission', [
                'mission_id' => $mission->id_mission,
                'name' => 'Groupe officiel mission',
            ])
            ->assertStatus(200)
            ->assertJsonPath('conversation.id', (string) $conversationId)
            ->assertJsonPath('conversation.name', 'Groupe officiel mission');

        $this->assertDatabaseCount('chat_conversations', 1);
        $this->assertDatabaseHas('chat_conversation_participants', [
            'id_chat_conversation' => $conversationId,
            'id_utilisateur' => $actor->id_utilisateur,
        ]);
    }

    public function test_create_mission_group_validates_mission_id(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor, 'sanctum')
            ->postJson('/api/conversations/mission', [
                'mission_id' => 999999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['mission_id']);
    }

    public function test_cannot_manually_add_participant_to_mission_group(): void
    {
        $actor = User::factory()->create();
        $newParticipant = User::factory()->create();
        $mission = Mission::factory()->create([
            'responsable_utilisateur_id' => $actor->id_utilisateur,
        ]);

        $created = $this->actingAs($actor, 'sanctum')
            ->postJson('/api/conversations/mission', [
                'mission_id' => $mission->id_mission,
            ])
            ->assertStatus(201);

        $conversationId = (int) $created->json('conversation.id');

        $this->actingAs($actor, 'sanctum')
            ->postJson("/api/conversations/{$conversationId}/participants", [
                'user_id' => $newParticipant->id_utilisateur,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Les participants d\'un groupe mission sont synchronisés automatiquement.');
    }

    public function test_non_participant_cannot_add_participant_to_group(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $target = User::factory()->create();

        $conversation = ChatConversation::create([
            'type_conversation' => 'group',
            'titre_conversation' => 'Groupe libre',
            'id_mission' => null,
            'created_by_utilisateur_id' => $owner->id_utilisateur,
        ]);

        ChatConversationParticipant::create([
            'id_chat_conversation' => (int) $conversation->id_chat_conversation,
            'id_utilisateur' => $owner->id_utilisateur,
            'joined_at' => now(),
        ]);

        $this->actingAs($outsider, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id_chat_conversation}/participants", [
                'user_id' => $target->id_utilisateur,
            ])
            ->assertStatus(403)
            ->assertJsonPath('message', 'Conversation inaccessible.');
    }

    public function test_cannot_add_participant_to_direct_conversation(): void
    {
        $actor = User::factory()->create();
        $other = User::factory()->create();
        $third = User::factory()->create();

        $direct = $this->actingAs($actor, 'sanctum')
            ->postJson('/api/conversations/direct', [
                'other_user_id' => $other->id_utilisateur,
            ])
            ->assertStatus(201);

        $conversationId = (int) $direct->json('conversation.id');

        $this->actingAs($actor, 'sanctum')
            ->postJson("/api/conversations/{$conversationId}/participants", [
                'user_id' => $third->id_utilisateur,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Ajout de participant reserve aux groupes.');
    }

    public function test_add_participant_returns_404_when_conversation_does_not_exist(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($actor, 'sanctum')
            ->postJson('/api/conversations/999999/participants', [
                'user_id' => $target->id_utilisateur,
            ])
            ->assertStatus(404)
            ->assertJsonPath('message', 'Conversation introuvable.');
    }
}
