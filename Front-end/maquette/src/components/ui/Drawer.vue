<template>
  <Teleport to="body">
    <Transition :name="`slide-${direction}`">
      <div
        v-if="open"
        class="offcanvas show"
        :class="positionClass"
        tabindex="-1"
        style="visibility: visible"
      >
        <!-- Handle (bottom only) -->
        <div v-if="direction === 'bottom'" class="mx-auto mt-3 rounded-pill bg-secondary" style="width:100px;height:6px" />

        <!-- Header -->
        <div v-if="title || $slots.header" class="offcanvas-header">
          <slot name="header">
            <h5 class="offcanvas-title">{{ title }}</h5>
          </slot>
          <button type="button" class="btn-close" @click="$emit('update:open', false)" />
        </div>

        <!-- Body -->
        <div class="offcanvas-body">
          <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="p-3 border-top">
          <slot name="footer" />
        </div>
      </div>
    </Transition>

    <!-- Backdrop -->
    <div v-if="open" class="offcanvas-backdrop fade show" @click="$emit('update:open', false)" />
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  direction: { type: String, default: 'bottom' }, // 'top' | 'bottom' | 'start' | 'end'
})

defineEmits(['update:open'])

const positionClass = computed(() => `offcanvas-${props.direction}`)
</script>

<style scoped>
.slide-bottom-enter-active, .slide-bottom-leave-active { transition: transform 0.3s ease; }
.slide-bottom-enter-from, .slide-bottom-leave-to { transform: translateY(100%); }

.slide-top-enter-active, .slide-top-leave-active { transition: transform 0.3s ease; }
.slide-top-enter-from, .slide-top-leave-to { transform: translateY(-100%); }

.slide-end-enter-active, .slide-end-leave-active { transition: transform 0.3s ease; }
.slide-end-enter-from, .slide-end-leave-to { transform: translateX(100%); }

.slide-start-enter-active, .slide-start-leave-active { transition: transform 0.3s ease; }
.slide-start-enter-from, .slide-start-leave-to { transform: translateX(-100%); }
</style>