<template>
  <nav v-show="!hideForOverlay" class="bottom-nav">
    <div class="bottom-nav-inner">
      <button
        v-for="item in navItems"
        :key="item.path"
        class="nav-btn"
        :class="{ 'nav-btn--active': isActive(item.path) }"
        :aria-label="item.label"
        @click="router.push(item.path)">
        <div class="nav-btn__icon-wrap">
          <div v-if="isActive(item.path)" class="nav-btn__pill" />
          <component :is="item.icon" class="nav-btn__icon" />
        </div>
        <span class="nav-btn__label">{{ item.label }}</span>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Home, Calendar, Briefcase, MessageCircle, ListCheck } from 'lucide-vue-next'

const router = useRouter()
const route  = useRoute()
const hideForOverlay = ref(false)
let observer = null

const navItems = [
  { path: '/',           icon: Home,         label: 'Accueil'    },
  { path: '/missions',   icon: ListCheck,    label: 'Missions'   },
  { path: '/events',     icon: Calendar,     label: 'Événements' },
  { path: '/my-missions',icon: Briefcase,    label: 'En cours'   },
  { path: '/messagerie', icon: MessageCircle,label: 'Messages'   },
]

const isActive = (path) => {
  if (path === '/my-missions' && route.path.startsWith('/my-managed-missions')) {
    return true
  }

  if (path === '/') return route.path === '/'
  return route.path === path || route.path.startsWith(`${path}/`)
}

function refreshOverlayState() {
  // Hide bottom nav while any modal/backdrop overlay is visible.
  hideForOverlay.value = !!document.querySelector(
    '.modal-backdrop-custom, .modal-backdrop.show, .modal.show, [data-hide-bottom-nav="true"]'
  )
}

onMounted(() => {
  refreshOverlayState()

  observer = new MutationObserver(() => {
    refreshOverlayState()
  })

  observer.observe(document.body, {
    subtree: true,
    childList: true,
    attributes: true,
    attributeFilter: ['class', 'style', 'data-hide-bottom-nav'],
  })
})

onBeforeUnmount(() => {
  if (observer) {
    observer.disconnect()
    observer = null
  }
})
</script>

<style scoped>
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0; right: 0;
  z-index: 1040;
  padding: 0 12px calc(12px + env(safe-area-inset-bottom, 0px));
  pointer-events: none;
}

.bottom-nav-inner {
  display: flex;
  justify-content: space-around;
  align-items: center;
  height: 68px;
  max-width: 680px;
  margin: 0 auto;
  padding: 0 6px;
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 249, 252, 0.94) 100%);
  border-radius: 24px;
  box-shadow:
    0 14px 38px rgba(44,53,73,0.16),
    0 4px 12px  rgba(44,53,73,0.10),
    0 0 0 1px  rgba(44,53,73,0.07);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  pointer-events: all;
  position: relative;
  overflow: hidden;
}

.bottom-nav-inner::before {
  content: '';
  position: absolute;
  top: 0;
  left: 18px;
  right: 18px;
  height: 1px;
  background: linear-gradient(90deg, rgba(197,216,46,0), rgba(197,216,46,0.65), rgba(197,216,46,0));
}

.nav-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  height: 100%;
  gap: 4px;
  border: none;
  background: transparent;
  cursor: pointer;
  padding: 0 2px;
  border-radius: 20px;
  transition: all 200ms cubic-bezier(0.4,0,0.2,1);
  -webkit-tap-highlight-color: transparent;
}
.nav-btn:active { transform: scale(0.90); }

.nav-btn__icon-wrap {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 30px;
}

.nav-btn__pill {
  position: absolute;
  inset: -1px;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(217,234,92,0.95) 0%, rgba(197,216,46,0.9) 100%);
  box-shadow: 0 6px 18px rgba(197,216,46,0.34);
  animation: pillIn 280ms cubic-bezier(0.34,1.56,0.64,1) both;
}
@keyframes pillIn {
  from { opacity: 0; transform: scale(0.6); }
  to   { opacity: 1; transform: scale(1); }
}

.nav-btn__icon {
  width: 18px;
  height: 18px;
  position: relative;
  z-index: 1;
  transition: color 200ms ease, transform 200ms ease;
  color: #9ca3af;
}
.nav-btn--active .nav-btn__icon {
  color: var(--primary-dark);
  transform: translateY(-1px);
}

.nav-btn__label {
  font-size: 0.67rem;
  font-weight: 500;
  color: #9ca3af;
  transition: color 200ms ease, font-weight 200ms ease, transform 200ms ease;
  line-height: 1;
  letter-spacing: 0.01em;
}
.nav-btn--active .nav-btn__label {
  color: var(--primary);
  font-weight: 700;
  transform: translateY(-1px);
}

@media (max-width: 576px) {
  .bottom-nav {
    padding-left: 10px;
    padding-right: 10px;
  }

  .bottom-nav-inner {
    height: 66px;
    border-radius: 22px;
  }

  .nav-btn__label {
    font-size: 0.63rem;
  }
}
</style>