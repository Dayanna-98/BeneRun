<template>
  <nav class="menubar d-flex align-items-center gap-1 border rounded p-1 bg-white shadow-sm" style="height: 2.5rem">
    <div
      v-for="(menu, index) in menus"
      :key="index"
      class="position-relative"
      @mouseenter="openIndex = index"
      @mouseleave="openIndex = -1"
    >
      <!-- Trigger -->
      <button
        class="btn btn-sm btn-light d-flex align-items-center gap-1 px-2 py-1"
        :class="{ active: openIndex === index }"
      >
        {{ menu.label }}
      </button>

      <!-- Dropdown -->
      <ul
        v-if="openIndex === index"
        class="dropdown-menu show shadow border rounded position-absolute"
        style="min-width: 12rem; z-index: 1000; top: 100%"
      >
        <template v-for="(item, i) in menu.items" :key="i">
          <li v-if="item.type === 'separator'"><hr class="dropdown-divider" /></li>
          <li v-else-if="item.type === 'label'">
            <span class="dropdown-header small fw-medium">{{ item.label }}</span>
          </li>
          <li v-else-if="item.type === 'checkbox'">
            <button class="dropdown-item d-flex align-items-center gap-2" @click="item.onSelect && item.onSelect()">
              <Check v-if="item.checked" style="width:14px;height:14px" />
              <span v-else style="width:14px;display:inline-block" />
              {{ item.label }}
            </button>
          </li>
          <li v-else-if="item.type === 'radio'">
            <button class="dropdown-item d-flex align-items-center gap-2" @click="item.onSelect && item.onSelect()">
              <Circle v-if="item.checked" style="width:8px;height:8px;fill:currentColor" />
              <span v-else style="width:8px;display:inline-block" />
              {{ item.label }}
            </button>
          </li>
          <li v-else>
            <button
              class="dropdown-item d-flex align-items-center gap-2"
              :class="{ 'text-danger': item.variant === 'destructive' }"
              :disabled="item.disabled"
              @click="item.onSelect && item.onSelect()"
            >
              <component v-if="item.icon" :is="item.icon" style="width:14px;height:14px" />
              {{ item.label }}
              <span v-if="item.shortcut" class="ms-auto text-muted small">{{ item.shortcut }}</span>
              <ChevronRight v-if="item.children" class="ms-auto" style="width:14px;height:14px" />
            </button>
          </li>
        </template>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { Check, Circle, ChevronRight } from 'lucide-vue-next'

defineProps({
  menus: {
    type: Array,
    // [{ label: 'Fichier', items: [{ type, label, icon?, shortcut?, variant?, disabled?, checked?, onSelect? }] }]
    default: () => []
  }
})

const openIndex = ref(-1)
</script>