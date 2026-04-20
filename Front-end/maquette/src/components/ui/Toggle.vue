<template>
  <div class="d-inline-flex">
    <input
      type="checkbox"
      class="btn-check"
      :id="id"
      :checked="modelValue"
      :disabled="disabled"
      autocomplete="off"
      @change="$emit('update:modelValue', $event.target.checked)"
    />
    <label
      class="btn d-flex align-items-center gap-2"
      :class="[variantClass, sizeClass]"
      :for="id"
    >
      <slot />
    </label>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  variant: { type: String, default: 'default' }, // 'default' | 'outline'
  size: { type: String, default: 'default' },    // 'sm' | 'default' | 'lg'
  disabled: { type: Boolean, default: false },
  id: { type: String, default: () => `toggle-${Math.random().toString(36).slice(2)}` },
})

defineEmits(['update:modelValue'])

const variantClass = computed(() => ({
  'btn-secondary': props.variant === 'default',
  'btn-outline-secondary': props.variant === 'outline',
}))

const sizeClass = computed(() => ({
  'btn-sm': props.size === 'sm',
  'btn-lg': props.size === 'lg',
}))
</script>