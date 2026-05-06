<template>
  <div class="d-flex flex-column gap-3">
    <div v-for="index in count" :key="index" class="card border-0 shadow-sm overflow-hidden">
      <div class="skeleton" :style="`height:${imageHeight}px`" />
      <div class="card-body d-flex flex-column gap-2">
        <div class="skeleton" style="height:18px;width:65%" />
        <div class="skeleton" style="height:14px;width:45%" />
        <div class="skeleton" style="height:12px;width:92%" />
        <div class="skeleton" style="height:12px;width:78%" />
        <div class="skeleton mt-2" style="height:38px;width:100%" />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  count: { type: Number, default: 3 },
  imageHeight: { type: Number, default: 160 },
})
</script>