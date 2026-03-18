<template>
  <nav aria-label="pagination" class="d-flex justify-content-center">
    <ul class="pagination mb-0">

      <!-- Précédent -->
      <li class="page-item" :class="{ disabled: currentPage <= 1 }">
        <button class="page-link d-flex align-items-center gap-1" @click="changePage(currentPage - 1)">
          <ChevronLeft style="width:14px;height:14px" />
          <span class="d-none d-sm-inline">Précédent</span>
        </button>
      </li>

      <!-- Première page + ellipsis -->
      <template v-if="showStartEllipsis">
        <li class="page-item">
          <button class="page-link" @click="changePage(1)">1</button>
        </li>
        <li class="page-item disabled">
          <span class="page-link"><MoreHorizontal style="width:14px;height:14px" /></span>
        </li>
      </template>

      <!-- Pages visibles -->
      <li
        v-for="page in visiblePages"
        :key="page"
        class="page-item"
        :class="{ active: page === currentPage }"
      >
        <button class="page-link" @click="changePage(page)">{{ page }}</button>
      </li>

      <!-- Ellipsis + dernière page -->
      <template v-if="showEndEllipsis">
        <li class="page-item disabled">
          <span class="page-link"><MoreHorizontal style="width:14px;height:14px" /></span>
        </li>
        <li class="page-item">
          <button class="page-link" @click="changePage(totalPages)">{{ totalPages }}</button>
        </li>
      </template>

      <!-- Suivant -->
      <li class="page-item" :class="{ disabled: currentPage >= totalPages }">
        <button class="page-link d-flex align-items-center gap-1" @click="changePage(currentPage + 1)">
          <span class="d-none d-sm-inline">Suivant</span>
          <ChevronRight style="width:14px;height:14px" />
        </button>
      </li>

    </ul>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-vue-next'

const props = defineProps({
  currentPage: { type: Number, default: 1 },
  totalPages: { type: Number, default: 1 },
  delta: { type: Number, default: 2 }, // pages visibles autour de la page courante
})

const emit = defineEmits(['update:currentPage'])

const changePage = (page) => {
  if (page >= 1 && page <= props.totalPages) {
    emit('update:currentPage', page)
  }
}

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, props.currentPage - props.delta)
  const end = Math.min(props.totalPages, props.currentPage + props.delta)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const showStartEllipsis = computed(() => visiblePages.value[0] > 2)
const showEndEllipsis = computed(() => visiblePages.value[visiblePages.value.length - 1] < props.totalPages - 1)
</script>