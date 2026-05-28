<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\ChatConversation;
use App\Models\ChatConversationParticipant;
use App\Models\ChatMessage;
use App\Models\Mission;
use App\Models\User;
use App\Notifications\ChatMessageReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ChatMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_send_emails_other_participant_when_messaging_notifications_are_enabled(): void
    {
        Notification::fake();

        $userA = User::factory()->create();
        $userB = User::factory()->create([
            'permissions_utilisateur' => 'messaging,favoriteMission',
        ]);

        $conversationId = $this->createDirectConversation($userA, $userB);

        $this->actingAs($userA, 'sanctum')
            ->postJson("/api/conversations/{$conversationId}/messages", [
                'text' => 'Salut, peux-tu vérifier la mission ?',
                'type' => 'text',
            ])
            ->assertStatus(201);

        Notification::assertSentTo($userB, ChatMessageReceivedNotification::class);
        Notification::assertNotSentTo($userA, ChatMessageReceivedNotification::class);
    }

    public function test_message_send_does_not_email_participant_when_messaging_notifications_are_disabled(): void
    {
        Notification::fake();

        $userA = User::factory()->create();
        $userB = User::factory()->create([
            'permissions_utilisateur' => 'favoriteMission',
        ]);

        $conversationId = $this->createDirectConversation($userA, $userB);

        $this->actingAs($userA, 'sanctum')
            ->postJson("/api/conversations/{$conversationId}/messages", [
                'text' => 'Salut, est-ce que tu es disponible ?',
                'type' => 'text',
            ])
            ->assertStatus(201);

        Notification::assertNotSentTo($userB, ChatMessageReceivedNotification::class);
    }

    public function test_unauthenticated_user_cannot_list_conversations(): void
    {
        $this->getJson('/api/conversations')
            ->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_user_can_create_direct_conversation_and_reuse_existing_one(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $firstResponse = $this->actingAs($userA, 'sanctum')
            ->postJson('/api/conversations/direct', [
                'other_user_id' => $userB->id_utilisateur,
            ])
            ->assertStatus(201)
            ->assertJsonPath('conversation.type', 'direct');

        $conversationId = $firstResponse->json('conversation.id');

        $this->actingAs($userA, 'sanctum')
            ->postJson('/api/conversations/direct', [
                'other_user_id' => $userB->id_utilisateur,
            ])
            ->assertStatus(200)
            ->assertJsonPath('conversation.id', $conversationId);

        $this->assertDatabaseCount('chat_conversations', 1);
        $this->assertDatabaseHas('chat_conversation_participants', [
            'id_chat_conversation' => (int) $conversationId,
            'id_utilisateur' => $userA->id_utilisateur,
        ]);
        $this->assertDatabaseHas('chat_conversation_participants', [
            'id_chat_conversation' => (int) $conversationId,
            'id_utilisateur' => $userB->id_utilisateur,
        ]);
    }

    public function test_user_cannot_create_direct_conversation_with_self(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/conversations/direct', [
                'other_user_id' => $user->id_utilisateur,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Impossible de creer une conversation avec soi-meme.');
    }

    public function test_participant_can_send_and_list_messages(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $conversationId = $this->createDirectConversation($userA, $userB);

        $sendResponse = $this->actingAs($userA, 'sanctum')
            ->postJson("/api/conversations/{$conversationId}/messages", [
                'text' => 'Salut équipe',
                'type' => 'text',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.text', 'Salut équipe');

        $messageId = (int) $sendResponse->json('data.id');

        $this->actingAs($userA, 'sanctum')
            ->getJson("/api/conversations/{$conversationId}/messages")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $messageId)
            ->assertJsonPath('0.senderId', (string) $userA->id_utilisateur)
            ->assertJsonPath('0.text', 'Salut équipe');

        $this->assertDatabaseHas('chat_messages', [
            'id_chat_message' => $messageId,
            'id_chat_conversation' => $conversationId,
            'id_sender_utilisateur' => $userA->id_utilisateur,
            'contenu_message' => 'Salut équipe',
        ]);

        // Sender is auto-marked as read at send time.
        $this->assertDatabaseHas('chat_message_reads', [
            'id_chat_message' => $messageId,
            'id_utilisateur' => $userA->id_utilisateur,
        ]);
    }

    public function test_non_participant_cannot_access_or_send_messages(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $outsider = User::factory()->create();

        $conversationId = $this->createDirectConversation($userA, $userB);

        $this->actingAs($outsider, 'sanctum')
            ->getJson("/api/conversations/{$conversationId}/messages")
            ->assertStatus(404)
            ->assertJsonPath('message', 'Conversation introuvable ou inaccessible.');

        $this->actingAs($outsider, 'sanctum')
            ->postJson("/api/conversations/{$conversationId}/messages", [
                'text' => 'Je ne devrais pas passer',
            ])
            ->assertStatus(404)
            ->assertJsonPath('message', 'Conversation introuvable ou inaccessible.');
    }

    public function test_mark_read_works_for_participant_and_fails_for_non_participant(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $outsider = User::factory()->create();

        $conversationId = $this->createDirectConversation($userA, $userB);

        $message = ChatMessage::create([
            'id_chat_conversation' => $conversationId,
            'id_sender_utilisateur' => $userA->id_utilisateur,
            'type_message' => 'text',
            'contenu_message' => 'Message à lire',
        ]);

        $this->actingAs($userB, 'sanctum')
            ->postJson("/api/messages/{$message->id_chat_message}/read")
            ->assertStatus(200)
            ->assertJsonPath('id_chat_message', $message->id_chat_message);

        $this->assertDatabaseHas('chat_message_reads', [
            'id_chat_message' => $message->id_chat_message,
            'id_utilisateur' => $userB->id_utilisateur,
        ]);

        $this->actingAs($outsider, 'sanctum')
            ->postJson("/api/messages/{$message->id_chat_message}/read")
            ->assertStatus(403)
            ->assertJsonPath('message', 'Conversation inaccessible.');
    }

    public function test_list_conversations_backfills_missing_mission_group_for_active_assignment(): void
    {
        $user = User::factory()->create();
        $missionA = Mission::factory()->create();
        $missionB = Mission::factory()->create();

        Affectation::factory()->create([
            'id_utilisateur' => $user->id_utilisateur,
            'id_mission' => $missionA->id_mission,
            'statut_affectation' => 'assigne',
        ]);
        Affectation::factory()->create([
            'id_utilisateur' => $user->id_utilisateur,
            'id_mission' => $missionB->id_mission,
            'statut_affectation' => 'assigne',
        ]);

        $existingConversation = ChatConversation::create([
            'type_conversation' => 'group',
            'titre_conversation' => 'Mission A',
            'id_mission' => $missionA->id_mission,
            'created_by_utilisateur_id' => $user->id_utilisateur,
        ]);

        ChatConversationParticipant::create([
            'id_chat_conversation' => (int) $existingConversation->id_chat_conversation,
            'id_utilisateur' => $user->id_utilisateur,
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/conversations')
            ->assertStatus(200);

        $this->assertDatabaseHas('chat_conversations', [
            'type_conversation' => 'group',
            'id_mission' => $missionB->id_mission,
        ]);

        $createdConversationId = (int) ChatConversation::query()
            ->where('type_conversation', 'group')
            ->where('id_mission', $missionB->id_mission)
            ->value('id_chat_conversation');

        $this->assertGreaterThan(0, $createdConversationId);

        $this->assertDatabaseHas('chat_conversation_participants', [
            'id_chat_conversation' => $createdConversationId,
            'id_utilisateur' => $user->id_utilisateur,
        ]);

        $missionIdsFromResponse = collect($response->json())
            ->where('type', 'group')
            ->pluck('missionId')
            ->filter()
            ->values()
            ->all();

        $this->assertContains((string) $missionA->id_mission, $missionIdsFromResponse);
        $this->assertContains((string) $missionB->id_mission, $missionIdsFromResponse);
    }

    private function createDirectConversation(User $userA, User $userB): int
    {
        $response = $this->actingAs($userA, 'sanctum')
            ->postJson('/api/conversations/direct', [
                'other_user_id' => $userB->id_utilisateur,
            ])
            ->assertStatus(201);

        return (int) $response->json('conversation.id');
    }
}
