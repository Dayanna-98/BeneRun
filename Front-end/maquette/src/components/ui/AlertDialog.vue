<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="open" class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-dialog-centered" :class="className">
          <div class="modal-content">

            <div class="modal-header" v-if="$slots.header">
              <slot name="header" />
            </div>

            <div class="modal-body">
              <slot />
            </div>

            <div class="modal-footer" v-if="$slots.footer">
              <slot name="footer" />
            </div>

          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  className: { type: String, default: '' },
})

defineEmits(['update:open'])
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>