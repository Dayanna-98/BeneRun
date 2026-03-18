<template>
  <div class="ratio" :class="`ratio-${ratioClass}`">
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  ratio: { type: Number, default: 16/9 }, // ex: 16/9, 4/3, 1
})

const ratioClass = computed(() => {
  if (props.ratio === 1) return '1x1'
  if (props.ratio === 4/3) return '4x3'
  if (props.ratio === 21/9) return '21x9'
  return '16x9' // défaut
})
</script>