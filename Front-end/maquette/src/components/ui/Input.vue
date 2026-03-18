<template>
  <input
    v-bind="$attrs"
    :type="type"
    class="form-control"
    :class="[{ 'is-invalid': invalid }, className]"
    :placeholder="placeholder"
    :disabled="disabled"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>

<script setup>
defineProps({
  type: { type: String, default: 'text' },
  modelValue: { type: [String, Number], default: '' },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  invalid: { type: Boolean, default: false },
  className: { type: String, default: '' },
})

defineEmits(['update:modelValue'])
</script>