<template>
  <div class="position-relative" @contextmenu.prevent="openMenu">
    <slot />

    <Teleport to="body">
      <div
        v-if="isOpen"
        class="context-menu dropdown-menu show shadow border rounded"
        :style="{ top: `${y}px`, left: `${x}px`, position: 'fixed', zIndex: 9999, minWidth: '10rem' }"
        @click.stop
      >
        <template v-for="(item, index) in items" :key="index">
          <!-- Séparateur -->
          <hr v-if="item.type === 'separator'" class="dropdown-divider my-1" />

          <!-- Label -->
          <span v-else-if="item.type === 'label'" class="dropdown-header small fw-medium">
            {{ item.label }}
          </span>

          <!-- Checkbox -->
          <button
            v-else-if="item.type === 'checkbox'"
            class="dropdown-item d-flex align-items-center gap-2"
            @click="item.onSelect && item.onSelect(); item.checked = !item.checked"
          >
            <Check v-if="item.checked" style="width:14px;height:14px" />
            <span v-else style="width:14px;display:inline-block" />
            {{ item.label }}
          </button>

          <!-- Radio -->
          <button
            v-else-if="item.type === 'radio'"
            class="dropdown-item d-flex align-items-center gap-2"
            @click="item.onSelect && item.onSelect()"
          >
            <Circle v-if="item.checked" style="width:8px;height:8px;fill:currentColor" />
            <span v-else style="width:8px;display:inline-block" />
            {{ item.label }}
          </button>

          <!-- Item normal -->
          <button
            v-else
            class="dropdown-item d-flex align-items-center gap-2"
            :class="{ 'text-danger': item.variant === 'destructive' }"
            :disabled="item.disabled"
            @click="item.onSelect && item.onSelect(); closeMenu()"
          >
            <component v-if="item.icon" :is="item.icon" style="width:14px;height:14px" />
            {{ item.label }}
            <span v-if="item.shortcut" class="ms-auto text-muted small">{{ item.shortcut }}</span>
            <ChevronRight v-if="item.children" class="ms-auto" style="width:14px;height:14px" />
          </button>
        </template>
      </div>

      <!-- Overlay pour fermer -->
      <div v-if="isOpen" class="position-fixed inset-0" style="z-index:9998" @click="closeMenu" @contextmenu.prevent="closeMenu" />
    </Teleport>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Check, Circle, ChevronRight } from 'lucide-vue-next'

defineProps({
  items: {
    type: Array,
    // [{ type: 'item'|'separator'|'label'|'checkbox'|'radio', label, icon?, shortcut?, variant?, disabled?, checked?, onSelect? }]
    default: () => []
  }
})

const isOpen = ref(false)
const x = ref(0)
const y = ref(0)

const openMenu = (e) => {
  x.value = e.clientX
  y.value = e.clientY
  isOpen.value = true
}

const closeMenu = () => isOpen.value = false
</script>