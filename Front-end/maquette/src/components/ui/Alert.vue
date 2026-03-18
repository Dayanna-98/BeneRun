<template>
  <div
    role="alert"
    class="alert d-flex align-items-start gap-2"
    :class="variantClass"
  >
    <slot name="icon" />
    <div>
      <div v-if="$slots.title" class="fw-medium">
        <slot name="title" />
      </div>
      <div v-if="$slots.default" class="small mt-1">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, default: 'default' }, // 'default' | 'destructive'
})

const variantClass = computed(() => ({
  'alert-secondary': props.variant === 'default',
  'alert-danger': props.variant === 'destructive',
}))
</script>