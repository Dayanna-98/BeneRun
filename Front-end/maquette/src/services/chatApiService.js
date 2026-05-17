import api from './api'

const toApiError = (error, fallbackMessage) => {
  if (!error?.response) {
    return { message: 'Serveur API inaccessible. Vérifiez que Laravel tourne sur http://localhost:8000.' }
  }

  return error.response.data || { message: fallbackMessage }
}

const mapUser = (raw) => ({
  id: String(raw?.id || ''),
  firstName: raw?.firstName || '',
  lastName: raw?.lastName || '',
  email: raw?.email || '',
  role: raw?.role || '',
})

const mapConversation = (raw) => ({
  id: String(raw?.id || ''),
  type: raw?.type || 'direct',
  name: raw?.name || null,
  missionId: raw?.missionId ? String(raw.missionId) : null,
  memberIds: Array.isArray(raw?.memberIds) ? raw.memberIds.map((id) => String(id)) : [],
  members: Array.isArray(raw?.members) ? raw.members.map(mapUser) : [],
  lastMessagePreview: raw?.lastMessagePreview || null,
  lastMessageAt: raw?.lastMessageAt || null,
  updatedAt: raw?.updatedAt || null,
  createdAt: raw?.createdAt || null,
  unreadCount: Number(raw?.unreadCount || 0),
})

const mapMessage = (raw) => ({
  id: String(raw?.id || ''),
  conversationId: String(raw?.conversationId || ''),
  senderId: String(raw?.senderId || ''),
  type: raw?.type || 'text',
  text: raw?.text || '',
  createdAt: raw?.createdAt || null,
  sender: raw?.sender ? mapUser(raw.sender) : null,
  readByUserIds: Array.isArray(raw?.readByUserIds) ? raw.readByUserIds.map((id) => String(id)) : [],
})

export const chatApiService = {
  listConversations: async () => {
    try {
      const response = await api.get('/conversations')
      const rows = Array.isArray(response.data) ? response.data : []
      return rows.map(mapConversation)
    } catch (error) {
      throw toApiError(error, 'Impossible de charger les conversations.')
    }
  },

  startDirectConversation: async (otherUserId) => {
    try {
      const response = await api.post('/conversations/direct', {
        other_user_id: Number(otherUserId),
      })

      return {
        ...response.data,
        conversation: mapConversation(response.data?.conversation),
      }
    } catch (error) {
      throw toApiError(error, 'Impossible de demarrer la discussion privee.')
    }
  },

  ensureMissionConversation: async ({ missionId, name = null, participantIds = [] }) => {
    try {
      const response = await api.post('/conversations/mission', {
        mission_id: Number(missionId),
        name,
        participant_ids: participantIds.map((id) => Number(id)).filter((id) => !Number.isNaN(id)),
      })

      return {
        ...response.data,
        conversation: mapConversation(response.data?.conversation),
      }
    } catch (error) {
      throw toApiError(error, 'Impossible de creer ou recuperer le groupe mission.')
    }
  },

  addParticipant: async ({ conversationId, userId }) => {
    try {
      const response = await api.post(`/conversations/${conversationId}/participants`, {
        user_id: Number(userId),
      })

      return {
        ...response.data,
        conversation: mapConversation(response.data?.conversation),
      }
    } catch (error) {
      throw toApiError(error, 'Impossible d\'ajouter le participant.')
    }
  },

  listMessages: async (conversationId) => {
    try {
      const response = await api.get(`/conversations/${conversationId}/messages`)
      const rows = Array.isArray(response.data) ? response.data : []
      return rows.map(mapMessage)
    } catch (error) {
      throw toApiError(error, 'Impossible de charger les messages.')
    }
  },

  sendMessage: async ({ conversationId, text, type = 'text' }) => {
    try {
      const response = await api.post(`/conversations/${conversationId}/messages`, {
        text,
        type,
      })

      return {
        ...response.data,
        data: mapMessage(response.data?.data),
      }
    } catch (error) {
      throw toApiError(error, 'Impossible d\'envoyer le message.')
    }
  },

  markMessageRead: async (messageId) => {
    try {
      const response = await api.post(`/messages/${messageId}/read`)
      return response.data
    } catch (error) {
      throw toApiError(error, 'Impossible de marquer le message comme lu.')
    }
  },
}

export default chatApiService
