<template>
  <template v-if="isLoggedIn">
    <router-view />
    <BottomNav />
  </template>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import BottomNav from './BottomNav.vue'

const router    = useRouter()
const isLoggedIn = computed(() => !!localStorage.getItem('isLoggedIn'))

if (!isLoggedIn.value) {
  router.push('/login')
}
</script>