<template>
  <div class="table-responsive w-100">
    <table class="table table-hover align-middle mb-0" :class="[striped && 'table-striped', bordered && 'table-bordered', small && 'table-sm', className]">

      <!-- Caption -->
      <caption v-if="caption" class="text-muted small pt-2">{{ caption }}</caption>

      <!-- Header -->
      <thead v-if="columns.length" class="table-light">
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            class="fw-medium text-nowrap px-2"
            :style="col.width ? { width: col.width } : {}"
          >
            {{ col.label }}
          </th>
        </tr>
      </thead>

      <!-- Body -->
      <tbody>
        <!-- Ligne skeleton (chargement) -->
        <tr v-if="loading" v-for="n in skeletonRows" :key="'sk-' + n">
          <td v-for="col in columns" :key="col.key">
            <div class="skeleton rounded" style="height:1rem" />
          </td>
        </tr>

        <!-- Aucune donnée -->
        <tr v-else-if="!rows.length">
          <td :colspan="columns.length" class="text-center text-muted py-4">
            <slot name="empty">Aucune donnée</slot>
          </td>
        </tr>

        <!-- Données -->
        <tr
          v-else
          v-for="(row, i) in rows"
          :key="row.id ?? i"
          :class="{ 'table-active': selectedRows.includes(row.id ?? i) }"
          @click="$emit('row-click', row)"
          style="cursor: pointer"
        >
          <td v-for="col in columns" :key="col.key" class="px-2 text-nowrap">
            <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
              {{ row[col.key] }}
            </slot>
          </td>
        </tr>
      </tbody>

      <!-- Footer -->
      <tfoot v-if="$slots.footer">
        <tr>
          <td :colspan="columns.length" class="bg-light fw-medium border-top">
            <slot name="footer" />
          </td>
        </tr>
      </tfoot>

    </table>
  </div>
</template>

<script setup>
defineProps({
  columns: { type: Array, default: () => [] },
  // [{ key: 'name', label: 'Nom', width?: '200px' }]
  rows: { type: Array, default: () => [] },
  caption: { type: String, default: '' },
  striped: { type: Boolean, default: false },
  bordered: { type: Boolean, default: false },
  small: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  skeletonRows: { type: Number, default: 5 },
  selectedRows: { type: Array, default: () => [] },
  className: { type: String, default: '' },
})

defineEmits(['row-click'])
</script>

<style scoped>
.skeleton {
  background: linear-gradient(90deg, #e9ecef 25%, #dee2e6 50%, #e9ecef 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}
@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>