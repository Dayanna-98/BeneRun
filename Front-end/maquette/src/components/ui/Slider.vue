<template>
  <div class="d-flex flex-column gap-1">
    <input
      type="range"
      class="form-range"
      :min="min"
      :max="max"
      :step="step"
      :value="modelValue"
      :disabled="disabled"
      @input="$emit('update:modelValue', Number($event.target.value))"
    />
    <div v-if="showValue" class="d-flex justify-content-between small text-muted">
      <span>{{ min }}</span>
      <span class="fw-medium text-dark">{{ modelValue }}</span>
      <span>{{ max }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: Number, default: 0 },
  min: { type: Number, default: 0 },
  max: { type: Number, default: 100 },
  step: { type: Number, default: 1 },
  disabled: { type: Boolean, default: false },
  showValue: { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>