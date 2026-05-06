<template>
  <div class="empty-state card border-0 shadow-sm">
    <div class="card-body text-center py-5 px-4">
      <div class="empty-state__icon">{{ icon }}</div>
      <h3 class="fs-6 fw-semibold mb-2">{{ title }}</h3>
      <p class="small text-muted mb-0">{{ description }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: { type: String, default: '◦' },
  title: { type: String, required: true },
  description: { type: String, default: '' },
})
</script>

<style scoped>
.empty-state__icon {
  width: 58px;
  height: 58px;
  margin: 0 auto 12px;
  border-radius: 18px;
  display: grid;
  place-items: center;
  font-size: 1.5rem;
  color: var(--primary);
  background: linear-gradient(135deg, rgba(197,216,46,0.18), rgba(44,53,73,0.08));
}
</style>