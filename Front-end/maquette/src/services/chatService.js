const STORAGE_KEY = 'benerun_chat_store_v1'

const ACTIVE_AFFECTATION_STATUSES = new Set(['assigne', 'confirme', 'present'])
const ACTIVE_POSTULATION_STATUSES = new Set(['en_attente', 'accepte'])

const nowIso = () => new Date().toISOString()

const toNumberOrNull = (value) => {
  const parsed = Number(value)
  return Number.isNaN(parsed) ? null : parsed
}

const normalizeStore = (rawStore) => {
  if (!rawStore || typeof rawStore !== 'object') {
    return { conversations: [], messages: [] }
  }

  const conversations = Array.isArray(rawStore.conversations) ? rawStore.conversations : []
  const messages = Array.isArray(rawStore.messages) ? rawStore.messages : []

  return { conversations, messages }
}

const loadStore = () => {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return { conversations: [], messages: [] }
    return normalizeStore(JSON.parse(raw))
  } catch {
    return { conversations: [], messages: [] }
  }
}

const saveStore = (store) => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(normalizeStore(store)))
}

const buildDirectConversationId = (userIdA, userIdB) => {
  const sorted = [String(userIdA), String(userIdB)].sort()
  return `direct:${sorted[0]}:${sorted[1]}`
}

const buildMissionConversationId = (missionId) => `mission:${String(missionId)}`

const extractMissionPayload = (payload) => {
  const mission = payload?.mission || payload || {}

  const missionId = String(
    mission.id
    ?? mission.id_mission
    ?? payload?.id
    ?? payload?.id_mission
    ?? ''
  )

  if (!missionId) return null

  return {
    id: missionId,
    eventId: String(mission.eventId ?? mission.id_evenement ?? mission.event?.id_evenement ?? ''),
    eventName: mission.eventName ?? mission.evenement?.nom_evenement ?? 'Événement',
    name: mission.name ?? mission.titre_mission ?? `Mission #${missionId}`,
    responsibleUserId: String(
      mission.responsibleUserId
      ?? mission.responsable_utilisateur_id
      ?? mission.responsable?.id_utilisateur
      ?? ''
    ),
  }
}

const upsertConversation = (store, nextConversation, options = {}) => {
  const { replaceMemberIds = false } = options
  const index = store.conversations.findIndex((conversation) => conversation.id === nextConversation.id)
  if (index === -1) {
    store.conversations.push(nextConversation)
    return nextConversation
  }

  const nextMemberIds = replaceMemberIds
    ? (nextConversation.memberIds || []).map((memberId) => String(memberId)).filter(Boolean)
    : Array.from(new Set([
      ...(store.conversations[index].memberIds || []),
      ...(nextConversation.memberIds || []),
    ].map((memberId) => String(memberId)).filter(Boolean)))

  const merged = {
    ...store.conversations[index],
    ...nextConversation,
    memberIds: nextMemberIds,
  }

  store.conversations[index] = merged
  return merged
}

const updateConversationMessageMeta = (store, conversationId, text) => {
  const index = store.conversations.findIndex((conversation) => conversation.id === conversationId)
  if (index === -1) return

  store.conversations[index] = {
    ...store.conversations[index],
    lastMessagePreview: text,
    updatedAt: nowIso(),
  }
}

export const chatService = {
  ensureDirectConversation: (currentUserId, otherUserId) => {
    const userIdA = String(currentUserId || '')
    const userIdB = String(otherUserId || '')
    if (!userIdA || !userIdB || userIdA === userIdB) return null

    const store = loadStore()
    const conversationId = buildDirectConversationId(userIdA, userIdB)

    const conversation = upsertConversation(store, {
      id: conversationId,
      type: 'direct',
      name: null,
      missionId: null,
      eventId: null,
      memberIds: [userIdA, userIdB],
      createdAt: nowIso(),
      updatedAt: nowIso(),
      lastMessagePreview: null,
    })

    saveStore(store)
    return conversation
  },

  ensureMissionGroupConversation: (payload, createdByUserId = null) => {
    const mission = extractMissionPayload(payload)
    if (!mission) return null

    const store = loadStore()
    const conversationId = buildMissionConversationId(mission.id)

    const conversation = upsertConversation(store, {
      id: conversationId,
      type: 'group',
      name: `${mission.name} • ${mission.eventName}`,
      missionId: mission.id,
      eventId: mission.eventId || null,
      memberIds: [],
      createdAt: nowIso(),
      updatedAt: nowIso(),
      lastMessagePreview: null,
    })

    saveStore(store)
    return conversation
  },

  syncMissionGroups: (missions = [], affectations = [], postulations = [], currentUserId = null) => {
    const store = loadStore()
    const nextByMission = new Map()
    const missionIds = new Set()

    for (const mission of missions) {
      const missionId = String(mission.id ?? mission.id_mission ?? '')
      if (!missionId) continue
      missionIds.add(missionId)

      const eventName = mission.eventName ?? mission.evenement?.nom_evenement ?? 'Événement'
      const missionName = mission.name ?? mission.titre_mission ?? `Mission #${missionId}`

      nextByMission.set(missionId, {
        missionId,
        eventId: String(mission.eventId ?? mission.id_evenement ?? ''),
        name: `${missionName} • ${eventName}`,
        memberIds: new Set(),
      })
    }

    for (const affectation of affectations) {
      const missionId = String(affectation.id_mission ?? '')
      if (!missionId || !nextByMission.has(missionId)) continue
      if (!ACTIVE_AFFECTATION_STATUSES.has(String(affectation.statut_affectation || '').toLowerCase())) continue

      const userId = String(affectation.id_utilisateur ?? '')
      if (!userId) continue

      nextByMission.get(missionId).memberIds.add(userId)
    }

    for (const postulation of postulations) {
      const missionId = String(postulation.id_mission ?? '')
      if (!missionId || !nextByMission.has(missionId)) continue
      if (!ACTIVE_POSTULATION_STATUSES.has(String(postulation.statut_postulation || '').toLowerCase())) continue

      const userId = String(postulation.id_utilisateur ?? '')
      if (!userId) continue

      nextByMission.get(missionId).memberIds.add(userId)
    }

    for (const missionEntry of nextByMission.values()) {
      upsertConversation(store, {
        id: buildMissionConversationId(missionEntry.missionId),
        type: 'group',
        name: missionEntry.name,
        missionId: missionEntry.missionId,
        eventId: missionEntry.eventId || null,
        memberIds: Array.from(missionEntry.memberIds),
        createdAt: nowIso(),
        updatedAt: nowIso(),
      }, { replaceMemberIds: true })
    }

    store.conversations = store.conversations.filter((conversation) => {
      if (conversation.type !== 'group') return true
      const missionId = String(conversation.missionId || '').trim()
      if (!missionId) return false
      return missionIds.has(missionId)
    })

    saveStore(store)
  },

  addUserToMissionGroup: ({ missionId, eventId = null, missionName = null, eventName = null, userId }) => {
    const normalizedMissionId = String(missionId || '')
    const normalizedUserId = String(userId || '')
    if (!normalizedMissionId || !normalizedUserId) return null

    const store = loadStore()
    const conversation = upsertConversation(store, {
      id: buildMissionConversationId(normalizedMissionId),
      type: 'group',
      name: missionName && eventName
        ? `${missionName} • ${eventName}`
        : (missionName || `Mission #${normalizedMissionId}`),
      missionId: normalizedMissionId,
      eventId: eventId ? String(eventId) : null,
      memberIds: [normalizedUserId],
      createdAt: nowIso(),
      updatedAt: nowIso(),
    })

    saveStore(store)
    return conversation
  },

  getConversationsForUser: (userId) => {
    const normalizedUserId = String(userId || '')
    if (!normalizedUserId) return []

    const store = loadStore()
    return store.conversations
      .filter((conversation) => (conversation.memberIds || []).map(String).includes(normalizedUserId))
      .sort((a, b) => {
        const timeA = new Date(a.updatedAt || a.createdAt || 0).getTime()
        const timeB = new Date(b.updatedAt || b.createdAt || 0).getTime()
        return timeB - timeA
      })
  },

  getMessagesForConversation: (conversationId) => {
    const store = loadStore()
    return store.messages
      .filter((message) => message.conversationId === conversationId)
      .sort((a, b) => new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime())
  },

  sendMessage: ({ conversationId, senderId, text }) => {
    const safeText = String(text || '').trim()
    if (!safeText) return null

    const store = loadStore()
    const conversation = store.conversations.find((entry) => entry.id === conversationId)
    if (!conversation) return null

    const sender = String(senderId || '')
    if (!sender || !(conversation.memberIds || []).map(String).includes(sender)) return null

    const message = {
      id: `msg:${Date.now()}:${Math.random().toString(36).slice(2, 8)}`,
      conversationId,
      senderId: sender,
      text: safeText,
      createdAt: nowIso(),
    }

    store.messages.push(message)
    updateConversationMessageMeta(store, conversationId, safeText)
    saveStore(store)

    return message
  },

  removeCurrentUserFromAllConversations: (userId) => {
    const normalizedUserId = String(userId || '')
    if (!normalizedUserId) return

    const store = loadStore()
    store.conversations = store.conversations
      .map((conversation) => ({
        ...conversation,
        memberIds: (conversation.memberIds || []).map(String).filter((memberId) => memberId !== normalizedUserId),
      }))
      .filter((conversation) => (conversation.memberIds || []).length > 0)

    saveStore(store)
  },

  toId: toNumberOrNull,
}

export default chatService
