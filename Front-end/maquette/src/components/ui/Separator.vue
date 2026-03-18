<template>
  <hr
    v-if="orientation === 'horizontal'"
    class="border-top my-2 w-100"
    :class="className"
    :role="decorative ? 'none' : 'separator'"
  />
  <div
    v-else
    class="border-start mx-2 align-self-stretch"
    :class="className"
    :role="decorative ? 'none' : 'separator'"
    style="width: 1px"
  />
</template>

<script setup>
defineProps({
  orientation: { type: String, default: 'horizontal' }, // 'horizontal' | 'vertical'
  decorative: { type: Boolean, default: true },
  className: { type: String, default: '' },
})
</script>