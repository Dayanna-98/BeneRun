<template>
  <div class="position-relative d-inline-block" @mouseenter="show = true" @mouseleave="show = false">
    <!-- Trigger -->
    <slot name="trigger" />

    <!-- Card -->
    <Transition name="fade">
      <div
        v-if="show"
        class="hover-card position-absolute bg-white border rounded shadow p-3"
        :class="alignClass"
        style="z-index: 1000; width: 16rem; margin-top: 4px"
      >
        <slot />
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  align: { type: String, default: 'center' }, // 'start' | 'center' | 'end'
})

const show = ref(false)

const alignClass = computed(() => ({
  'start-0': props.align === 'start',
  'start-50 translate-middle-x': props.align === 'center',
  'end-0': props.align === 'end',
}))
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>