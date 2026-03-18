<template>
  <div>
    <!-- Tab nav -->
    <ul class="nav nav-tabs" :class="pills && 'nav-pills'">
      <li v-for="tab in tabs" :key="tab.value" class="nav-item">
        <button
          class="nav-link d-flex align-items-center gap-1"
          :class="{ active: modelValue === tab.value, disabled: tab.disabled }"
          @click="!tab.disabled && $emit('update:modelValue', tab.value)"
        >
          <component v-if="tab.icon" :is="tab.icon" style="width:14px;height:14px" />
          {{ tab.label }}
          <span v-if="tab.badge" class="badge bg-secondary ms-1">{{ tab.badge }}</span>
        </button>
      </li>
    </ul>

    <!-- Tab content -->
    <div class="tab-content pt-3">
      <div
        v-for="tab in tabs"
        :key="tab.value"
        class="tab-pane"
        :class="{ 'show active': modelValue === tab.value }"
      >
        <slot :name="tab.value" />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: String, required: true },
  tabs: {
    type: Array,
    default: () => []
    // [{ value, label, icon?, badge?, disabled? }]
  },
  pills: { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>