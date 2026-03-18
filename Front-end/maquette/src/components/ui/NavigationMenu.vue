<template>
  <nav class="navbar navbar-expand bg-white border rounded shadow-sm px-3">
    <ul class="navbar-nav d-flex flex-row gap-1">
      <li
        v-for="(item, index) in items"
        :key="index"
        class="nav-item position-relative"
        @mouseenter="item.children && (openIndex = index)"
        @mouseleave="openIndex = -1"
      >
        <!-- Lien simple -->
        <router-link
          v-if="!item.children"
          :to="item.to || '#'"
          class="nav-link px-3 py-2 rounded small fw-medium"
          :class="{ active: isActive(item.to) }"
        >
          <component v-if="item.icon" :is="item.icon" style="width:14px;height:14px" class="me-1" />
          {{ item.label }}
        </router-link>

        <!-- Trigger avec sous-menu -->
        <button
          v-else
          class="btn btn-light btn-sm d-flex align-items-center gap-1 px-3 py-2"
          :class="{ active: openIndex === index }"
        >
          {{ item.label }}
          <ChevronDown style="width:12px;height:12px" :class="{ 'rotate-180': openIndex === index }" class="transition" />
        </button>

        <!-- Sous-menu -->
        <div
          v-if="item.children && openIndex === index"
          class="position-absolute bg-white border rounded shadow p-2"
          style="top: 100%; left: 0; min-width: 12rem; z-index: 1000; margin-top: 6px"
        >
          <router-link
            v-for="(child, ci) in item.children"
            :key="ci"
            :to="child.to || '#'"
            class="d-flex flex-column gap-1 rounded p-2 text-decoration-none text-dark small"
            :class="{ 'bg-light': isActive(child.to) }"
            style="transition: background 0.15s"
            @mouseenter="$event.currentTarget.classList.add('bg-light')"
            @mouseleave="!isActive(child.to) && $event.currentTarget.classList.remove('bg-light')"
          >
            <span class="fw-medium">{{ child.label }}</span>
            <span v-if="child.description" class="text-muted" style="font-size: 0.75rem">{{ child.description }}</span>
          </router-link>
        </div>
      </li>
    </ul>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { ChevronDown } from 'lucide-vue-next'

defineProps({
  items: {
    type: Array,
    // [{ label, to?, icon?, children?: [{ label, to, description? }] }]
    default: () => []
  }
})

const openIndex = ref(-1)
const route = useRoute()
const isActive = (to) => to && route.path === to
</script>