<template>
  <div class="mb-3">
    <label v-if="label" :for="name" class="form-label" :class="{ 'text-danger': errorMessage }">
      {{ label }}
    </label>

    <slot :field="field" :errorMessage="errorMessage" />

    <div v-if="hint && !errorMessage" class="form-text text-muted">{{ hint }}</div>
    <div v-if="errorMessage" class="invalid-feedback d-block">{{ errorMessage }}</div>
  </div>
</template>

<script setup>
import { useField } from 'vee-validate'

const props = defineProps({
  name: { type: String, required: true },
  label: { type: String, default: '' },
  hint: { type: String, default: '' },
})

const { value: field, errorMessage } = useField(() => props.name)
</script>