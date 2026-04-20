<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import BottomNav from '@/components/ui/BottomNav.vue'

const route = useRoute()
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
</script>

<template>
  <div>
    <RouterView />
    <BottomNav v-if="showLayout" />
  </div>
</template>

<style scoped></style>