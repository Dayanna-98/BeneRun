<template>
  <div class="form-check">
    <input
      type="checkbox"
      class="form-check-input"
      :id="id"
      :checked="modelValue"
      :disabled="disabled"
      @change="$emit('update:modelValue', $event.target.checked)"
    />
    <label v-if="label" class="form-check-label" :for="id">
      {{ label }}
    </label>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  label: { type: String, default: '' },
  id: { type: String, default: () => `checkbox-${Math.random().toString(36).slice(2)}` },
  disabled: { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>