<template>
  <div class="form-check form-switch d-flex align-items-center gap-2 ps-0">
    <input
      class="form-check-input ms-0"
      type="checkbox"
      role="switch"
      :id="id"
      :checked="modelValue"
      :disabled="disabled"
      style="width: 2rem; height: 1.15rem; cursor: pointer"
      @change="$emit('update:modelValue', $event.target.checked)"
    />
    <label v-if="label" class="form-check-label" :for="id">{{ label }}</label>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  label: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  id: { type: String, default: () => `switch-${Math.random().toString(36).slice(2)}` },
})

defineEmits(['update:modelValue'])
</script>