<template>
  <div class="command-wrapper border rounded-3 overflow-hidden shadow">

    <!-- Input recherche -->
    <div class="d-flex align-items-center gap-2 px-3 border-bottom">
      <Search class="text-muted" style="width:16px; height:16px" />
      <input
        v-model="search"
        type="text"
        class="form-control border-0 shadow-none py-3"
        :placeholder="placeholder"
      />
    </div>

    <!-- Liste filtrée -->
    <div class="overflow-auto" style="max-height: 300px">

      <!-- Vide -->
      <div v-if="filteredItems.length === 0" class="text-center text-muted py-4 small">
        Aucun résultat
      </div>

      <!-- Groupes -->
      <template v-for="group in filteredGroups" :key="group.label">
        <div v-if="group.label" class="px-3 py-1 text-muted small fw-medium border-bottom">
          {{ group.label }}
        </div>
        <button
          v-for="item in group.items"
          :key="item.value"
          class="d-flex align-items-center gap-2 w-100 px-3 py-2 border-0 bg-transparent text-start hover-bg"
          @click="$emit('select', item)"
        >
          <component v-if="item.icon" :is="item.icon" style="width:16px; height:16px" />
          {{ item.label }}
          <span v-if="item.shortcut" class="ms-auto text-muted small">{{ item.shortcut }}</span>
        </button>
      </template>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Search } from 'lucide-vue-next'

const props = defineProps({
  placeholder: { type: String, default: 'Rechercher...' },
  groups: {
    type: Array,
    // [{ label: 'Actions', items: [{ value, label, icon?, shortcut? }] }]
    default: () => []
  },
})

defineEmits(['select'])

const search = ref('')

const filteredGroups = computed(() =>
  props.groups.map(group => ({
    ...group,
    items: group.items.filter(item =>
      item.label.toLowerCase().includes(search.value.toLowerCase())
    )
  })).filter(group => group.items.length > 0)
)

const filteredItems = computed(() =>
  filteredGroups.value.flatMap(g => g.items)
)
</script>

<style scoped>
.hover-bg:hover { background-color: var(--bs-light) !important; }
</style>