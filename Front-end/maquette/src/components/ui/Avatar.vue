<template>
  <div class="avatar rounded-circle overflow-hidden flex-shrink-0" :class="className" :style="sizeStyle">
    <img
      v-if="src && !imgError"
      :src="src"
      :alt="alt"
      class="w-100 h-100 object-fit-cover"
      @error="imgError = true"
    />
    <div
      v-else
      class="w-100 h-100 d-flex align-items-center justify-content-center bg-secondary text-white fw-medium"
    >
      {{ fallback }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  src: { type: String, default: null },
  alt: { type: String, default: '' },
  fallback: { type: String, default: '?' },
  size: { type: Number, default: 40 },
  className: { type: String, default: '' },
})

const imgError = ref(false)

const sizeStyle = { width: `${props.size}px`, height: `${props.size}px` }
</script>