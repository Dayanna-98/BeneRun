<template>
  <nav class="fixed-bottom bg-white border-top shadow-lg">
    <div class="d-flex justify-content-around align-items-center mx-auto"
      style="height:64px;max-width:640px">
      <button
        v-for="item in navItems"
        :key="item.path"
        class="d-flex flex-column align-items-center justify-content-center flex-fill h-100 gap-1 border-0 bg-transparent"
        :class="{ 'scale-110': isActive(item.path) }"
        @click="router.push(item.path)">
        <div class="p-2 rounded-3"
          :style="isActive(item.path) ? 'background:var(--accent)' : ''">
          <component :is="item.icon"
            style="width:20px;height:20px"
            :style="isActive(item.path) ? 'color:var(--primary)' : 'color:#9ca3af'" />
        </div>
        <span class="x-small"
          :class="isActive(item.path) ? 'fw-bold' : 'text-muted'"
          :style="isActive(item.path) ? 'color:var(--primary)' : ''">
          {{ item.label }}
        </span>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router'
import { Home, Calendar, Briefcase, User, ListCheck } from 'lucide-vue-next'

const router = useRouter()
const route  = useRoute()

const navItems = [
  { path: '/',           icon: Home,      label: 'Accueil'     },
  { path: '/missions',   icon: ListCheck, label: 'Missions'    },
  { path: '/events',     icon: Calendar,  label: 'Événements'  },
  { path: '/my-missions',icon: Briefcase, label: 'En cours'    },
  { path: '/profile',    icon: User,      label: 'Profil'      },
]

const isActive = (path) => route.path === path
</script>