<template>
  <label
    :for="htmlFor"
    class="form-label d-flex align-items-center gap-2 fw-medium small"
    :class="[{ 'text-danger': error }, className]"
  >
    <slot />
  </label>
</template>

<script setup>
defineProps({
  htmlFor: { type: String, default: '' },
  error: { type: Boolean, default: false },
  className: { type: String, default: '' },
})
</script>