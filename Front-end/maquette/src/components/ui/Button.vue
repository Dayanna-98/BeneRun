<template>
  <component
    :is="as"
    class="btn"
    :class="[variantClass, sizeClass, className]"
    :disabled="disabled"
    v-bind="$attrs"
  >
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, default: 'default' }, // 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
  size: { type: String, default: 'default' },    // 'default' | 'sm' | 'lg' | 'icon'
  as: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
  className: { type: String, default: '' },
})

const variantClass = computed(() => ({
  'btn-primary':          props.variant === 'default',
  'btn-danger':           props.variant === 'destructive',
  'btn-outline-secondary': props.variant === 'outline',
  'btn-secondary':        props.variant === 'secondary',
  'btn-light':            props.variant === 'ghost',
  'btn-link':             props.variant === 'link',
}))

const sizeClass = computed(() => ({
  'btn-sm': props.size === 'sm',
  'btn-lg': props.size === 'lg',
  'p-2':    props.size === 'icon',
}))
</script>