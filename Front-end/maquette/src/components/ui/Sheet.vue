<template>
  <Teleport to="body">
    <Transition :name="`slide-${side}`">
      <div
        v-if="open"
        class="offcanvas show d-flex flex-column"
        :class="positionClass"
        tabindex="-1"
        style="visibility: visible"
      >
        <!-- Header -->
        <div class="offcanvas-header border-bottom">
          <div>
            <h5 v-if="title" class="offcanvas-title fw-semibold">{{ title }}</h5>
            <p v-if="description" class="text-muted small mb-0">{{ description }}</p>
            <slot name="header" />
          </div>
          <button type="button" class="btn-close" @click="$emit('update:open', false)" />
        </div>

        <!-- Body -->
        <div class="offcanvas-body flex-grow-1">
          <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="p-3 border-top mt-auto d-flex flex-column gap-2">
          <slot name="footer" />
        </div>
      </div>
    </Transition>

    <!-- Backdrop -->
    <div
      v-if="open"
      class="offcanvas-backdrop fade show"
      @click="$emit('update:open', false)"
    />
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  side: { type: String, default: 'right' }, // 'top' | 'bottom' | 'left' | 'right'
  title: { type: String, default: '' },
  description: { type: String, default: '' },
})

defineEmits(['update:open'])

const positionClass = computed(() => {
  const map = { right: 'offcanvas-end', left: 'offcanvas-start', top: 'offcanvas-top', bottom: 'offcanvas-bottom' }
  return map[props.side] || 'offcanvas-end'
})
</script>

<style scoped>
.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s ease; }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }

.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.3s ease; }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }

.slide-bottom-enter-active, .slide-bottom-leave-active { transition: transform 0.3s ease; }
.slide-bottom-enter-from, .slide-bottom-leave-to { transform: translateY(100%); }

.slide-top-enter-active, .slide-top-leave-active { transition: transform 0.3s ease; }
.slide-top-enter-from, .slide-top-leave-to { transform: translateY(-100%); }
</style>