<template>
  <div class="dropdown" :class="{ show: isOpen }" ref="dropdownRef">

    <!-- Trigger -->
    <div @click="toggle">
      <slot name="trigger" />
    </div>

    <!-- Menu -->
    <ul v-if="isOpen" class="dropdown-menu show shadow" style="min-width: 10rem">
      <template v-for="(item, index) in items" :key="index">

        <!-- Séparateur -->
        <li v-if="item.type === 'separator'"><hr class="dropdown-divider" /></li>

        <!-- Label -->
        <li v-else-if="item.type === 'label'">
          <span class="dropdown-header small fw-medium">{{ item.label }}</span>
        </li>

        <!-- Checkbox -->
        <li v-else-if="item.type === 'checkbox'">
          <button class="dropdown-item d-flex align-items-center gap-2" @click="item.onSelect && item.onSelect()">
            <Check v-if="item.checked" style="width:14px;height:14px" />
            <span v-else style="width:14px;display:inline-block" />
            {{ item.label }}
          </button>
        </li>

        <!-- Radio -->
        <li v-else-if="item.type === 'radio'">
          <button class="dropdown-item d-flex align-items-center gap-2" @click="item.onSelect && item.onSelect()">
            <Circle v-if="item.checked" style="width:8px;height:8px;fill:currentColor" />
            <span v-else style="width:8px;display:inline-block" />
            {{ item.label }}
          </button>
        </li>

        <!-- Item normal -->
        <li v-else>
          <button
            class="dropdown-item d-flex align-items-center gap-2"
            :class="{ 'text-danger': item.variant === 'destructive' }"
            :disabled="item.disabled"
            @click="item.onSelect && item.onSelect(); close()"
          >
            <component v-if="item.icon" :is="item.icon" style="width:14px;height:14px" />
            {{ item.label }}
            <span v-if="item.shortcut" class="ms-auto text-muted small">{{ item.shortcut }}</span>
          </button>
        </li>

      </template>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Check, Circle } from 'lucide-vue-next'

defineProps({
  items: {
    type: Array,
    // [{ type: 'item'|'separator'|'label'|'checkbox'|'radio', label, icon?, shortcut?, variant?, disabled?, checked?, onSelect? }]
    default: () => []
  }
})

const isOpen = ref(false)
const dropdownRef = ref(null)

const toggle = () => isOpen.value = !isOpen.value
const close = () => isOpen.value = false

const handleOutsideClick = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) close()
}

onMounted(() => document.addEventListener('click', handleOutsideClick))
onUnmounted(() => document.removeEventListener('click', handleOutsideClick))
</script>