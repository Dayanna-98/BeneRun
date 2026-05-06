<template>
  <div v-if="hasCoords" class="d-flex flex-column gap-2">
    <a
      :href="mapsLink"
      target="_blank"
      rel="noopener noreferrer"
      class="small text-primary d-inline-flex align-items-center gap-1 text-decoration-none"
    >
      <MapPinned style="width:14px;height:14px" />
      {{ linkLabel }}
    </a>

    <div ref="mapEl" class="rounded overflow-hidden border" :style="{ height: `${height}px`, width: '100%' }"></div>

    <div v-if="formattedRadius" class="small text-muted">
      Périmètre affiché : {{ formattedRadius }}
    </div>
  </div>

  <div v-else class="small text-muted">
    Collez un lien Google Maps contenant un point précis pour afficher l'aperçu.
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { MapPinned } from 'lucide-vue-next'
import 'leaflet/dist/leaflet.css'
import { extractGoogleMapsCoordinates, formatRadius, getMapZoomForRadius, buildGoogleMapsLink } from '@/utils/googleMaps'

const props = defineProps({
  mapsUrl: { type: String, default: '' },
  radiusMeters: { type: [Number, String], default: null },
  height: { type: Number, default: 220 },
  linkLabel: { type: String, default: 'Voir sur Google Maps' },
})

const mapEl = ref(null)

let L = null
let leafletMap = null
let circleLayer = null
let markerLayer = null

const coords = computed(() => extractGoogleMapsCoordinates(props.mapsUrl))
const hasCoords = computed(() => !!coords.value)
const formattedRadius = computed(() => formatRadius(props.radiusMeters))
const mapsLink = computed(() => buildGoogleMapsLink(props.mapsUrl) || (coords.value ? `https://www.google.com/maps?q=${coords.value.latitude},${coords.value.longitude}` : ''))

const initMap = async () => {
  if (!mapEl.value || !coords.value) return
  if (!L) {
    const leaflet = await import('leaflet')
    L = leaflet.default ?? leaflet
  }

  if (leafletMap) {
    leafletMap.remove()
    leafletMap = null
    circleLayer = null
    markerLayer = null
  }

  const zoom = getMapZoomForRadius(props.radiusMeters)

  leafletMap = L.map(mapEl.value, {
    center: [coords.value.latitude, coords.value.longitude],
    zoom,
    zoomControl: true,
    scrollWheelZoom: false,
  })

  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://carto.com/">CARTO</a> &copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>',
    subdomains: 'abcd',
    maxZoom: 19,
  }).addTo(leafletMap)

  const radius = Number(props.radiusMeters || 0)
  if (radius > 0) {
    circleLayer = L.circle(
      [coords.value.latitude, coords.value.longitude],
      { radius, color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.09, weight: 2, dashArray: '6 4' }
    ).addTo(leafletMap)
  }

  const pinHtml = `<div style="width:28px;height:28px;border-radius:50%;background:#ef4444;border:2.5px solid #fff;box-shadow:0 2px 8px rgba(239,68,68,.45);display:flex;align-items:center;justify-content:center"><svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><path d='M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z'/><circle cx='12' cy='10' r='3'/></svg></div>`
  markerLayer = L.marker(
    [coords.value.latitude, coords.value.longitude],
    { icon: L.divIcon({ html: pinHtml, className: '', iconSize: [28, 28], iconAnchor: [14, 14] }) }
  ).addTo(leafletMap)
}

const destroyMap = () => {
  if (leafletMap) { leafletMap.remove(); leafletMap = null }
  circleLayer = null
  markerLayer = null
}

watch(() => props.mapsUrl, async () => {
  if (!hasCoords.value) { destroyMap(); return }
  await nextTick()
  await initMap()
})

watch(() => props.radiusMeters, async () => {
  if (!leafletMap || !coords.value) return
  if (circleLayer) { leafletMap.removeLayer(circleLayer); circleLayer = null }
  const radius = Number(props.radiusMeters || 0)
  if (radius > 0) {
    circleLayer = L.circle(
      [coords.value.latitude, coords.value.longitude],
      { radius, color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.09, weight: 2, dashArray: '6 4' }
    ).addTo(leafletMap)
  }
  const zoom = getMapZoomForRadius(props.radiusMeters)
  leafletMap.setView([coords.value.latitude, coords.value.longitude], zoom)
})

onMounted(async () => {
  if (hasCoords.value) {
    await nextTick()
    await initMap()
  }
})

onUnmounted(() => destroyMap())
</script>
