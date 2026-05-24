<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { UserRound, Heart } from 'lucide-vue-next'
import BottomNav from '@/components/ui/BottomNav.vue'
import NotificationBell from '@/components/notifications/NotificationBell.vue'
import TutorialGuide from '@/components/tutorial/TutorialGuide.vue'
import TutorialButton from '@/components/tutorial/TutorialButton.vue'
import { useTutorial } from '@/composables/useTutorial'

const route = useRoute()
const router = useRouter()
const isLoggedIn = ref(!!localStorage.getItem('token'))
const { checkRoleChange } = useTutorial()

// Écoute les changements de token dans localStorage
onMounted(() => {
  // Vérifie au chargement
  isLoggedIn.value = !!localStorage.getItem('token')

  // Détection de changement de rôle (affiche guide automatiquement si le rôle a changé)
  if (isLoggedIn.value) {
    checkRoleChange()
  }

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
const showProfileQuickAccess = computed(() =>
  showLayout.value
  && route.path !== '/profile'
  && route.path !== '/profile/edit'
)
const showFavoritesQuickAccess = computed(() => showLayout.value && route.path !== '/favorites')
const isDashboardRoute = computed(() => route.path === '/')
</script>

<template>
  <div
    class="app-shell"
    :class="{
      'app-shell--authenticated': showLayout,
      'app-shell--with-nav': showLayout,
      'app-shell--with-quick-actions': showProfileQuickAccess,
      'app-shell--dashboard-flush': showProfileQuickAccess && isDashboardRoute,
    }">
    <div v-if="showLayout" class="app-shell__aurora app-shell__aurora--one" />
    <div v-if="showLayout" class="app-shell__aurora app-shell__aurora--two" />
    <div v-if="showProfileQuickAccess" class="quick-actions">
      <NotificationBell />
      <TutorialButton />
      <button
        v-if="showFavoritesQuickAccess"
        type="button"
        class="quick-icon-access"
        @click="router.push('/favorites')"
        aria-label="Accéder aux favoris"
        title="Favoris">
        <Heart style="width:16px;height:16px" />
      </button>
      <button
        type="button"
        class="profile-quick-access"
        @click="router.push('/profile')">
        <UserRound style="width:15px;height:15px" />
        <span>Profil</span>
      </button>
    </div>
    <main class="app-shell__content">
      <RouterView />
    </main>
    <BottomNav v-if="showLayout" />
    <TutorialGuide />
  </div>
</template>

<style scoped>
.app-shell {
  min-height: 100vh;
  position: relative;
}

.app-shell__content {
  min-height: 100vh;
  position: relative;
  z-index: 1;
}

.app-shell--authenticated {
  background:
    radial-gradient(circle at top left, rgba(197, 216, 46, 0.12), transparent 28%),
    radial-gradient(circle at top right, rgba(61, 78, 106, 0.12), transparent 24%),
    linear-gradient(180deg, rgba(22, 32, 45, 0.1) 0%, rgba(242,243,247,0) 22%);
}

.app-shell__aurora {
  position: fixed;
  pointer-events: none;
  border-radius: 999px;
  filter: blur(18px);
  opacity: 0.7;
  z-index: 0;
}

.app-shell__aurora--one {
  top: 72px;
  left: -44px;
  width: 180px;
  height: 180px;
  background: radial-gradient(circle, rgba(197, 216, 46, 0.22) 0%, rgba(197, 216, 46, 0) 72%);
}

.app-shell__aurora--two {
  right: -38px;
  bottom: 98px;
  width: 220px;
  height: 220px;
  background: radial-gradient(circle, rgba(44, 53, 73, 0.14) 0%, rgba(44, 53, 73, 0) 72%);
}

.app-shell--with-quick-actions .app-shell__content {
  padding-top: 0;
}

.app-shell--dashboard-flush .app-shell__content {
  padding-top: 0;
}

.app-shell--with-nav .app-shell__content {
  padding-bottom: 96px;
}

.quick-actions {
  position: fixed;
  top: 12px;
  right: 12px;
  z-index: 1060;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  max-width: calc(100vw - 24px);
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
  white-space: nowrap;
}
.profile-quick-access:hover {
  background: rgba(255,255,255,0.98);
  box-shadow: 0 6px 24px rgba(44,53,73,0.24), 0 2px 6px rgba(44,53,73,0.12);
  transform: translateY(-1px);
}
.profile-quick-access:active { transform: scale(0.95); }

.quick-icon-access {
  width: 46px;
  height: 46px;
  border-radius: 999px;
  border: none;
  background: rgba(255,255,255,0.88);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 4px 16px rgba(44,53,73,0.18), 0 1px 4px rgba(44,53,73,0.10), 0 0 0 1px rgba(44,53,73,0.07);
  color: #2C3549;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 200ms cubic-bezier(0.4,0,0.2,1);
}

.quick-icon-access:hover {
  background: rgba(255,255,255,0.98);
  box-shadow: 0 6px 24px rgba(44,53,73,0.24), 0 2px 6px rgba(44,53,73,0.12);
  transform: translateY(-1px);
}

.quick-icon-access:active {
  transform: scale(0.95);
}

@media (max-width: 576px) {
  .app-shell--with-quick-actions .app-shell__content {
    padding-top: 0;
  }

  .app-shell--dashboard-flush .app-shell__content {
    padding-top: 0;
  }

  .app-shell--with-nav .app-shell__content {
    padding-bottom: 104px;
  }

  .quick-actions {
    left: 12px;
    right: 12px;
  }

  .profile-quick-access {
    padding: 7px 12px;
    font-size: 0.76rem;
  }

  .app-shell__aurora--one {
    width: 140px;
    height: 140px;
    top: 96px;
  }

  .app-shell__aurora--two {
    width: 170px;
    height: 170px;
    bottom: 110px;
  }
}
</style>