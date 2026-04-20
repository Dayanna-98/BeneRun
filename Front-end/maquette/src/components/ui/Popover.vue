<template>
  <div class="position-relative d-inline-block" ref="popoverRef">
    <!-- Trigger -->
    <div @click="toggle">
      <slot name="trigger" />
    </div>

    <!-- Content -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="isOpen"
          class="popover-content bg-white border rounded shadow p-3"
          :style="contentStyle"
          style="position: fixed; z-index: 1050; width: 18rem"
        >
          <slot />
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  align: { type: String, default: 'center' }, // 'start' | 'center' | 'end'
  sideOffset: { type: Number, default: 4 },
})

const isOpen = ref(false)
const popoverRef = ref(null)
const triggerRect = ref(null)

const toggle = () => {
  if (!isOpen.value) {
    triggerRect.value = popoverRef.value?.getBoundingClientRect()
  }
  isOpen.value = !isOpen.value
}

const contentStyle = computed(() => {
  if (!triggerRect.value) return {}
  const rect = triggerRect.value
  let left = rect.left

  if (props.align === 'center') left = rect.left + rect.width / 2 - 144
  else if (props.align === 'end') left = rect.right - 288

  return {
    top: `${rect.bottom + props.sideOffset}px`,
    left: `${Math.max(8, left)}px`,
  }
})

const handleOutside = (e) => {
  if (popoverRef.value && !popoverRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', handleOutside))
onUnmounted(() => document.removeEventListener('click', handleOutside))
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s, transform 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: scale(0.95); }
</style>