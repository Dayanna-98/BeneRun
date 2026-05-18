<template>
  <div v-if="displayLeafletMap" class="d-flex flex-column gap-2">
    <a
      v-if="mapsLink"
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

    <div v-else-if="hasBoundaryPoints" class="small text-muted">
      {{ boundarySummary }}
    </div>
  </div>

  <div v-else-if="embedUrl" class="d-flex flex-column gap-2">
    <a
      :href="mapsLink"
      target="_blank"
      rel="noopener noreferrer"
      class="small text-primary d-inline-flex align-items-center gap-1 text-decoration-none"
    >
      <MapPinned style="width:14px;height:14px" />
      {{ linkLabel }}
    </a>

    <div class="rounded overflow-hidden border" :style="{ height: `${height}px`, width: '100%' }">
      <iframe
        :src="embedUrl"
        width="100%"
        height="100%"
        frameborder="0"
        style="border:0"
        allowfullscreen
      />
    </div>

    <div v-if="formattedRadius" class="small text-muted">
      Périmètre configuré : {{ formattedRadius }}
    </div>
  </div>

  <div v-else-if="props.mapsUrl && props.mapsUrl.trim()" class="alert alert-info small mb-0">
    <strong>ℹ️ Lien sans aperçu</strong>
    <br/>
    L'aperçu de la carte n'est pas disponible pour ce lien, mais la mission s'affichera correctement avec la carte.
  </div>

  <div v-else class="small text-muted">
    Collez un lien Google Maps pour afficher l'aperçu.
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { MapPinned } from 'lucide-vue-next'
import 'leaflet/dist/leaflet.css'
import { extractGoogleMapsCoordinates, formatRadius, getMapZoomForRadius, buildGoogleMapsLink, buildGoogleMapsEmbedUrl } from '@/utils/googleMaps'

const props = defineProps({
  mapsUrl: { type: String, default: '' },
  radiusMeters: { type: [Number, String], default: null },
  boundaryPoints: { type: Array, default: () => [] },
  height: { type: Number, default: 220 },
  linkLabel: { type: String, default: 'Voir sur Google Maps' },
})

const mapEl = ref(null)

let L = null
let leafletMap = null
let circleLayer = null
let markerLayer = null
let boundaryLayer = null
let boundaryMarkersLayer = null

const coords = computed(() => extractGoogleMapsCoordinates(props.mapsUrl))
const hasCoords = computed(() => !!coords.value)
const normalizedBoundaryPoints = computed(() => {
  if (!Array.isArray(props.boundaryPoints)) return []

  return props.boundaryPoints
    .map((point) => {
      const latitude = Number(point?.latitude)
      const longitude = Number(point?.longitude)
      if (Number.isNaN(latitude) || Number.isNaN(longitude)) return null
      if (latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) return null
      return { latitude, longitude }
    })
    .filter(Boolean)
})
const hasBoundaryPoints = computed(() => normalizedBoundaryPoints.value.length > 0)
const displayLeafletMap = computed(() => hasCoords.value || hasBoundaryPoints.value)
const formattedRadius = computed(() => formatRadius(props.radiusMeters))
const centerCoordinates = computed(() => {
  if (coords.value) return coords.value
  if (!hasBoundaryPoints.value) return null
  return normalizedBoundaryPoints.value[0]
})
const mapsLink = computed(() => buildGoogleMapsLink(props.mapsUrl, centerCoordinates.value))
const embedUrl = computed(() => buildGoogleMapsEmbedUrl(props.mapsUrl, { radiusMeters: props.radiusMeters }))
const boundarySummary = computed(() => {
  const count = normalizedBoundaryPoints.value.length
  if (!count) return ''
  if (count < 3) return `${count} point(s) détecté(s). Ajoutez au moins 3 missions pour afficher un polygone fermé.`
  return `Périmètre calculé automatiquement depuis ${count} missions.`
})

const initMap = async () => {
  if (!mapEl.value || !centerCoordinates.value) return
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
    center: [centerCoordinates.value.latitude, centerCoordinates.value.longitude],
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
  if (radius > 0 && hasCoords.value) {
    circleLayer = L.circle(
      [coords.value.latitude, coords.value.longitude],
      { radius, color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.09, weight: 2, dashArray: '6 4' }
    ).addTo(leafletMap)
  }

  if (hasCoords.value) {
    const pinHtml = `<div style="width:28px;height:28px;border-radius:50%;background:#ef4444;border:2.5px solid #fff;box-shadow:0 2px 8px rgba(239,68,68,.45);display:flex;align-items:center;justify-content:center"><svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><path d='M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z'/><circle cx='12' cy='10' r='3'/></svg></div>`
    markerLayer = L.marker(
      [coords.value.latitude, coords.value.longitude],
      { icon: L.divIcon({ html: pinHtml, className: '', iconSize: [28, 28], iconAnchor: [14, 14] }) }
    ).addTo(leafletMap)
  }

  if (hasBoundaryPoints.value) {
    const latLngs = normalizedBoundaryPoints.value.map((point) => [point.latitude, point.longitude])

    boundaryMarkersLayer = L.layerGroup(
      latLngs.map((latLng) => L.circleMarker(latLng, {
        radius: 5,
        color: '#0f766e',
        fillColor: '#14b8a6',
        fillOpacity: 0.9,
        weight: 1.5,
      }))
    ).addTo(leafletMap)

    if (latLngs.length >= 3) {
      boundaryLayer = L.polygon(latLngs, {
        color: '#0f766e',
        fillColor: '#14b8a6',
        fillOpacity: 0.14,
        weight: 2,
      }).addTo(leafletMap)
    } else {
      boundaryLayer = L.polyline(latLngs, {
        color: '#0f766e',
        weight: 2,
        dashArray: '4 4',
      }).addTo(leafletMap)
    }
  }

  const allPoints = []
  if (hasCoords.value) {
    allPoints.push([coords.value.latitude, coords.value.longitude])
  }
  if (hasBoundaryPoints.value) {
    for (const point of normalizedBoundaryPoints.value) {
      allPoints.push([point.latitude, point.longitude])
    }
  }

  if (allPoints.length > 1) {
    leafletMap.fitBounds(L.latLngBounds(allPoints), { padding: [24, 24], maxZoom: 16 })
  }
}

const destroyMap = () => {
  if (leafletMap) { leafletMap.remove(); leafletMap = null }
  circleLayer = null
  markerLayer = null
  boundaryLayer = null
  boundaryMarkersLayer = null
}

watch(() => props.mapsUrl, async () => {
  if (!displayLeafletMap.value) { destroyMap(); return }
  await nextTick()
  await initMap()
})

watch(() => props.boundaryPoints, async () => {
  if (!displayLeafletMap.value) { destroyMap(); return }
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
  if (hasCoords.value) {
    const zoom = getMapZoomForRadius(props.radiusMeters)
    leafletMap.setView([coords.value.latitude, coords.value.longitude], zoom)
  }
})

onMounted(async () => {
  if (displayLeafletMap.value) {
    await nextTick()
    await initMap()
  }
})

onUnmounted(() => destroyMap())
</script>
