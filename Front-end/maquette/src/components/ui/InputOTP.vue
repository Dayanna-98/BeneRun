<template>
  <div class="d-flex align-items-center gap-2">
    <div class="d-flex gap-1">
      <input
        v-for="(_, index) in length"
        :key="index"
        :ref="el => inputs[index] = el"
        type="text"
        inputmode="numeric"
        maxlength="1"
        class="form-control text-center fw-medium p-0"
        style="width: 2.5rem; height: 2.5rem; font-size: 1rem"
        :class="{ 'border-primary': activeIndex === index }"
        :value="otp[index] || ''"
        @focus="activeIndex = index"
        @blur="activeIndex = -1"
        @keydown="handleKeydown($event, index)"
        @input="handleInput($event, index)"
        :disabled="disabled"
      />
    </div>

    <!-- Séparateur optionnel au milieu -->
    <span v-if="separator" class="text-muted">—</span>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  length: { type: Number, default: 6 },
  modelValue: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  separator: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'complete'])

const inputs = ref([])
const activeIndex = ref(-1)
const otp = ref(props.modelValue.split(''))

watch(() => props.modelValue, (val) => {
  otp.value = val.split('')
})

const handleInput = (e, index) => {
  const val = e.target.value.replace(/\D/g, '')
  otp.value[index] = val
  emit('update:modelValue', otp.value.join(''))

  if (val && index < props.length - 1) {
    inputs.value[index + 1]?.focus()
  }

  if (otp.value.filter(Boolean).length === props.length) {
    emit('complete', otp.value.join(''))
  }
}

const handleKeydown = (e, index) => {
  if (e.key === 'Backspace' && !otp.value[index] && index > 0) {
    inputs.value[index - 1]?.focus()
  }
  if (e.key === 'ArrowLeft' && index > 0) inputs.value[index - 1]?.focus()
  if (e.key === 'ArrowRight' && index < props.length - 1) inputs.value[index + 1]?.focus()
}
</script>