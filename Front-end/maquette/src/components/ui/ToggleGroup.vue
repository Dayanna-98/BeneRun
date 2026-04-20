<template>
  <div class="btn-group" role="group">
    <template v-for="item in items" :key="item.value">
      <input
        type="checkbox"
        class="btn-check"
        :id="`tg-${groupId}-${item.value}`"
        :checked="modelValue.includes(item.value)"
        :disabled="item.disabled"
        autocomplete="off"
        @change="toggle(item.value)"
      />
      <label
        class="btn d-flex align-items-center gap-1"
        :class="[variantClass, sizeClass]"
        :for="`tg-${groupId}-${item.value}`"
      >
        <component v-if="item.icon" :is="item.icon" style="width:14px;height:14px" />
        {{ item.label }}
      </label>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  items: {
    type: Array,
    default: () => []
    // [{ value, label, icon?, disabled? }]
  },
  variant: { type: String, default: 'outline' }, // 'default' | 'outline'
  size: { type: String, default: 'default' },
  groupId: { type: String, default: () => Math.random().toString(36).slice(2) },
})

const emit = defineEmits(['update:modelValue'])

const toggle = (value) => {
  const current = [...props.modelValue]
  const i = current.indexOf(value)
  i === -1 ? current.push(value) : current.splice(i, 1)
  emit('update:modelValue', current)
}

const variantClass = computed(() => ({
  'btn-secondary': props.variant === 'default',
  'btn-outline-secondary': props.variant === 'outline',
}))

const sizeClass = computed(() => ({
  'btn-sm': props.size === 'sm',
  'btn-lg': props.size === 'lg',
}))
</script>