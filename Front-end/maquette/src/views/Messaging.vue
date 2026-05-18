<template>
  <div class="messaging-page">
    <!-- Header styled like ManageMissions -->
    <header class="text-white sticky-top overflow-hidden" style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div>
            <div class="x-small" style="color:rgba(255,255,255,.55)">Communication</div>
            <h1 class="fs-5 fw-semibold mb-0">Messagerie</h1>
          </div>
        </div>
      </div>
    </header>

    <div class="container py-2 messaging-shell" style="max-width: 1120px;">
      <div class="messaging-header d-flex justify-content-between align-items-end gap-2 flex-wrap mb-2 mt-1">
        <div>
          <p class="mb-0 x-small hero-sync" v-if="isAutoSyncing" aria-live="polite">Synchronisation en direct...</p>
        </div>

        <div>
          <button class="btn btn-primary new-discussion-btn" @click="openNewDiscussionModal" aria-label="Ouvrir une nouvelle discussion">
            Nouvelle discussion
          </button>
        </div>
      </div>

      <div v-if="loadError" class="alert alert-danger py-2 small" role="alert">{{ loadError }}</div>

      <div class="row g-2 messaging-layout">
        <div class="col-12 col-lg-4">
          <div class="card border-0 shadow-sm h-100 conversation-card">
            <div class="card-body p-2">
              <div class="btn-group w-100 mb-2" role="group" aria-label="Filtrer les discussions">
                <button class="btn btn-sm filter-btn" :class="activeFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary'" @click="activeFilter = 'all'">Tous</button>
                <button class="btn btn-sm filter-btn" :class="activeFilter === 'direct' ? 'btn-dark' : 'btn-outline-secondary'" @click="activeFilter = 'direct'">Privé</button>
                <button class="btn btn-sm filter-btn" :class="activeFilter === 'group' ? 'btn-dark' : 'btn-outline-secondary'" @click="activeFilter = 'group'">Groupes</button>
              </div>

              <div class="conversation-tools mb-2">
                <input
                  v-model="conversationSearch"
                  class="form-control form-control-sm"
                  placeholder="Rechercher une discussion..."
                  autocomplete="off"
                  aria-label="Chercher dans les discussions"
                />
                <div class="d-flex gap-2 mt-2 flex-wrap">
                  <label class="form-check-label x-small d-flex align-items-center gap-2">
                    <input v-model="unreadOnly" class="form-check-input mt-0" type="checkbox" aria-label="Afficher uniquement les non lus" />
                    Non lus uniquement
                  </label>
                  <label class="form-check-label x-small d-flex align-items-center gap-2">
                    <input v-model="hideContentOnTabLoss" class="form-check-input mt-0" type="checkbox" @change="saveHideContentOnTabLoss" aria-label="Masquer le contenu quand l'onglet perd le focus" />
                    Cacher à la perte focus
                  </label>
                </div>
              </div>

              <div v-if="isLoading" class="text-center py-4 text-muted small">Chargement des discussions...</div>
              <div v-else-if="filteredConversations.length === 0" class="text-center py-4 text-muted small">
                Aucune discussion.
              </div>

              <div v-else class="conversation-list" role="list">
                <button
                  v-for="conversation in filteredConversations"
                  :key="conversation.id"
                  type="button"
                  class="conversation-item w-100 text-start border-0 bg-transparent"
                  :class="{ active: selectedConversation?.id === conversation.id }"
                  @click="selectConversation(conversation.id)"
                  :aria-current="selectedConversation?.id === conversation.id ? 'true' : 'false'"
                  role="listitem"
                  :aria-label="`Discussion: ${getConversationName(conversation)}`">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <div class="flex-grow-1">
                      <div class="fw-semibold small conversation-name">{{ getConversationName(conversation) }}</div>
                      <div class="text-muted x-small text-truncate conversation-preview">
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
          <div class="card border-0 shadow-sm h-100 d-flex flex-column chat-card">
            <div v-if="contentHidden" class="alert alert-warning py-2 small mb-0">
              💬 Contenu masqué (vous êtes hors de cet onglet)
            </div>
            <div class="card-body p-0 d-flex flex-column" style="min-height: 520px;">
              <template v-if="selectedConversation">
                <div class="border-bottom px-3 py-2 d-flex justify-content-between align-items-center chat-topbar">
                  <div>
                    <div class="fw-semibold">{{ getConversationName(selectedConversation) }}</div>
                    <div class="x-small topbar-subtitle">
                      {{ selectedConversation.type === 'group' ? 'Groupe auto-créé depuis une mission' : 'Discussion 1:1' }}
                    </div>
                  </div>
                </div>

                <div v-if="selectedConversation.type === 'group'" class="px-3 py-2 border-bottom group-members-bar">
                  <div class="x-small text-muted mb-1">Membres du groupe ({{ selectedGroupMembers.length }})</div>
                  <div v-if="selectedGroupMembers.length" class="d-flex flex-wrap gap-1">
                    <span
                      v-for="member in selectedGroupMembers"
                      :key="member.id"
                      class="badge member-chip">
                      {{ member.name }}
                    </span>
                  </div>
                  <div v-else class="x-small text-muted">Aucun membre pour l'instant.</div>
                </div>

                <div ref="messagesContainer" class="flex-grow-1 p-3 overflow-auto messages-scroll" role="log" aria-label="Fil de messages" aria-live="polite">
                  <div v-if="messages.length === 0" class="text-center text-muted small py-5">
                    Commence la conversation.
                  </div>

                  <div v-else class="d-flex flex-column gap-2">
                    <div
                      v-for="row in renderedMessages"
                      :key="row.id">
                      <div v-if="row.kind === 'separator'" class="message-day-separator">
                        <span>{{ row.label }}</span>
                      </div>

                      <div
                        v-else
                        class="d-flex"
                        :class="isOwnMessage(row.message) ? 'justify-content-end' : 'justify-content-start'">
                        <div class="message-bubble-wrapper" :class="isOwnMessage(row.message) ? 'own-wrapper' : 'other-wrapper'">
                          <!-- Show author name only on first message or when grouping breaks -->
                          <div v-if="row.showAuthor" class="x-small fw-semibold mb-1 author-name">
                            {{ getUserDisplayName(row.message.senderId) }}
                          </div>
                          
                          <div 
                            class="message-bubble" 
                            :class="[
                              isOwnMessage(row.message) ? 'message-own' : 'message-other',
                              { 'message-grouped': row.isGrouped }
                            ]">
                            <div>{{ row.message.text }}</div>
                            
                            <!-- Message status indicator (Optimistic Send) -->
                            <div class="d-flex align-items-center justify-content-between gap-2 mt-1">
                              <span 
                                :title="formatDateFull(row.message.createdAt)"
                                class="x-small opacity-75"
                                style="white-space: nowrap;">
                                {{ formatDate(row.message.createdAt) }}
                              </span>
                              
                              <span v-if="isOwnMessage(row.message)" :class="['message-status', 'x-small', `status-${row.message.status || 'sent'}`]">
                                <span v-if="row.message.status === 'sending'">⏳ Envoi...</span>
                                <span v-else-if="row.message.status === 'sent'">✓</span>
                                <span v-else-if="row.message.status === 'failed'">✗</span>
                              </span>
                            </div>
                            
                            <!-- Retry button for failed messages -->
                            <button
                              v-if="isOwnMessage(row.message) && row.message.status === 'failed'"
                              @click="retryFailedMessage(row.message.id)"
                              class="btn btn-sm btn-link mt-1 p-0 x-small"
                              aria-label="Réessayer d'envoyer ce message">
                              [Réessayer]
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <form class="border-top p-2 d-flex gap-2 composer" @submit.prevent="handleSendMessage" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
                  <textarea
                    ref="composerTextarea"
                    v-model="draftMessage"
                    class="form-control composer-input"
                    placeholder="Écrire un message..."
                    autocomplete="off"
                    @input="handleComposerInput"
                    @keydown.enter.ctrl="handleSendMessage"
                    rows="1"
                    aria-label="Composer votre message"
                    style="resize: none; max-height: 120px;"></textarea>
                  <button class="btn btn-primary composer-send" type="submit" :disabled="!draftMessage.trim() || isSendingMessage" aria-label="Envoyer le message">Envoyer</button>
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

    <div v-if="openDirectModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(15, 23, 42, 0.45);" aria-modal="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Nouvelle discussion privée</h5>
            <button type="button" class="btn-close" aria-label="Fermer" @click="openDirectModal = false"></button>
          </div>
          <div class="modal-body">
            <p class="small text-muted">
              Les discussions de groupe sont créées automatiquement lors de la création d'une mission.
            </p>
            <div class="list-group" style="max-height: 300px; overflow: auto;" role="list">
              <button
                v-for="member in otherUsers"
                :key="member.id"
                type="button"
                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                @click="startDirectConversation(member.id)"
                role="listitem">
                <span>{{ member.firstName }} {{ member.lastName }}</span>
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
const conversationSearch = ref('')
const unreadOnly = ref(false)
const draftMessage = ref('')
const messagesContainer = ref(null)
const composerTextarea = ref(null)
const usersLoaded = ref(false)
const isSendingMessage = ref(false)

// Confidentiality & Privacy
const hideContentOnTabLoss = ref(false)

// Performance & Optimization
const isSearching = ref(false)
const hasMoreOldMessages = ref(true)
const isLoadingOldMessages = ref(false)

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
const contentHidden = ref(false)

const CONVERSATION_POLL_MS = 30000
const MESSAGES_POLL_MS = 15000
const SEARCH_DEBOUNCE_MS = 500
const MESSAGE_GROUP_TIME_WINDOW_MS = 5 * 60 * 1000 // 5 minutes

// Utility: Debounce function
const debounce = (func, wait) => {
  let timeout
  return (...args) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => func(...args), wait)
  }
}

// Utility: Format relative time
const formatRelativeTime = (dateValue) => {
  const date = new Date(dateValue)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  
  const seconds = Math.floor(diff / 1000)
  const minutes = Math.floor(seconds / 60)
  const hours = Math.floor(minutes / 60)
  const days = Math.floor(hours / 24)
  
  if (seconds < 60) return 'À l\'instant'
  if (minutes < 60) return `Il y a ${minutes}m`
  if (hours < 24) return `Il y a ${hours}h`
  if (days === 1) return 'Hier'
  if (days < 7) return `Il y a ${days}j`
  
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: '2-digit' })
}

const selectedConversation = computed(() =>
  conversations.value.find((conversation) => conversation.id === selectedConversationId.value) || null
)

// Smart Message Grouping: Check if two messages should be grouped together
const shouldGroupMessages = (prevMessage, currentMessage) => {
  if (!prevMessage || !currentMessage) return false
  if (prevMessage.senderId !== currentMessage.senderId) return false
  
  const prevTime = new Date(prevMessage.createdAt).getTime()
  const currentTime = new Date(currentMessage.createdAt).getTime()
  const timeDiff = currentTime - prevTime
  
  return timeDiff <= MESSAGE_GROUP_TIME_WINDOW_MS
}

const selectedGroupMembers = computed(() => {
  if (!selectedConversation.value || selectedConversation.value.type !== 'group') return []

  if (Array.isArray(selectedConversation.value.members) && selectedConversation.value.members.length > 0) {
    return selectedConversation.value.members.map((member) => {
      const fullName = `${member.firstName || ''} ${member.lastName || ''}`.trim()
      return {
        id: String(member.id || ''),
        name: fullName || `Utilisateur #${member.id}`,
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
        name: fullName || `Utilisateur #${memberId}`,
      }
    })
})

const debouncedSearch = debounce(() => {
  isSearching.value = false
}, SEARCH_DEBOUNCE_MS)

const filteredConversations = computed(() => {
  const query = conversationSearch.value.trim().toLowerCase()

  return conversations.value.filter((conversation) => {
    if (activeFilter.value !== 'all' && conversation.type !== activeFilter.value) {
      return false
    }

    if (unreadOnly.value && Number(conversation.unreadCount || 0) <= 0) {
      return false
    }

    if (!query) {
      return true
    }

    const name = getConversationName(conversation).toLowerCase()
    const preview = String(conversation.lastMessagePreview || '').toLowerCase()

    return name.includes(query) || preview.includes(query)
  })
})

// Enhanced renderedMessages with smart grouping and better formatting
const renderedMessages = computed(() => {
  let lastDayKey = ''
  const result = []
  
  messages.value.forEach((message, index) => {
    const messageDate = new Date(message.createdAt)
    const dayKey = Number.isNaN(messageDate.getTime())
      ? 'unknown'
      : `${messageDate.getFullYear()}-${messageDate.getMonth() + 1}-${messageDate.getDate()}`

    // Add day separator if date changed
    if (dayKey !== lastDayKey) {
      lastDayKey = dayKey
      result.push({
        id: `sep-${dayKey}-${message.id}`,
        kind: 'separator',
        label: Number.isNaN(messageDate.getTime())
          ? 'Date inconnue'
          : messageDate.toLocaleDateString('fr-FR', {
              weekday: 'long',
              day: '2-digit',
              month: 'long',
            }),
      })
    }

    // Determine if this message should show author name (not grouped)
    const prevMessage = index > 0 ? messages.value[index - 1] : null
    const isGrouped = shouldGroupMessages(prevMessage, message)
    const showAuthor = !isGrouped && selectedConversation.value?.type === 'group' && !isOwnMessage(message)

    result.push({
      id: `msg-${message.id}`,
      kind: 'message',
      message,
      showAuthor,
      isGrouped,
    })
  })

  return result
})

const otherUsers = computed(() =>
  users.value.filter((user) => String(user.id) !== currentUserId)
)

const formatDate = (dateValue) => {
  return formatRelativeTime(dateValue)
}

const formatDateFull = (dateValue) => {
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
    return fullName || 'Utilisateur'
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
  if (document.hidden) {
    if (hideContentOnTabLoss.value) {
      contentHidden.value = true
    }
    return
  }

  contentHidden.value = false
  runAutoRefreshConversations()
  runAutoRefreshMessages()
}

const saveHideContentOnTabLoss = () => {
  if (typeof window !== 'undefined' && typeof window.localStorage !== 'undefined') {
    window.localStorage.setItem('chat_hide_on_tab_loss', hideContentOnTabLoss.value)
  }
}

// Keyboard Navigation (Accessibility)
const handleKeyboardNavigation = (event) => {
  // Reserved for future keyboard shortcuts
}

// Auto-expand composer textarea (Mobile Ergonomics)
const handleComposerInput = () => {
  if (composerTextarea.value) {
    composerTextarea.value.style.height = 'auto'
    const newHeight = Math.min(composerTextarea.value.scrollHeight, 120) // Max 5 lines (~120px)
    composerTextarea.value.style.height = newHeight + 'px'
  }
}

// Watch for search input changes with debounce (Performance)
watch(conversationSearch, () => {
  isSearching.value = true
  debouncedSearch()
})

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
  if (!selectedConversation.value || !draftMessage.value.trim() || isSendingMessage.value) return

  loadError.value = ''
  isSendingMessage.value = true
  const messageText = draftMessage.value.trim()
  const tempId = `temp-${Date.now()}`

  // 1. Optimistic Send: Add message immediately with "sending" status
  const optimisticMessage = {
    id: tempId,
    conversationId: selectedConversation.value.id,
    senderId: currentUserId,
    text: messageText,
    createdAt: new Date().toISOString(),
    status: 'sending', // 'sending', 'sent', 'failed'
    readByUserIds: [currentUserId],
  }

  messages.value = [...messages.value, optimisticMessage]
  draftMessage.value = ''

  // Scroll to bottom
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }

  // 2. Send to server
  try {
    const response = await chatApiService.sendMessage({
      conversationId: selectedConversation.value.id,
      text: messageText,
    })

    const serverMessage = response?.data || null

    // Update optimistic message with real ID and mark as sent
    const messageIndex = messages.value.findIndex((m) => m.id === tempId)
    if (messageIndex !== -1) {
      messages.value[messageIndex] = {
        ...messages.value[messageIndex],
        id: serverMessage?.id || tempId,
        status: 'sent',
        createdAt: serverMessage?.createdAt || optimisticMessage.createdAt,
      }
    }

    await refreshConversations()
  } catch (error) {
    loadError.value = error.message || 'Impossible d\'envoyer le message.'
    
    // Mark message as failed
    const messageIndex = messages.value.findIndex((m) => m.id === tempId)
    if (messageIndex !== -1) {
      messages.value[messageIndex] = {
        ...messages.value[messageIndex],
        status: 'failed',
      }
    }

    // Restore draft on failure
    draftMessage.value = messageText
  } finally {
    isSendingMessage.value = false
  }
}

const retryFailedMessage = async (tempId) => {
  const failedMessage = messages.value.find((m) => m.id === tempId)
  if (!failedMessage || failedMessage.status !== 'failed') return

  loadError.value = ''

  // Mark as retrying
  const messageIndex = messages.value.findIndex((m) => m.id === tempId)
  if (messageIndex !== -1) {
    messages.value[messageIndex] = {
      ...messages.value[messageIndex],
      status: 'sending',
    }
  }

  try {
    const response = await chatApiService.sendMessage({
      conversationId: failedMessage.conversationId,
      text: failedMessage.text,
    })

    const serverMessage = response?.data || null

    // Update with real ID and mark as sent
    if (messageIndex !== -1) {
      messages.value[messageIndex] = {
        ...messages.value[messageIndex],
        id: serverMessage?.id || tempId,
        status: 'sent',
        createdAt: serverMessage?.createdAt || failedMessage.createdAt,
      }
    }

    await refreshConversations()
  } catch (error) {
    loadError.value = error.message || 'Impossible de renvoyer le message.'
    if (messageIndex !== -1) {
      messages.value[messageIndex] = {
        ...messages.value[messageIndex],
        status: 'failed',
      }
    }
  }
}

const loadMessagingContext = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
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

const loadUsersForDirectMessages = async () => {
  if (usersLoaded.value) return

  const usersList = await userService.getAll()
  const safeUsers = Array.isArray(usersList)
    ? usersList.map((user) => ({
        id: user.id,
        firstName: user.firstName || '',
        lastName: user.lastName || '',
      }))
    : []

  users.value = safeUsers
  usersById.value = safeUsers.reduce((acc, user) => {
    acc[String(user.id)] = user
    return acc
  }, {})
  usersLoaded.value = true
}

const openNewDiscussionModal = async () => {
  try {
    await loadUsersForDirectMessages()
    openDirectModal.value = true
  } catch (error) {
    loadError.value = error.message || 'Impossible de charger les utilisateurs pour la discussion privée.'
  }
}

watch(selectedConversationId, async () => {
  await loadMessages()
})

watch(openDirectModal, (isOpen) => {
  if (!isOpen) return
  loadUsersForDirectMessages().catch(() => null)
})

onMounted(async () => {
  if (!currentUserId) return
  
  // Load confidentiality preferences
  if (typeof window !== 'undefined' && typeof window.localStorage !== 'undefined') {
    hideContentOnTabLoss.value = window.localStorage.getItem('chat_hide_on_tab_loss') === 'true'
  }
  
  await loadMessagingContext()
  startPolling()
  document.addEventListener('visibilitychange', handleVisibilityChange)
  document.addEventListener('keydown', handleKeyboardNavigation)
})

onUnmounted(() => {
  stopPolling()
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  document.removeEventListener('keydown', handleKeyboardNavigation)
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
.messaging-page {
  --msg-accent: #176b87;
  --msg-accent-soft: #e7f5fb;
  --msg-bg: #f3f7f9;
  --msg-card: #ffffff;
  --msg-text: #1f2937;
  --msg-muted: #607084;
  --msg-own: #d8f4eb;
  --msg-other: #ffffff;
  --msg-group-chip: #ecf4ff;
  background: var(--msg-bg);
}

/* Header styling like ManageMissions */
header {
  box-shadow: 0 2px 8px rgba(26, 34, 48, 0.12);
}

.messaging-shell {
  padding-bottom: 0.35rem;
}

.messaging-header {
  min-height: auto;
  padding: 0.35rem 0.25rem;
  border-radius: 0;
  background: transparent;
  border: none;
}

.hero-subtitle {
  color: var(--msg-muted);
}

.hero-sync {
  color: #0d6efd;
}

.new-discussion-btn {
  border-radius: 0.8rem;
  padding: 0.4rem 0.85rem;
  font-weight: 600;
  box-shadow: none;
  margin-top: 1rem;
}

.messaging-layout .card {
  border-radius: 1rem;
  background: var(--msg-card);
}

.conversation-card,
.chat-card {
  border: 1px solid #e1ebf2;
}

.filter-btn {
  font-weight: 600;
}

.conversation-tools .form-control {
  border-color: #d2dee9;
}

.conversation-tools .form-control:focus {
  border-color: #8ec1d4;
  box-shadow: 0 0 0 0.18rem rgba(23, 107, 135, 0.14);
}

.conversation-list {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  max-height: 70vh;
  overflow: auto;
  padding: 0.1rem;
}

.conversation-item {
  border-radius: 0.75rem;
  padding: 0.7rem 0.8rem;
  transition: background-color 0.2s ease, transform 0.15s ease;
}

.conversation-item:hover {
  background: #f4f9fc;
  transform: translateY(-1px);
}

.conversation-item.active {
  background: var(--msg-accent-soft);
  outline: 1px solid #cbe9f4;
}

.conversation-name {
  color: var(--msg-text);
}

.conversation-preview {
  max-width: 220px;
}

.chat-topbar {
  background: linear-gradient(180deg, #fafdff, #ffffff);
}

.topbar-subtitle {
  color: var(--msg-muted);
}

.group-members-bar {
  background: #f8fbff;
}

.member-chip {
  background: var(--msg-group-chip);
  color: #184766;
  border: 1px solid #d9e9ff;
}

.messages-scroll {
  background: #f8fbfd;
}

.message-day-separator {
  display: flex;
  justify-content: center;
  margin: 0.15rem 0 0.35rem;
}

.message-day-separator span {
  background: #eaf1f6;
  color: #486276;
  border: 1px solid #d6e3ec;
  border-radius: 999px;
  padding: 0.1rem 0.6rem;
  font-size: 0.72rem;
  text-transform: capitalize;
}

.composer {
  background: #fff;
}

.composer-input {
  border-radius: 999px;
  border: 1px solid #d2dee9;
  padding-left: 0.95rem;
  font-family: inherit;
  line-height: 1.4;
  min-height: 2.2rem;
}

.composer-input:focus {
  border-color: #8ec1d4;
  box-shadow: 0 0 0 0.2rem rgba(23, 107, 135, 0.15);
}

.composer-send {
  border-radius: 999px;
  font-weight: 600;
  white-space: nowrap;
  padding: 0.4rem 0.8rem;
}

/* Message Bubble: Optimistic Send & Smart Grouping */
.message-bubble-wrapper {
  display: flex;
  flex-direction: column;
  max-width: min(78%, 560px);
}

.own-wrapper {
  align-items: flex-end;
}

.other-wrapper {
  align-items: flex-start;
}

.message-bubble {
  display: block;
  width: 100%;
  max-width: 100%;
  border-radius: 0.95rem;
  padding: 0.55rem 0.75rem;
  font-size: 0.94rem;
  color: var(--msg-text);
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
  white-space: pre-wrap;
  word-break: break-word;
  overflow-wrap: anywhere;
  min-width: 0;
}

.message-bubble.message-grouped {
  padding-top: 0.35rem;
  padding-bottom: 0.35rem;
}

.message-own {
  background: var(--msg-own);
  border: 1px solid #b6e8d5;
}

.message-other {
  background: var(--msg-other);
  border: 1px solid #e2ebf2;
}

/* Message Status Indicators (Optimistic Send) */
.message-status {
  font-weight: 600;
  min-width: 1.2rem;
  text-align: right;
}

.message-status.status-sending {
  color: #ffc107;
}

.message-status.status-sent {
  color: #28a745;
}

.message-status.status-failed {
  color: #dc3545;
}

/* Author name for grouped messages */
.author-name {
  color: var(--msg-muted);
  margin-top: 0.3rem;
}

@media (max-width: 991px) {
  .messaging-header {
    min-height: auto;
    padding: 0.35rem 0.15rem;
    background: transparent;
    border: none;
  }

  .new-discussion-btn {
    width: 100%;
    margin-top: 0.45rem;
  }

  .messaging-layout .col-12.col-lg-8 .card-body {
    min-height: 400px !important;
  }

  /* Mobile: Reduce gaps and padding */
  .message-bubble-wrapper {
    max-width: 92%;
  }

  .message-bubble {
    width: 100%;
    max-width: 100%;
    padding: 0.5rem 0.6rem;
    font-size: 0.9rem;
    min-width: 0;
  }

  .composer {
    padding: 0.5rem !important;
  }

  .composer-input {
    min-height: 2.4rem;
  }

  /* Safe area support for iOS */
  .messages-scroll {
    padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
  }

  .composer {
    padding-bottom: max(0.5rem, env(safe-area-inset-bottom)) !important;
  }
}

/* Improved WCAG Contrast */
.form-check-label {
  color: #1f2937;
  font-weight: 500;
}

.text-muted {
  color: #6b7280 !important;
}

.badge {
  font-weight: 600;
  letter-spacing: 0.3px;
}

/* Focus indicators for keyboard navigation */
button:focus-visible,
input:focus-visible,
textarea:focus-visible {
  outline: 2px solid #176b87;
  outline-offset: 2px;
}

/* Reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
