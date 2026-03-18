<template>
  <div>
    <label v-if="label" :for="id" class="form-label small fw-medium">{{ label }}</label>
    <textarea
      :id="id"
      class="form-control"
      :class="{ 'is-invalid': invalid, 'form-control-sm': small }"
      :placeholder="placeholder"
      :rows="rows"
      :disabled="disabled"
      :value="modelValue"
      :maxlength="maxlength"
      style="resize: none"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <div class="d-flex justify-content-between mt-1">
      <div v-if="invalid && errorMessage" class="invalid-feedback d-block">{{ errorMessage }}</div>
      <small v-if="maxlength" class="text-muted ms-auto">{{ modelValue?.length ?? 0 }}/{{ maxlength }}</small>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  rows: { type: Number, default: 3 },
  disabled: { type: Boolean, default: false },
  invalid: { type: Boolean, default: false },
  errorMessage: { type: String, default: '' },
  small: { type: Boolean, default: false },
  maxlength: { type: Number, default: null },
  id: { type: String, default: () => `textarea-${Math.random().toString(36).slice(2)}` },
})

defineEmits(['update:modelValue'])
</script>