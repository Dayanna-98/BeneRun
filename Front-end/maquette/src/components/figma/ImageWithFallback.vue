<template>
  <!-- Image normale -->
  <img
    v-if="!didError"
    :src="src"
    :alt="alt"
    :class="className"
    :style="style"
    v-bind="$attrs"
    @error="didError = true"
  />

  <!-- Fallback si erreur -->
  <div
    v-else
    class="d-inline-flex align-items-center justify-content-center bg-light"
    :class="className"
    :style="style"
  >
    <img
      :src="ERROR_IMG_SRC"
      alt="Error loading image"
      :data-original-url="src"
      v-bind="$attrs"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineOptions({ inheritAttrs: false })

defineProps({
  src: { type: String, default: '' },
  alt: { type: String, default: '' },
  className: { type: String, default: '' },
  style: { type: [String, Object], default: '' },
})

const didError = ref(false)

const ERROR_IMG_SRC =
  'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODgiIGhlaWdodD0iODgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgc3Ryb2tlPSIjMDAwIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBvcGFjaXR5PSIuMyIgZmlsbD0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIzLjciPjxyZWN0IHg9IjE2IiB5PSIxNiIgd2lkdGg9IjU2IiBoZWlnaHQ9IjU2IiByeD0iNiIvPjxwYXRoIGQ9Im0xNiA1OCAxNi0xOCAzMiAzMiIvPjxjaXJjbGUgY3g9IjUzIiBjeT0iMzUiIHI9IjciLz48L3N2Zz4KCg=='
</script>