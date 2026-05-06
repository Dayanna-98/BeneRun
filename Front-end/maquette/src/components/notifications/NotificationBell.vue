<template>
  <div class="notification-bell" ref="rootRef">
    <button type="button" class="notification-bell__trigger" @click="toggleOpen">
      <Bell style="width:16px;height:16px" />
      <span v-if="unreadCount > 0" class="notification-bell__badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
    </button>

    <div v-if="isOpen" class="notification-bell__panel card border-0 shadow-lg animate-scale-in">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span>Notifications</span>
        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" @click="markAllAsRead">
          Tout lire
        </button>
      </div>

      <div class="notification-bell__content">
        <div v-if="isLoading" class="p-3">
          <div class="skeleton" style="height:56px;width:100%" />
          <div class="skeleton mt-2" style="height:56px;width:100%" />
        </div>

        <div v-else-if="items.length === 0" class="p-3">
          <div class="small text-muted text-center py-3">Aucune notification récente.</div>
        </div>

        <button
          v-for="item in items"
          v-else
          :key="item.id"
          type="button"
          class="notification-bell__item"
          @click="openItem(item)">
          <div class="notification-bell__item-header">
            <span class="fw-semibold small">{{ item.title }}</span>
            <span class="x-small text-muted">{{ formatWhen(item.created_at) }}</span>
          </div>
          <div class="small text-muted text-start">{{ item.body }}</div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Bell } from 'lucide-vue-next'
import notificationService from '@/services/notificationService'
import { getCurrentUser } from '@/utils/auth'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const toast = useToast()
const rootRef = ref(null)
const isOpen = ref(false)
const isLoading = ref(false)
const items = ref([])
const sessionSeenIds = ref(new Set())
const currentUser = getCurrentUser()
const storageKey = `benerun_notifications_seen_at_${currentUser?.id || 'guest'}`
const lastSeenAt = ref(localStorage.getItem(storageKey) || '')
let pollTimer = null

const unreadCount = computed(() => {
  if (!lastSeenAt.value) return items.value.length
  const seenAt = new Date(lastSeenAt.value).getTime()
  return items.value.filter((item) => new Date(item.created_at).getTime() > seenAt).length
})

const formatWhen = (value) => {
  if (!value) return 'à l’instant'
  const date = new Date(value)
  return date.toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const markAllAsRead = () => {
  const nowIso = new Date().toISOString()
  lastSeenAt.value = nowIso
  localStorage.setItem(storageKey, nowIso)
}

const openItem = (item) => {
  markAllAsRead()
  isOpen.value = false
  if (item.href) router.push(item.href)
}

const toggleOpen = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) markAllAsRead()
}

const handleDocumentClick = (event) => {
  if (!rootRef.value?.contains(event.target)) {
    isOpen.value = false
  }
}

const fetchNotifications = async () => {
  if (!currentUser) return
  isLoading.value = items.value.length === 0

  try {
    const nextItems = await notificationService.getFeed()
    for (const item of nextItems) {
      if (!sessionSeenIds.value.has(item.id)) {
        sessionSeenIds.value.add(item.id)
        if (lastSeenAt.value && new Date(item.created_at).getTime() > new Date(lastSeenAt.value).getTime()) {
          toast.info(item.title)
        }
      }
    }
    items.value = nextItems
  } catch {
    // Silent polling failure: bell should not break the app shell.
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
  fetchNotifications()
  pollTimer = window.setInterval(fetchNotifications, 45000)
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
  width: min(92vw, 360px);
  overflow: hidden;
}

.notification-bell__content {
  max-height: 360px;
  overflow-y: auto;
}

.notification-bell__item {
  width: 100%;
  border: none;
  background: transparent;
  padding: 12px 14px;
  text-align: left;
  border-top: 1px solid rgba(44,53,73,0.06);
}

.notification-bell__item:hover {
  background: rgba(44,53,73,0.03);
}

.notification-bell__item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 4px;
}
</style>