<template>
  <div>
    <label v-if="label" :for="id" class="form-label small fw-medium">{{ label }}</label>

    <select
      :id="id"
      class="form-select"
      :class="[sizeClass, { 'is-invalid': invalid }]"
      :value="modelValue"
      :disabled="disabled"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <option v-if="placeholder" value="" disabled :selected="!modelValue">
        {{ placeholder }}
      </option>

      <template v-for="(group, gi) in groupedOptions" :key="gi">
        <!-- Groupe -->
        <optgroup v-if="group.label" :label="group.label">
          <option
            v-for="opt in group.options"
            :key="opt.value"
            :value="opt.value"
            :disabled="opt.disabled"
          >
            {{ opt.label }}
          </option>
        </optgroup>

        <!-- Sans groupe -->
        <template v-else>
          <option
            v-for="opt in group.options"
            :key="opt.value"
            :value="opt.value"
            :disabled="opt.disabled"
          >
            {{ opt.label }}
          </option>
        </template>
      </template>
    </select>

    <div v-if="invalid && errorMessage" class="invalid-feedback">{{ errorMessage }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: {
    type: Array,
    // Format plat : [{ value, label, disabled? }]
    // Format groupé : [{ label: 'Groupe', options: [{ value, label }] }]
    default: () => []
  },
  placeholder: { type: String, default: '' },
  label: { type: String, default: '' },
  id: { type: String, default: () => `select-${Math.random().toString(36).slice(2)}` },
  size: { type: String, default: '' }, // 'sm' | 'lg'
  disabled: { type: Boolean, default: false },
  invalid: { type: Boolean, default: false },
  errorMessage: { type: String, default: '' },
})

defineEmits(['update:modelValue'])

const sizeClass = computed(() => props.size ? `form-select-${props.size}` : '')

// Normalise les options en format groupé
const groupedOptions = computed(() => {
  if (!props.options.length) return []
  if (props.options[0]?.options) return props.options
  return [{ label: '', options: props.options }]
})
</script>