<template>
  <div class="calendar-wrapper">
    <input
      type="date"
      class="form-control"
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      :min="min"
      :max="max"
    />
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  min: { type: String, default: '' },
  max: { type: String, default: '' },
})

defineEmits(['update:modelValue'])
</script>