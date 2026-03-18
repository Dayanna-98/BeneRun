<template>
  <div class="tooltip-wrapper position-relative d-inline-flex" @mouseenter="show = true" @mouseleave="show = false">

    <!-- Élément déclencheur -->
    <slot />

    <!-- Contenu tooltip -->
    <Teleport to="body">
      <Transition name="tooltip-fade">
        <div
          v-if="show && content"
          ref="tooltipEl"
          class="tooltip-box position-fixed bg-dark text-white rounded px-2 py-1 small"
          style="z-index: 9999; pointer-events: none; white-space: nowrap; font-size: 0.75rem"
          :style="tooltipStyle"
        >
          {{ content }}
          <div class="tooltip-arrow" :class="`arrow-${side}`" />
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  content: { type: String, default: '' },
  side: { type: String, default: 'top' }, // 'top' | 'bottom' | 'left' | 'right'
  delay: { type: Number, default: 0 },
})

const show = ref(false)
const tooltipEl = ref(null)
const tooltipStyle = ref({})

let timer = null

watch(show, async (val) => {
  if (val) {
    if (props.delay) {
      timer = setTimeout(() => positionTooltip(), props.delay)
    } else {
      await nextTick()
      positionTooltip()
    }
  } else {
    clearTimeout(timer)
  }
})

const positionTooltip = async () => {
  await nextTick()
  const trigger = tooltipEl.value?.previousElementSibling
  if (!tooltipEl.value) return

  // Utilise l'élément parent comme référence
  const parent = document.querySelector('.tooltip-wrapper:hover')
  if (!parent) return

  const rect = parent.getBoundingClientRect()
  const tip = tooltipEl.value.getBoundingClientRect()

  const positions = {
    top:    { top: `${rect.top - tip.height - 8}px`,  left: `${rect.left + rect.width / 2 - tip.width / 2}px` },
    bottom: { top: `${rect.bottom + 8}px`,             left: `${rect.left + rect.width / 2 - tip.width / 2}px` },
    left:   { top: `${rect.top + rect.height / 2 - tip.height / 2}px`, left: `${rect.left - tip.width - 8}px` },
    right:  { top: `${rect.top + rect.height / 2 - tip.height / 2}px`, left: `${rect.right + 8}px` },
  }

  tooltipStyle.value = positions[props.side] || positions.top
}
</script>

<style scoped>
.tooltip-fade-enter-active, .tooltip-fade-leave-active { transition: opacity 0.15s, transform 0.15s; }
.tooltip-fade-enter-from, .tooltip-fade-leave-to { opacity: 0; transform: scale(0.95); }

.tooltip-arrow {
  position: absolute;
  width: 0; height: 0;
}
.arrow-top    { bottom: -4px; left: 50%; transform: translateX(-50%); border-left: 4px solid transparent; border-right: 4px solid transparent; border-top: 4px solid #212529; }
.arrow-bottom { top: -4px;    left: 50%; transform: translateX(-50%); border-left: 4px solid transparent; border-right: 4px solid transparent; border-bottom: 4px solid #212529; }
.arrow-left   { right: -4px;  top: 50%;  transform: translateY(-50%); border-top: 4px solid transparent; border-bottom: 4px solid transparent; border-left: 4px solid #212529; }
.arrow-right  { left: -4px;   top: 50%;  transform: translateY(-50%); border-top: 4px solid transparent; border-bottom: 4px solid transparent; border-right: 4px solid #212529; }
</style>