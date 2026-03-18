<template>
  <div
    class="scroll-area position-relative"
    :style="{ maxHeight: maxHeight, overflow: 'auto' }"
    :class="[orientation === 'horizontal' ? 'overflow-x-auto overflow-y-hidden' : 'overflow-y-auto overflow-x-hidden', className]"
  >
    <slot />
  </div>
</template>

<script setup>
defineProps({
  maxHeight: { type: String, default: '100%' },
  orientation: { type: String, default: 'vertical' }, // 'vertical' | 'horizontal'
  className: { type: String, default: '' },
})
</script>

<style scoped>
.scroll-area {
  scrollbar-width: thin;
  scrollbar-color: #dee2e6 transparent;
}
.scroll-area::-webkit-scrollbar { width: 6px; height: 6px; }
.scroll-area::-webkit-scrollbar-track { background: transparent; }
.scroll-area::-webkit-scrollbar-thumb { background-color: #dee2e6; border-radius: 9999px; }
</style>