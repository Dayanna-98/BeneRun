<template>
  <div class="notification-bell" ref="rootRef">
    <button type="button" class="notification-bell__trigger" @click="toggleOpen">
      <Bell style="width:16px;height:16px" />
      <span v-if="unreadCount > 0" class="notification-bell__badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
    </button>

    <div v-if="isOpen" class="notification-bell__panel card border-0 shadow-lg animate-scale-in">
      <div class="card-header">
        <div class="d-flex align-items-center justify-content-between gap-2">
          <span class="fw-semibold">Centre de notifications</span>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" @click="refreshFeed">
              Actualiser
            </button>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" :disabled="unreadCount === 0" @click="markAllAsRead">
              Tout lire
            </button>
          </div>
        </div>
        <div class="notification-bell__meta small text-muted mt-1">
          {{ unreadCount }} non lue(s) sur {{ totalCount }}
        </div>
      </div>

      <div class="notification-bell__toolbar p-2 border-bottom d-flex gap-2 flex-wrap">
        <button
          type="button"
          class="btn btn-sm"
          :class="activeScope === 'all' ? 'btn-primary' : 'btn-outline-secondary'"
          @click="activeScope = 'all'">
          Toutes
        </button>
        <button
          type="button"
          class="btn btn-sm"
          :class="activeScope === 'unread' ? 'btn-primary' : 'btn-outline-secondary'"
          @click="activeScope = 'unread'">
          Non lues
        </button>
        <select v-model="activeType" class="form-select form-select-sm notification-bell__type-filter">
          <option value="all">Tous les types</option>
          <option v-for="type in availableTypes" :key="type" :value="type">
            {{ formatTypeLabel(type) }}
          </option>
        </select>
        <button
          type="button"
          class="btn btn-sm btn-outline-secondary"
          :disabled="activeType === 'all' || unreadCountByType(activeType) === 0"
          @click="markSelectedTypeAsRead">
          Lire ce type
        </button>
      </div>

      <div class="notification-bell__quick-actions p-2 border-bottom" v-if="quickActions.length > 0">
        <div class="x-small text-muted mb-2">Actions rapides</div>
        <div class="d-flex gap-2 flex-wrap">
          <button
            v-for="action in quickActions"
            :key="action.key"
            type="button"
            class="btn btn-sm btn-outline-primary"
            @click="runQuickAction(action)">
            {{ action.label }}
          </button>
        </div>
      </div>

      <div class="notification-bell__content">
        <div v-if="isLoading" class="p-3">
          <div class="skeleton" style="height:56px;width:100%" />
          <div class="skeleton mt-2" style="height:56px;width:100%" />
          <div class="skeleton mt-2" style="height:56px;width:100%" />
        </div>

        <div v-else-if="loadError" class="p-3">
          <div class="alert alert-danger small mb-2">{{ loadError }}</div>
          <button type="button" class="btn btn-sm btn-outline-secondary" @click="refreshFeed">Réessayer</button>
        </div>

        <div v-else-if="filteredItems.length === 0" class="p-3">
          <div class="small text-muted text-center py-3">Aucune notification pour ce filtre.</div>
        </div>

        <template v-else>
          <div v-for="group in groupedItems" :key="group.key" class="notification-bell__group">
            <div class="notification-bell__group-header">
              <span>{{ group.label }}</span>
              <span class="x-small text-muted">{{ group.items.length }}</span>
            </div>

            <button
              v-for="item in group.items"
              :key="item.id"
              type="button"
              class="notification-bell__item"
              :class="{ 'notification-bell__item--unread': !item.is_read }"
              @click="openItem(item)">
              <div class="notification-bell__item-main">
                <div class="notification-bell__icon" :class="`notification-bell__icon--${item.level || 'info'}`">
                  <component :is="iconFor(item)" style="width:13px;height:13px" />
                </div>
                <div class="flex-grow-1 min-w-0">
                  <div class="notification-bell__item-header">
                    <span class="fw-semibold small text-truncate">{{ item.title }}</span>
                    <span class="x-small text-muted">{{ formatWhen(item.created_at) }}</span>
                  </div>
                  <div class="small text-muted text-start notification-bell__body">{{ item.body }}</div>
                  <div class="notification-bell__item-footer">
                    <span class="badge rounded-pill bg-light text-dark border">{{ formatTypeLabel(item.type) }}</span>
                    <span v-if="!item.is_read" class="notification-bell__dot" aria-hidden="true" />
                    <span class="x-small text-primary fw-semibold">{{ item.action_label || 'Ouvrir' }}</span>
                  </div>
                </div>
              </div>
              <button
                v-if="!item.is_read"
                type="button"
                class="btn btn-link btn-sm p-0 notification-bell__mark"
                title="Marquer comme lue"
                @click.stop="markItemAsRead(item)">
                Marquer lue
              </button>
            </button>
          </div>
        </template>
      </div>

      <div class="notification-bell__footer border-top p-2 small text-muted d-flex justify-content-between align-items-center">
        <span>{{ filteredItems.length }} élément(s) affiché(s)</span>
        <span v-if="lastUpdatedAt">Maj {{ formatWhen(lastUpdatedAt) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Bell, CheckCircle2, Clock3, CircleAlert, Info } from 'lucide-vue-next'
import notificationService from '@/services/notificationService'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const toast = useToast()
const rootRef = ref(null)
const isOpen = ref(false)
const isLoading = ref(false)
const items = ref([])
const totalCount = ref(0)
const activeScope = ref('all')
const activeType = ref('all')
const loadError = ref('')
const lastUpdatedAt = ref('')
const sessionSeenIds = ref(new Set())
let pollTimer = null

const unreadCount = computed(() => items.value.filter((item) => !item?.is_read).length)
const availableTypes = computed(() => Array.from(new Set(items.value.map((item) => item?.type).filter(Boolean))))
const filteredItems = computed(() => {
  let next = [...items.value]

  if (activeScope.value === 'unread') {
    next = next.filter((item) => !item?.is_read)
  }

  if (activeType.value !== 'all') {
    next = next.filter((item) => item?.type === activeType.value)
  }

  return next
})

const groupedItems = computed(() => {
  const buckets = {
    today: [],
    yesterday: [],
    week: [],
    older: [],
  }

  for (const item of filteredItems.value) {
    const key = computeTimeGroupKey(item?.created_at)
    buckets[key].push(item)
  }

  const order = [
    { key: 'today', label: 'Aujourd\'hui' },
    { key: 'yesterday', label: 'Hier' },
    { key: 'week', label: 'Cette semaine' },
    { key: 'older', label: 'Plus ancien' },
  ]

  return order
    .map(({ key, label }) => ({ key, label, items: buckets[key] }))
    .filter((group) => group.items.length > 0)
})

const quickActions = computed(() => {
  const actions = []
  const hasPending = filteredItems.value.some((item) => item?.type === 'pending_postulation')
  const hasStatus = filteredItems.value.some((item) => item?.type === 'postulation_status')
  const hasAffectation = filteredItems.value.some((item) => item?.type === 'affectation')

  if (hasPending) {
    actions.push({ key: 'pending', label: 'Ouvrir les demandes en attente', to: '/manage-events?openQueue=1' })
  }
  if (hasStatus) {
    actions.push({ key: 'status', label: 'Voir mes inscriptions', to: '/my-missions' })
  }
  if (hasAffectation) {
    actions.push({ key: 'affectation', label: 'Voir mes missions assignées', to: '/my-missions' })
  }

  return actions
})

const formatWhen = (value) => {
  if (!value) return 'à l’instant'
  const date = new Date(value)
  return date.toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const formatTypeLabel = (type) => {
  if (!type) return 'Générale'

  return {
    pending_postulation: 'Demandes',
    postulation_status: 'Inscriptions',
    affectation: 'Missions',
  }[type] || type
}

const iconFor = (item) => {
  if (item?.type === 'pending_postulation') return Clock3
  if (item?.type === 'affectation') return CheckCircle2
  if (item?.level === 'danger') return CircleAlert
  if (item?.level === 'success') return CheckCircle2
  return Info
}

const unreadCountByType = (type) => {
  if (!type || type === 'all') return unreadCount.value
  return items.value.filter((item) => item?.type === type && !item?.is_read).length
}

const markSelectedTypeAsRead = async () => {
  if (activeType.value === 'all') return

  const unreadIds = items.value
    .filter((item) => item?.type === activeType.value)
    .filter((item) => !item?.is_read)
    .map((item) => item?.id)
    .filter(Boolean)

  if (!unreadIds.length) return

  const previous = [...items.value]
  items.value = items.value.map((item) => (
    item.type === activeType.value
      ? { ...item, is_read: true, read_at: item.read_at || new Date().toISOString() }
      : item
  ))

  try {
    await notificationService.markAsRead(unreadIds)
  } catch {
    items.value = previous
  }
}

const runQuickAction = async (action) => {
  if (!action?.to) return
  isOpen.value = false
  await router.push(action.to)
}

const computeTimeGroupKey = (value) => {
  if (!value) return 'older'

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return 'older'

  const now = new Date()
  const startToday = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const startTarget = new Date(date.getFullYear(), date.getMonth(), date.getDate())
  const diffDays = Math.floor((startToday.getTime() - startTarget.getTime()) / (1000 * 60 * 60 * 24))

  if (diffDays <= 0) return 'today'
  if (diffDays === 1) return 'yesterday'
  if (diffDays <= 7) return 'week'
  return 'older'
}

const markAllAsRead = async () => {
  const unreadIds = items.value
    .filter((item) => !item?.is_read)
    .map((item) => item?.id)
    .filter(Boolean)

  if (!unreadIds.length) return

  const previous = [...items.value]
  items.value = items.value.map((item) => ({ ...item, is_read: true, read_at: item.read_at || new Date().toISOString() }))

  try {
    await notificationService.markAsRead(unreadIds)
  } catch {
    items.value = previous
  }
}

const markItemAsRead = async (item) => {
  if (!item?.id || item?.is_read) return

  items.value = items.value.map((entry) =>
    entry.id === item.id
      ? { ...entry, is_read: true, read_at: new Date().toISOString() }
      : entry
  )

  try {
    await notificationService.markAsRead([item.id])
  } catch {
    await refreshFeed()
  }
}

const openItem = async (item) => {
  await markItemAsRead(item)
  isOpen.value = false
  if (item.href) router.push(item.href)
}

const toggleOpen = () => {
  isOpen.value = !isOpen.value
}

const handleDocumentClick = (event) => {
  if (!rootRef.value?.contains(event.target)) {
    isOpen.value = false
  }
}

const fetchNotifications = async () => {
  isLoading.value = items.value.length === 0
  loadError.value = ''

  try {
    const payload = await notificationService.getFeed()
    const nextItems = Array.isArray(payload?.items) ? payload.items : []

    totalCount.value = Number(payload?.count || nextItems.length || 0)
    lastUpdatedAt.value = new Date().toISOString()

    let shownToasts = 0
    for (const item of nextItems) {
      if (!sessionSeenIds.value.has(item.id)) {
        sessionSeenIds.value.add(item.id)
        if (!item?.is_read && shownToasts < 2) {
          toast.info(`${item.title} - ${item.action_label || 'Ouvrir le centre de notifications'}`)
          shownToasts += 1
        }
      }
    }
    items.value = nextItems
  } catch {
    loadError.value = 'Impossible de récupérer les notifications pour le moment.'
  } finally {
    isLoading.value = false
  }
}

const refreshFeed = async () => {
  await fetchNotifications()
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
  fetchNotifications()
  pollTimer = window.setInterval(fetchNotifications, 30000)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
  if (pollTimer) window.clearInterval(pollTimer)
})
</script>

<style scoped>
.notification-bell {
  position: relative;
}

.notification-bell__trigger {
  position: relative;
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 999px;
  background: rgba(255,255,255,0.88);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 4px 16px rgba(44,53,73,0.18), 0 1px 4px rgba(44,53,73,0.10), 0 0 0 1px rgba(44,53,73,0.07);
  color: var(--primary);
}

.notification-bell__badge {
  position: absolute;
  top: -2px;
  right: -2px;
  min-width: 18px;
  height: 18px;
  border-radius: 999px;
  padding: 0 5px;
  display: grid;
  place-items: center;
  font-size: 0.65rem;
  font-weight: 700;
  background: var(--accent);
  color: var(--primary-dark);
}

.notification-bell__panel {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: min(95vw, 430px);
  overflow: hidden;
}

.notification-bell__meta {
  line-height: 1.2;
}

.notification-bell__toolbar {
  background: rgba(44,53,73,0.03);
}

.notification-bell__quick-actions {
  background: rgba(13,110,253,0.05);
}

.notification-bell__type-filter {
  min-width: 148px;
  flex: 1;
}

.notification-bell__content {
  max-height: 420px;
  overflow-y: auto;
}

.notification-bell__group + .notification-bell__group {
  border-top: 1px solid rgba(44,53,73,0.06);
}

.notification-bell__group-header {
  position: sticky;
  top: 0;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  background: #f7f8fb;
  color: #44506a;
  border-bottom: 1px solid rgba(44,53,73,0.06);
}

.notification-bell__item {
  width: 100%;
  border: none;
  background: transparent;
  padding: 12px 14px;
  text-align: left;
  border-top: 1px solid rgba(44,53,73,0.06);
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.notification-bell__item:hover {
  background: rgba(44,53,73,0.03);
}

.notification-bell__item--unread {
  background: rgba(197,216,46,0.12);
}

.notification-bell__item-main {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  min-width: 0;
  width: 100%;
}

.notification-bell__icon {
  width: 24px;
  height: 24px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 1px;
}

.notification-bell__icon--info {
  background: rgba(13,110,253,0.14);
  color: #0d6efd;
}

.notification-bell__icon--success {
  background: rgba(25,135,84,0.14);
  color: #198754;
}

.notification-bell__icon--warning {
  background: rgba(255,193,7,0.2);
  color: #9a6b00;
}

.notification-bell__icon--danger {
  background: rgba(220,53,69,0.15);
  color: #b42333;
}

.notification-bell__item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 4px;
}

.notification-bell__body {
  line-height: 1.35;
}

.notification-bell__item-footer {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 7px;
}

.notification-bell__dot {
  width: 7px;
  height: 7px;
  border-radius: 999px;
  background: #0d6efd;
}

.notification-bell__mark {
  white-space: nowrap;
}

.notification-bell__footer {
  background: rgba(44,53,73,0.03);
}
</style>