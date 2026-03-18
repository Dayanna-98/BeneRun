<template>
  <div
    class="resizable-container d-flex h-100 w-100"
    :class="direction === 'vertical' ? 'flex-column' : 'flex-row'"
    ref="containerRef"
  >
    <!-- Panneau 1 -->
    <div
      class="resizable-panel overflow-auto"
      :style="panel1Style"
    >
      <slot name="panel1" />
    </div>

    <!-- Handle -->
    <div
      class="resizable-handle d-flex align-items-center justify-content-center bg-light border flex-shrink-0"
      :class="direction === 'vertical' ? 'w-100' : 'h-100'"
      :style="direction === 'vertical' ? 'height: 6px; cursor: row-resize' : 'width: 6px; cursor: col-resize'"
      @mousedown="startResize"
    >
      <GripVertical v-if="withHandle && direction !== 'vertical'" style="width:12px;height:12px" class="text-muted" />
      <GripHorizontal v-if="withHandle && direction === 'vertical'" style="width:12px;height:12px" class="text-muted" />
    </div>

    <!-- Panneau 2 -->
    <div class="resizable-panel overflow-auto flex-grow-1">
      <slot name="panel2" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { GripVertical, GripHorizontal } from 'lucide-vue-next'

const props = defineProps({
  direction: { type: String, default: 'horizontal' }, // 'horizontal' | 'vertical'
  defaultSize: { type: Number, default: 50 }, // % du panneau 1
  withHandle: { type: Boolean, default: true },
})

const containerRef = ref(null)
const size = ref(props.defaultSize)

const panel1Style = computed(() => ({
  [props.direction === 'vertical' ? 'height' : 'width']: `${size.value}%`,
  flexShrink: 0,
  overflow: 'auto',
}))

const startResize = (e) => {
  e.preventDefault()
  const container = containerRef.value
  if (!container) return

  const onMove = (moveEvent) => {
    const rect = container.getBoundingClientRect()
    if (props.direction === 'vertical') {
      size.value = Math.min(90, Math.max(10, ((moveEvent.clientY - rect.top) / rect.height) * 100))
    } else {
      size.value = Math.min(90, Math.max(10, ((moveEvent.clientX - rect.left) / rect.width) * 100))
    }
  }

  const onUp = () => {
    document.removeEventListener('mousemove', onMove)
    document.removeEventListener('mouseup', onUp)
  }

  document.addEventListener('mousemove', onMove)
  document.addEventListener('mouseup', onUp)
}
</script>