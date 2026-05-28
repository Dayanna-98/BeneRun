<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
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
