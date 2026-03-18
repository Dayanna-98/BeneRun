<template>
  <span
    class="badge"
    :class="[variantClass, className]"
  >
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, default: 'default' }, // 'default' | 'secondary' | 'destructive' | 'outline'
  className: { type: String, default: '' },
})

const variantClass = computed(() => ({
  'bg-primary text-white':   props.variant === 'default',
  'bg-secondary text-white': props.variant === 'secondary',
  'bg-danger text-white':    props.variant === 'destructive',
  'bg-transparent border text-body': props.variant === 'outline',
}))
</script>