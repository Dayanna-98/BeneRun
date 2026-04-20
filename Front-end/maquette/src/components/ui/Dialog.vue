<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="open" class="modal d-block" tabindex="-1" @click.self="$emit('update:open', false)">
        <div class="modal-dialog modal-dialog-centered" :class="sizeClass">
          <div class="modal-content">

            <!-- Header -->
            <div v-if="title || $slots.header" class="modal-header">
              <slot name="header">
                <h5 class="modal-title">{{ title }}</h5>
              </slot>
              <button type="button" class="btn-close" @click="$emit('update:open', false)" />
            </div>

            <!-- Body -->
            <div class="modal-body">
              <p v-if="description" class="text-muted small mb-3">{{ description }}</p>
              <slot />
            </div>

            <!-- Footer -->
            <div v-if="$slots.footer" class="modal-footer">
              <slot name="footer" />
            </div>

          </div>
        </div>
      </div>
    </Transition>

    <!-- Backdrop -->
    <div v-if="open" class="modal-backdrop fade show" />
  </Teleport>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  size: { type: String, default: '' }, // 'sm' | 'lg' | 'xl'
})

defineEmits(['update:open'])

const sizeClass = (size) => size ? `modal-${size}` : ''
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>