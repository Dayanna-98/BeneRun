<template>
  <div class="chart-container" :style="{ position: 'relative', height, width }">
    <component
      :is="chartComponent"
      :data="chartData"
      :options="chartOptions"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  Chart as ChartJS,
  CategoryScale, LinearScale, PointElement,
  LineElement, BarElement, ArcElement,
  Title, Tooltip, Legend
} from 'chart.js'
import { Bar, Line, Pie, Doughnut } from 'vue-chartjs'

ChartJS.register(
  CategoryScale, LinearScale, PointElement,
  LineElement, BarElement, ArcElement,
  Title, Tooltip, Legend
)

const props = defineProps({
  type: { type: String, default: 'bar' }, // 'bar' | 'line' | 'pie' | 'doughnut'
  chartData: { type: Object, required: true },
  chartOptions: { type: Object, default: () => ({}) },
  height: { type: String, default: '300px' },
  width: { type: String, default: '100%' },
})

const chartComponent = computed(() => ({
  bar: Bar,
  line: Line,
  pie: Pie,
  doughnut: Doughnut,
}[props.type] || Bar))
</script>