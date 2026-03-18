<template>
  <div class="d-flex flex-column gap-2">
    <div
      v-for="option in options"
      :key="option.value"
      class="form-check d-flex align-items-center gap-2"
    >
      <input
        class="form-check-input"
        type="radio"
        :id="`${name}-${option.value}`"
        :name="name"
        :value="option.value"
        :checked="modelValue === option.value"
        :disabled="option.disabled"
        @change="$emit('update:modelValue', option.value)"
      />
      <label class="form-check-label" :for="`${name}-${option.value}`">
        {{ option.label }}
      </label>
    </div>
  </div>
</template>

<script setup>
defineProps({
  name: { type: String, required: true },
  modelValue: { type: [String, Number], default: '' },
  options: {
    type: Array,
    // [{ value, label, disabled? }]
    default: () => []
  }
})

defineEmits(['update:modelValue'])
</script>