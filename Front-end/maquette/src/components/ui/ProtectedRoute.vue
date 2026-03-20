<template>
  <router-view v-if="isLoggedIn" />
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const router     = useRouter()
const isLoggedIn = computed(() => !!localStorage.getItem('isLoggedIn'))

if (!isLoggedIn.value) {
  router.push('/login')
}
</script>