<template>
  <div class="progress" :style="{ height: height }">
    <div
      class="progress-bar"
      :class="variantClass"
      role="progressbar"
      :style="{ width: `${clampedValue}%` }"
      :aria-valuenow="clampedValue"
      aria-valuemin="0"
      aria-valuemax="100"
    >
      <span v-if="showLabel" class="small">{{ clampedValue }}%</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: { type: Number, default: 0 },
  height: { type: String, default: '0.5rem' },
  variant: { type: String, default: '' }, // 'success' | 'danger' | 'warning' | 'info'
  showLabel: { type: Boolean, default: false },
  striped: { type: Boolean, default: false },
  animated: { type: Boolean, default: false },
})

const clampedValue = computed(() => Math.min(100, Math.max(0, props.value)))

const variantClass = computed(() => [
  props.variant ? `bg-${props.variant}` : '',
  props.striped ? 'progress-bar-striped' : '',
  props.animated ? 'progress-bar-animated' : '',
])
</script>