<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { UserRound } from 'lucide-vue-next'
import BottomNav from '@/components/ui/BottomNav.vue'
import NotificationBell from '@/components/notifications/NotificationBell.vue'

const route = useRoute()
const router = useRouter()
const isLoggedIn = ref(!!localStorage.getItem('token'))

// Écoute les changements de token dans localStorage
onMounted(() => {
  // Vérifie au chargement
  isLoggedIn.value = !!localStorage.getItem('token')

  // Écoute les changements de storage (depuis d'autres onglets)
  window.addEventListener('storage', () => {
    isLoggedIn.value = !!localStorage.getItem('token')
  })

  // Écoute les changements de localStorage dans le même onglet
  const originalSetItem = localStorage.setItem
  localStorage.setItem = function(key, value) {
    originalSetItem.apply(this, arguments)
    if (key === 'token') {
      isLoggedIn.value = !!value
    }
  }

  const originalRemoveItem = localStorage.removeItem
  localStorage.removeItem = function(key) {
    originalRemoveItem.apply(this, arguments)
    if (key === 'token') {
      isLoggedIn.value = false
    }
  }
})

// Affiche BottomNav seulement sur les routes protégées ET si connecté
const isProtectedRoute = computed(() => route.meta.requiresAuth === true)
const showLayout = computed(() => isLoggedIn.value && isProtectedRoute.value)
const showProfileQuickAccess = computed(() => showLayout.value && route.path !== '/profile' && route.path !== '/profile/edit')
</script>

<template>
  <div>
    <div v-if="showProfileQuickAccess" class="quick-actions">
      <NotificationBell />
      <button
        type="button"
        class="profile-quick-access"
        @click="router.push('/profile')">
        <UserRound style="width:15px;height:15px" />
        <span>Profil</span>
      </button>
    </div>
    <RouterView />
    <BottomNav v-if="showLayout" />
  </div>
</template>

<style scoped>
.quick-actions {
  position: fixed;
  top: 14px;
  right: 14px;
  z-index: 1060;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.profile-quick-access {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 999px;
  border: none;
  background: rgba(255,255,255,0.88);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 4px 16px rgba(44,53,73,0.18), 0 1px 4px rgba(44,53,73,0.10), 0 0 0 1px rgba(44,53,73,0.07);
  font-size: 0.8rem;
  font-weight: 600;
  color: #2C3549;
  cursor: pointer;
  transition: all 200ms cubic-bezier(0.4,0,0.2,1);
}
.profile-quick-access:hover {
  background: rgba(255,255,255,0.98);
  box-shadow: 0 6px 24px rgba(44,53,73,0.24), 0 2px 6px rgba(44,53,73,0.12);
  transform: translateY(-1px);
}
.profile-quick-access:active { transform: scale(0.95); }
</style>