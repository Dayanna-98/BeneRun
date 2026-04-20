<template>
  <div>
    <!-- Trigger -->
    <div @click="toggle" style="cursor: pointer">
      <slot name="trigger" />
    </div>

    <!-- Content -->
    <Transition
      enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="isOpen">
        <slot />
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  defaultOpen: { type: Boolean, default: false },
})

const isOpen = ref(props.defaultOpen)
const toggle = () => isOpen.value = !isOpen.value
</script>