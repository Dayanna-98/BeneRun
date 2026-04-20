<template>
  <div class="accordion" :id="id">
    <div
      v-for="item in items"
      :key="item.value"
      class="accordion-item"
    >
      <h2 class="accordion-header">
        <button
          class="accordion-button"
          :class="{ collapsed: item.value !== defaultOpen }"
          type="button"
          data-bs-toggle="collapse"
          :data-bs-target="`#collapse-${item.value}`"
        >
          {{ item.title }}
        </button>
      </h2>
      <div
        :id="`collapse-${item.value}`"
        class="accordion-collapse collapse"
        :class="{ show: item.value === defaultOpen }"
        :data-bs-parent="`#${id}`"
      >
        <div class="accordion-body">
          {{ item.content }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  id: { type: String, default: () => `accordion-${Math.random().toString(36).slice(2)}` },
  defaultOpen: { type: String, default: null },
  items: {
    type: Array, // [{ value, title, content }]
    default: () => []
  },
})
</script>