<template>
  <div class="min-vh-100 bg-body-tertiary pb-5">
    <div class="container py-3" style="max-width: 1120px;">
      <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
        <div>
          <h2 class="h4 mb-1">Messagerie</h2>
          <p class="text-muted mb-0 small">Discussions 1:1 et groupes mission automatiques</p>
          <p class="text-muted mb-0 x-small" v-if="isAutoSyncing">Synchronisation en direct...</p>
        </div>
        <button class="btn btn-primary btn-sm" @click="openDirectModal = true">
          Nouvelle discussion
        </button>
      </div>

      <div v-if="loadError" class="alert alert-danger py-2 small">{{ loadError }}</div>

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
                  <div v-if="Number(conversation.unreadCount || 0) > 0" class="mt-1 text-end">
                    <span class="badge rounded-pill text-bg-danger">{{ Number(conversation.unreadCount) }}</span>
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
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import userService from '@/services/userService'
import chatApiService from '@/services/chatApiService'
import getEcho, { leaveEchoChannel, destroyEcho } from '@/services/realtime'
import { getCurrentUser } from '@/utils/auth'

const currentUser = getCurrentUser()
const currentUserId = String(currentUser?.id || '')

const isLoading = ref(true)
const loadError = ref('')
const isAutoSyncing = ref(false)
const openDirectModal = ref(false)
const activeFilter = ref('all')
const draftMessage = ref('')
const messagesContainer = ref(null)

const users = ref([])
const usersById = ref({})
const conversations = ref([])
const selectedConversationId = ref(null)
const messages = ref([])
const conversationPollHandle = ref(null)
const messagePollHandle = ref(null)
const userChannelName = ref('')
const activeConversationChannelName = ref('')
const echo = ref(null)

const CONVERSATION_POLL_MS = 30000
const MESSAGES_POLL_MS = 15000

const selectedConversation = computed(() =>
  conversations.value.find((conversation) => conversation.id === selectedConversationId.value) || null
)

const selectedGroupMembers = computed(() => {
  if (!selectedConversation.value || selectedConversation.value.type !== 'group') return []

  if (Array.isArray(selectedConversation.value.members) && selectedConversation.value.members.length > 0) {
    return selectedConversation.value.members.map((member) => {
      const fullName = `${member.firstName || ''} ${member.lastName || ''}`.trim()
      return {
        id: String(member.id || ''),
        name: fullName || member.email || `Utilisateur #${member.id}`,
      }
    })
  }

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

  const member = (conversation.members || []).find((entry) => String(entry.id) !== currentUserId)
  if (member) {
    const fullName = `${member.firstName || ''} ${member.lastName || ''}`.trim()
    return fullName || member.email || 'Utilisateur'
  }

  const otherId = (conversation.memberIds || []).map(String).find((memberId) => memberId !== currentUserId)
  return getUserDisplayName(otherId)
}

const isOwnMessage = (message) => String(message.senderId) === currentUserId

const markConversationMessagesAsRead = async (conversationId, rows) => {
  const unreadMessages = rows.filter((message) => {
    const isOwn = String(message.senderId) === currentUserId
    const alreadyRead = (message.readByUserIds || []).map(String).includes(currentUserId)
    return !isOwn && !alreadyRead
  })

  if (!unreadMessages.length) return

  await Promise.all(unreadMessages.map((message) =>
    chatApiService.markMessageRead(message.id).catch(() => null)
  ))

  conversations.value = conversations.value.map((conversation) =>
    conversation.id === conversationId
      ? { ...conversation, unreadCount: 0 }
      : conversation
  )
}

const mergeConversation = (nextConversation) => {
  if (!nextConversation?.id) return

  const index = conversations.value.findIndex((conversation) => conversation.id === nextConversation.id)
  if (index === -1) {
    conversations.value = [nextConversation, ...conversations.value]
    return
  }

  const merged = {
    ...conversations.value[index],
    ...nextConversation,
  }

  conversations.value = [
    ...conversations.value.slice(0, index),
    merged,
    ...conversations.value.slice(index + 1),
  ]
}

const mergeConversationList = (nextConversations) => {
  const selectedId = selectedConversationId.value
  conversations.value = Array.isArray(nextConversations) ? nextConversations : []

  if (!conversations.value.length) {
    selectedConversationId.value = null
    messages.value = []
    return
  }

  const hasSelection = selectedId && conversations.value.some((conversation) => conversation.id === selectedId)
  if (!hasSelection) {
    selectedConversationId.value = conversations.value[0].id
  }
}

const upsertMessage = (nextMessage) => {
  if (!nextMessage?.id || !selectedConversation.value || String(nextMessage.conversationId) !== String(selectedConversation.value.id)) {
    return
  }

  if (messages.value.some((message) => message.id === nextMessage.id)) {
    return
  }

  messages.value = [...messages.value, nextMessage].sort(
    (a, b) => new Date(a.createdAt || 0).getTime() - new Date(b.createdAt || 0).getTime()
  )
}

const refreshConversationFromRealtime = async () => {
  try {
    const rows = await chatApiService.listConversations()
    mergeConversationList(rows)
  } catch {
    // fallback only
  }
}

const refreshSelectedConversationMessages = async () => {
  if (!selectedConversation.value) return

  try {
    const rows = await chatApiService.listMessages(selectedConversation.value.id)
    messages.value = rows
    await markConversationMessagesAsRead(selectedConversation.value.id, rows)
  } catch {
    // fallback only
  }
}

const bindConversationChannel = (conversationId) => {
  const instance = echo.value || getEcho()
  if (!instance || !conversationId) return

  const nextName = `chat.conversation.${conversationId}`
  if (activeConversationChannelName.value === nextName) return

  if (activeConversationChannelName.value) {
    leaveEchoChannel(activeConversationChannelName.value)
  }

  activeConversationChannelName.value = nextName
  instance.private(nextName)
    .listen('.chat.message.sent', (event) => {
      upsertMessage(event?.message)
      refreshConversationFromRealtime()
      if (selectedConversation.value && String(selectedConversation.value.id) === String(conversationId) && !isOwnMessage(event?.message || {})) {
        markConversationMessagesAsRead(conversationId, [event.message]).catch(() => null)
      }
    })
}

const bindUserChannel = () => {
  const instance = echo.value || getEcho()
  if (!instance || !currentUserId) return

  const nextName = `chat.user.${currentUserId}`
  if (userChannelName.value === nextName) return

  if (userChannelName.value) {
    leaveEchoChannel(userChannelName.value)
  }

  userChannelName.value = nextName
  instance.private(nextName)
    .listen('.chat.conversation.changed', () => {
      refreshConversationFromRealtime()
    })
}

const loadMessages = async () => {
  if (!selectedConversation.value) {
    messages.value = []
    return
  }

  const rows = await chatApiService.listMessages(selectedConversation.value.id)
  messages.value = rows
  await markConversationMessagesAsRead(selectedConversation.value.id, rows)

  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }

  bindConversationChannel(selectedConversation.value.id)
}

const refreshConversations = async () => {
  const rows = await chatApiService.listConversations()
  mergeConversationList(rows)
}

const runAutoRefreshConversations = async () => {
  if (document.hidden) return

  try {
    isAutoSyncing.value = true
    const rows = await chatApiService.listConversations()
    mergeConversationList(rows)
  } catch {
    // Keep existing data if silent sync fails.
  } finally {
    isAutoSyncing.value = false
  }
}

const runAutoRefreshMessages = async () => {
  if (document.hidden || !selectedConversationId.value) return

  try {
    const rows = await chatApiService.listMessages(selectedConversationId.value)
    messages.value = rows
    await markConversationMessagesAsRead(selectedConversationId.value, rows)
  } catch {
    // Keep existing data if silent sync fails.
  }
}

const stopPolling = () => {
  if (conversationPollHandle.value) {
    clearInterval(conversationPollHandle.value)
    conversationPollHandle.value = null
  }

  if (messagePollHandle.value) {
    clearInterval(messagePollHandle.value)
    messagePollHandle.value = null
  }
}

const startPolling = () => {
  stopPolling()

  conversationPollHandle.value = setInterval(() => {
    runAutoRefreshConversations()
  }, CONVERSATION_POLL_MS)

  messagePollHandle.value = setInterval(() => {
    runAutoRefreshMessages()
  }, MESSAGES_POLL_MS)
}

const handleVisibilityChange = () => {
  if (document.hidden) return

  runAutoRefreshConversations()
  runAutoRefreshMessages()
}

const selectConversation = async (conversationId) => {
  selectedConversationId.value = conversationId
  await loadMessages()
}

const startDirectConversation = async (otherUserId) => {
  loadError.value = ''

  try {
    const response = await chatApiService.startDirectConversation(otherUserId)
    const conversation = response.conversation
    if (!conversation?.id) return

    openDirectModal.value = false
    await refreshConversations()
    await selectConversation(conversation.id)
  } catch (error) {
    loadError.value = error.message || 'Impossible de démarrer la discussion privée.'
  }
}

const handleSendMessage = async () => {
  if (!selectedConversation.value || !draftMessage.value.trim()) return

  loadError.value = ''

  try {
    await chatApiService.sendMessage({
      conversationId: selectedConversation.value.id,
      text: draftMessage.value,
    })

    draftMessage.value = ''
    await refreshConversations()
    await loadMessages()
  } catch (error) {
    loadError.value = error.message || 'Impossible d\'envoyer le message.'
  }
}

const loadMessagingContext = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const [usersList] = await Promise.all([
      userService.getAll(),
    ])

    users.value = Array.isArray(usersList) ? usersList : []
    usersById.value = users.value.reduce((acc, user) => {
      acc[String(user.id)] = user
      return acc
    }, {})

    await refreshConversations()
    await loadMessages()
    echo.value = getEcho()
    bindUserChannel()
    if (selectedConversation.value) {
      bindConversationChannel(selectedConversation.value.id)
    }
  } catch (error) {
    loadError.value = error.message || 'Impossible de charger la messagerie.'
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
  startPolling()
  document.addEventListener('visibilitychange', handleVisibilityChange)
})

onUnmounted(() => {
  stopPolling()
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  if (userChannelName.value) {
    leaveEchoChannel(userChannelName.value)
  }
  if (activeConversationChannelName.value) {
    leaveEchoChannel(activeConversationChannelName.value)
  }
  destroyEcho()
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
