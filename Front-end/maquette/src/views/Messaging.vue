<template>
  <div class="min-vh-100 bg-body-tertiary pb-5">
    <div class="container py-3" style="max-width: 1120px;">
      <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
        <div>
          <h2 class="h4 mb-1">Messagerie</h2>
          <p class="text-muted mb-0 small">Discussions 1:1 et groupes mission automatiques</p>
        </div>
        <button class="btn btn-primary btn-sm" @click="openDirectModal = true">
          Nouvelle discussion
        </button>
      </div>

      <div class="row g-3 messaging-layout">
        <div class="col-12 col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-2">
              <div class="btn-group w-100 mb-2" role="group">
                <button class="btn btn-sm" :class="activeFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary'" @click="activeFilter = 'all'">Tous</button>
                <button class="btn btn-sm" :class="activeFilter === 'direct' ? 'btn-dark' : 'btn-outline-secondary'" @click="activeFilter = 'direct'">Privé</button>
                <button class="btn btn-sm" :class="activeFilter === 'group' ? 'btn-dark' : 'btn-outline-secondary'" @click="activeFilter = 'group'">Groupes</button>
              </div>

              <div v-if="isLoading" class="text-center py-4 text-muted small">Chargement des discussions...</div>
              <div v-else-if="filteredConversations.length === 0" class="text-center py-4 text-muted small">
                Aucune discussion.
              </div>

              <div v-else class="conversation-list">
                <button
                  v-for="conversation in filteredConversations"
                  :key="conversation.id"
                  type="button"
                  class="conversation-item w-100 text-start border-0 bg-transparent"
                  :class="{ active: selectedConversation?.id === conversation.id }"
                  @click="selectConversation(conversation.id)">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                      <div class="fw-semibold small">{{ getConversationName(conversation) }}</div>
                      <div class="text-muted x-small text-truncate" style="max-width: 220px;">
                        {{ conversation.lastMessagePreview || 'Pas encore de message' }}
                      </div>
                    </div>
                    <span class="badge rounded-pill" :class="conversation.type === 'group' ? 'text-bg-warning' : 'text-bg-info'">
                      {{ conversation.type === 'group' ? 'Groupe' : 'Privé' }}
                    </span>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-8">
          <div class="card border-0 shadow-sm h-100 d-flex flex-column">
            <div class="card-body p-0 d-flex flex-column" style="min-height: 520px;">
              <template v-if="selectedConversation">
                <div class="border-bottom px-3 py-2 d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-semibold">{{ getConversationName(selectedConversation) }}</div>
                    <div class="x-small text-muted">
                      {{ selectedConversation.type === 'group' ? 'Groupe auto-créé depuis une mission' : 'Discussion 1:1' }}
                    </div>
                  </div>
                </div>

                <div v-if="selectedConversation.type === 'group'" class="px-3 py-2 border-bottom bg-light">
                  <div class="x-small text-muted mb-1">Membres du groupe ({{ selectedGroupMembers.length }})</div>
                  <div v-if="selectedGroupMembers.length" class="d-flex flex-wrap gap-1">
                    <span
                      v-for="member in selectedGroupMembers"
                      :key="member.id"
                      class="badge text-bg-secondary">
                      {{ member.name }}
                    </span>
                  </div>
                  <div v-else class="x-small text-muted">Aucun membre pour l'instant.</div>
                </div>

                <div ref="messagesContainer" class="flex-grow-1 p-3 overflow-auto" style="background:#f8fafc;">
                  <div v-if="messages.length === 0" class="text-center text-muted small py-5">
                    Commence la conversation.
                  </div>

                  <div v-else class="d-flex flex-column gap-2">
                    <div
                      v-for="message in messages"
                      :key="message.id"
                      class="d-flex"
                      :class="isOwnMessage(message) ? 'justify-content-end' : 'justify-content-start'">
                      <div class="message-bubble" :class="isOwnMessage(message) ? 'message-own' : 'message-other'">
                        <div v-if="selectedConversation.type === 'group' && !isOwnMessage(message)" class="x-small fw-semibold mb-1">
                          {{ getUserDisplayName(message.senderId) }}
                        </div>
                        <div>{{ message.text }}</div>
                        <div class="x-small opacity-75 mt-1 text-end">{{ formatDate(message.createdAt) }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <form class="border-top p-2 d-flex gap-2" @submit.prevent="handleSendMessage">
                  <input
                    v-model="draftMessage"
                    class="form-control"
                    placeholder="Écrire un message..."
                    autocomplete="off"
                  />
                  <button class="btn btn-primary" type="submit" :disabled="!draftMessage.trim()">Envoyer</button>
                </form>
              </template>

              <div v-else class="h-100 d-flex align-items-center justify-content-center text-muted">
                Sélectionne une discussion.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="openDirectModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(15, 23, 42, 0.45);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Nouvelle discussion privée</h5>
            <button type="button" class="btn-close" aria-label="Close" @click="openDirectModal = false"></button>
          </div>
          <div class="modal-body">
            <p class="small text-muted">
              Les discussions de groupe sont créées automatiquement lors de la création d'une mission.
            </p>
            <div class="list-group" style="max-height: 300px; overflow: auto;">
              <button
                v-for="member in otherUsers"
                :key="member.id"
                type="button"
                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                @click="startDirectConversation(member.id)">
                <span>{{ member.firstName }} {{ member.lastName }}</span>
                <span class="badge text-bg-secondary">{{ member.accountType }}</span>
              </button>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="openDirectModal = false">Fermer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import api from '@/services/api'
import missionService from '@/services/missionService'
import userService from '@/services/userService'
import chatService from '@/services/chatService'
import { getCurrentUser } from '@/utils/auth'

const currentUser = getCurrentUser()
const currentUserId = String(currentUser?.id || '')

const isLoading = ref(true)
const openDirectModal = ref(false)
const activeFilter = ref('all')
const draftMessage = ref('')
const messagesContainer = ref(null)

const users = ref([])
const usersById = ref({})
const conversations = ref([])
const selectedConversationId = ref(null)
const messages = ref([])

const selectedConversation = computed(() =>
  conversations.value.find((conversation) => conversation.id === selectedConversationId.value) || null
)

const selectedGroupMembers = computed(() => {
  if (!selectedConversation.value || selectedConversation.value.type !== 'group') return []

  return (selectedConversation.value.memberIds || [])
    .map((memberId) => String(memberId))
    .filter(Boolean)
    .map((memberId) => {
      const user = usersById.value[memberId]
      if (!user) {
        return { id: memberId, name: `Utilisateur #${memberId}` }
      }

      const fullName = `${user.firstName || ''} ${user.lastName || ''}`.trim()
      return {
        id: memberId,
        name: fullName || user.email || `Utilisateur #${memberId}`,
      }
    })
})

const filteredConversations = computed(() => {
  if (activeFilter.value === 'all') return conversations.value
  return conversations.value.filter((conversation) => conversation.type === activeFilter.value)
})

const otherUsers = computed(() =>
  users.value.filter((user) => String(user.id) !== currentUserId)
)

const formatDate = (dateValue) => {
  const date = new Date(dateValue)
  return date.toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const getUserDisplayName = (userId) => {
  const user = usersById.value[String(userId)]
  if (!user) return 'Utilisateur'
  return `${user.firstName} ${user.lastName}`.trim()
}

const getConversationName = (conversation) => {
  if (!conversation) return ''
  if (conversation.type === 'group') return conversation.name || 'Groupe mission'

  const otherId = (conversation.memberIds || []).map(String).find((memberId) => memberId !== currentUserId)
  return getUserDisplayName(otherId)
}

const isOwnMessage = (message) => String(message.senderId) === currentUserId

const loadMessages = async () => {
  if (!selectedConversation.value) {
    messages.value = []
    return
  }

  messages.value = chatService.getMessagesForConversation(selectedConversation.value.id)
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const refreshConversations = () => {
  conversations.value = chatService.getConversationsForUser(currentUserId)
  if (!conversations.value.length) {
    selectedConversationId.value = null
    messages.value = []
    return
  }

  const stillExists = conversations.value.some((conversation) => conversation.id === selectedConversationId.value)
  if (!stillExists) {
    selectedConversationId.value = conversations.value[0].id
  }
}

const selectConversation = async (conversationId) => {
  selectedConversationId.value = conversationId
  await loadMessages()
}

const startDirectConversation = async (otherUserId) => {
  const conversation = chatService.ensureDirectConversation(currentUserId, String(otherUserId))
  if (!conversation) return

  openDirectModal.value = false
  refreshConversations()
  await selectConversation(conversation.id)
}

const handleSendMessage = async () => {
  if (!selectedConversation.value || !draftMessage.value.trim()) return

  chatService.sendMessage({
    conversationId: selectedConversation.value.id,
    senderId: currentUserId,
    text: draftMessage.value,
  })

  draftMessage.value = ''
  refreshConversations()
  await loadMessages()
}

const loadMessagingContext = async () => {
  isLoading.value = true

  try {
    const [usersList, missionsList, affectationsResponse, postulationsResponse] = await Promise.all([
      userService.getAll(),
      missionService.getAll(),
      api.get('/affectations').then((response) => response.data).catch(() => []),
      api.get('/postulations').then((response) => response.data).catch(() => []),
    ])

    users.value = Array.isArray(usersList) ? usersList : []
    usersById.value = users.value.reduce((acc, user) => {
      acc[String(user.id)] = user
      return acc
    }, {})

    const missions = Array.isArray(missionsList) ? missionsList : []
    const affectations = Array.isArray(affectationsResponse) ? affectationsResponse : []
    const postulations = Array.isArray(postulationsResponse) ? postulationsResponse : []

    chatService.syncMissionGroups(missions, affectations, postulations, currentUserId)

    refreshConversations()
    await loadMessages()
  } catch {
    conversations.value = []
    messages.value = []
  } finally {
    isLoading.value = false
  }
}

watch(selectedConversationId, async () => {
  await loadMessages()
})

onMounted(async () => {
  if (!currentUserId) return
  await loadMessagingContext()
})
</script>

<style scoped>
.messaging-layout .card {
  border-radius: 1rem;
}

.conversation-list {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  max-height: 70vh;
  overflow: auto;
}

.conversation-item {
  border-radius: 0.75rem;
  padding: 0.65rem 0.75rem;
  transition: background-color 0.2s ease;
}

.conversation-item:hover {
  background: #f8fafc;
}

.conversation-item.active {
  background: #ecfeff;
}

.message-bubble {
  max-width: min(80%, 520px);
  border-radius: 0.9rem;
  padding: 0.5rem 0.7rem;
  font-size: 0.94rem;
}

.message-own {
  background: #dcfce7;
}

.message-other {
  background: #ffffff;
}

@media (max-width: 991px) {
  .messaging-layout .col-12.col-lg-8 .card-body {
    min-height: 400px !important;
  }
}
</style>
