<template>
  <div class="card" :class="className">

    <div v-if="$slots.header || title" class="card-header">
      <slot name="header">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h5 v-if="title" class="card-title mb-0">{{ title }}</h5>
            <p v-if="description" class="card-subtitle text-muted small mt-1">{{ description }}</p>
          </div>
          <div v-if="$slots.action">
            <slot name="action" />
          </div>
        </div>
      </slot>
    </div>

    <div class="card-body">
      <slot />
    </div>

    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer" />
    </div>

  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  className: { type: String, default: '' },
})
</script>