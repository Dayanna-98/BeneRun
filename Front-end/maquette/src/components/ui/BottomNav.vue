<template>
  <nav class="bottom-nav">
    <div class="bottom-nav-inner">
      <button
        v-for="item in navItems"
        :key="item.path"
        class="nav-btn"
        :class="{ 'nav-btn--active': isActive(item.path) }"
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
import { useRouter, useRoute } from 'vue-router'
import { Home, Calendar, Briefcase, MessageCircle, ListCheck } from 'lucide-vue-next'

const router = useRouter()
const route  = useRoute()

const navItems = [
  { path: '/',           icon: Home,         label: 'Accueil'    },
  { path: '/missions',   icon: ListCheck,    label: 'Missions'   },
  { path: '/events',     icon: Calendar,     label: 'Événements' },
  { path: '/my-missions',icon: Briefcase,    label: 'En cours'   },
  { path: '/messagerie', icon: MessageCircle,label: 'Messages'   },
]

const isActive = (path) => {
  if (path === '/') return route.path === '/'
  return route.path === path || route.path.startsWith(`${path}/`)
}
</script>

<style scoped>
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0; right: 0;
  z-index: 1040;
  padding: 0 12px 12px;
  pointer-events: none;
}

.bottom-nav-inner {
  display: flex;
  justify-content: space-around;
  align-items: center;
  height: 64px;
  max-width: 640px;
  margin: 0 auto;
  background: rgba(255, 255, 255, 0.92);
  border-radius: 22px;
  box-shadow:
    0 8px 32px rgba(44,53,73,0.16),
    0 2px 8px  rgba(44,53,73,0.10),
    0 0 0 1px  rgba(44,53,73,0.06);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  pointer-events: all;
}

.nav-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  height: 100%;
  gap: 3px;
  border: none;
  background: transparent;
  cursor: pointer;
  padding: 0;
  border-radius: 18px;
  transition: all 200ms cubic-bezier(0.4,0,0.2,1);
  -webkit-tap-highlight-color: transparent;
}
.nav-btn:active { transform: scale(0.90); }

.nav-btn__icon-wrap {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 28px;
}

.nav-btn__pill {
  position: absolute;
  inset: 0;
  border-radius: 12px;
  background: var(--accent);
  animation: pillIn 280ms cubic-bezier(0.34,1.56,0.64,1) both;
}
@keyframes pillIn {
  from { opacity: 0; transform: scale(0.6); }
  to   { opacity: 1; transform: scale(1); }
}

.nav-btn__icon {
  width: 19px;
  height: 19px;
  position: relative;
  z-index: 1;
  transition: color 200ms ease;
  color: #9ca3af;
}
.nav-btn--active .nav-btn__icon {
  color: var(--primary-dark);
}

.nav-btn__label {
  font-size: 0.67rem;
  font-weight: 500;
  color: #9ca3af;
  transition: color 200ms ease, font-weight 200ms ease;
  line-height: 1;
}
.nav-btn--active .nav-btn__label {
  color: var(--primary);
  font-weight: 700;
}
</style>