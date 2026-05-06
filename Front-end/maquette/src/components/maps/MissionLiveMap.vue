<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div>
        <div class="small fw-semibold">
          {{ isActiveMission ? "Carte en temps réel" : "Localisation de la mission" }}
        </div>
        <div v-if="isActiveMission" class="x-small text-muted">
          {{ visibleParticipants.length }} participant{{ visibleParticipants.length !== 1 ? "s" : "" }} visible{{ visibleParticipants.length !== 1 ? "s" : "" }}
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button
          v-if="isActiveMission && currentUserId"
          @click="toggleGps"
          :class="[`btn btn-sm d-flex align-items-center gap-1 px-2 py-1`, gpsActive ? `btn-success` : `btn-outline-secondary`]"
          style="font-size:11px;line-height:1.5"
        >
          <span :class="[`gps-dot`, gpsActive ? `gps-dot--active` : ``]"></span>
          {{ gpsActive ? "GPS actif" : "Partager ma position" }}
        </button>
        <a
          v-if="externalLink"
          :href="externalLink"
          target="_blank"
          rel="noopener noreferrer"
          class="btn btn-sm btn-outline-secondary px-2 py-1"
          style="font-size:11px;line-height:1.5"
        >↗ Maps</a>
      </div>
    </div>

    <!-- Carte Leaflet -->
    <div
      v-if="hasCoords"
      ref="mapEl"
      class="rounded overflow-hidden border"
      style="height:300px;width:100%"
    ></div>
    <div
      v-else
      class="rounded border bg-light d-flex align-items-center justify-content-center text-muted small"
      style="height:110px"
    >
      Aucune coordonnée disponible pour cette mission.
    </div>

    <!-- Liste des participants visibles -->
    <div v-if="isActiveMission && visibleParticipants.length" class="mt-2 d-flex flex-column gap-1">
      <div
        v-for="p in visibleParticipants"
        :key="p.id_utilisateur"
        class="d-flex align-items-center justify-content-between rounded px-2 py-1"
        style="background:#f8fafc"
      >
        <div class="d-flex align-items-center gap-2">
          <span
            class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
            :style="{
              width: `22px`, height: `22px`, fontSize: `9px`,
              background: isMe(p) ? `#16a34a` : `#3b82f6`,
            }"
          >{{ initials(p.name) }}</span>
          <span class="small">{{ p.name }}<span v-if="isMe(p)" class="text-muted"> (vous)</span></span>
        </div>
        <span class="x-small text-muted">{{ relativeTime(p.updated_at) }}</span>
      </div>
    </div>
    <div v-else-if="isActiveMission" class="mt-2 x-small text-muted text-center">
      Aucun participant n'a encore partagé sa position.
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import 'leaflet/dist/leaflet.css'
import api from '@/services/api'

const props = defineProps({
  missionId:       { type: [String, Number], default: null },
  missionPoint:    { type: Object, default: null },
  eventCenter:     { type: Object, default: null },
  radiusMeters:    { type: Number, default: 0 },
  currentUserId:   { type: [String, Number], default: '' },
  isActiveMission: { type: Boolean, default: false },
  googleMapsUrl:   { type: String, default: '' },
})

const mapEl = ref(null)
const gpsActive = ref(false)
const visibleParticipants = ref([])

let L = null
let leafletMap = null
let pollInterval = null
let watchId = null
let lastPush = 0
const participantMarkers = new Map()

const center = computed(() => props.missionPoint || props.eventCenter)
const hasCoords = computed(() => !!center.value)

const externalLink = computed(() => {
  if (props.googleMapsUrl) return props.googleMapsUrl
  if (center.value) return `https://www.google.com/maps?q=${center.value.latitude},${center.value.longitude}`
  return null
})

const initials = (name) => {
  if (!name) return '?'
  return name.trim().split(/\s+/).map(w => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase()
}

const isMe = (p) => String(p.id_utilisateur) === String(props.currentUserId)

const relativeTime = (ts) => {
  if (!ts) return ''
  const diff = Math.floor((Date.now() - new Date(ts).getTime()) / 1000)
  if (diff < 60) return "à l'instant"
  if (diff < 3600) return `il y a ${Math.floor(diff / 60)} min`
  return `il y a ${Math.floor(diff / 3600)} h`
}

const createParticipantIcon = (name, isCurrent) => {
  const bg = isCurrent ? '#16a34a' : '#3b82f6'
  const html = `<div style="width:32px;height:32px;border-radius:50%;background:${bg};border:2.5px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:11px;font-family:system-ui,sans-serif">${initials(name)}</div>`
  return L.divIcon({ html, className: '', iconSize: [32, 32], iconAnchor: [16, 16] })
}

const createMissionIcon = () => {
  const html = `
    <div style="position:relative;width:40px;height:40px">
      <div class="lm-pulse"></div>
      <div style="position:absolute;inset:0;border-radius:50%;background:#ef4444;border:2.5px solid #fff;box-shadow:0 3px 12px rgba(239,68,68,.55);display:flex;align-items:center;justify-content:center">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
        </svg>
      </div>
    </div>`
  return L.divIcon({ html, className: '', iconSize: [40, 40], iconAnchor: [20, 20] })
}

const initMap = async () => {
  if (!mapEl.value || !center.value) return

  const leaflet = await import('leaflet')
  L = leaflet.default ?? leaflet

  leafletMap = L.map(mapEl.value, {
    center: [center.value.latitude, center.value.longitude],
    zoom: 15,
    zoomControl: true,
  })

  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://carto.com/">CARTO</a> &copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>',
    subdomains: 'abcd',
    maxZoom: 19,
  }).addTo(leafletMap)

  if (props.radiusMeters > 0 && props.eventCenter) {
    L.circle(
      [props.eventCenter.latitude, props.eventCenter.longitude],
      { radius: props.radiusMeters, color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.07, weight: 2, dashArray: '6 4' }
    ).addTo(leafletMap)
  }

  if (props.missionPoint) {
    L.marker(
      [props.missionPoint.latitude, props.missionPoint.longitude],
      { icon: createMissionIcon(), zIndexOffset: 1000 }
    ).addTo(leafletMap).bindPopup('<strong>Point de mission</strong>', { closeButton: false })
  }

  if (props.isActiveMission && props.missionId) {
    await fetchPositions()
    pollInterval = setInterval(fetchPositions, 15000)
  }
}

const fetchPositions = async () => {
  if (!props.missionId) return
  try {
    const res = await api.get(`/missions/${props.missionId}/positions`)
    const positions = Array.isArray(res.data) ? res.data : []
    visibleParticipants.value = positions
    syncMarkers(positions)
  } catch { /* silencieux */ }
}

const syncMarkers = (positions) => {
  if (!leafletMap) return
  const seen = new Set()
  for (const p of positions) {
    const key = String(p.id_utilisateur)
    const latlng = [Number(p.latitude), Number(p.longitude)]
    seen.add(key)
    if (participantMarkers.has(key)) {
      participantMarkers.get(key).setLatLng(latlng)
    } else {
      const marker = L.marker(latlng, {
        icon: createParticipantIcon(p.name, isMe(p)),
        zIndexOffset: isMe(p) ? 500 : 0,
      }).addTo(leafletMap).bindPopup(`<strong>${p.name}</strong>`, { closeButton: false })
      participantMarkers.set(key, marker)
    }
  }
  for (const [key, marker] of participantMarkers.entries()) {
    if (!seen.has(key)) { leafletMap.removeLayer(marker); participantMarkers.delete(key) }
  }
}

const toggleGps = () => { if (gpsActive.value) stopGps(); else startGps() }

const startGps = () => {
  if (!navigator.geolocation) return
  watchId = navigator.geolocation.watchPosition(
    (pos) => pushPosition(pos.coords.latitude, pos.coords.longitude),
    () => { gpsActive.value = false },
    { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 }
  )
  gpsActive.value = true
}

const stopGps = () => {
  if (watchId !== null) { navigator.geolocation.clearWatch(watchId); watchId = null }
  gpsActive.value = false
}

const pushPosition = async (lat, lng) => {
  const now = Date.now()
  if (now - lastPush < 10000) return
  lastPush = now
  if (!props.missionId || !props.currentUserId) return
  try {
    await api.post(`/missions/${props.missionId}/positions`, {
      id_utilisateur: Number(props.currentUserId),
      latitude: lat,
      longitude: lng,
    })
  } catch { /* silencieux */ }
}

onMounted(() => { if (hasCoords.value) initMap() })

onUnmounted(() => {
  stopGps()
  if (pollInterval) clearInterval(pollInterval)
  if (leafletMap) { leafletMap.remove(); leafletMap = null }
})
</script>

<style>
.lm-pulse {
  position: absolute;
  inset: -10px;
  border-radius: 50%;
  background: rgba(239, 68, 68, 0.14);
  border: 1.5px solid rgba(239, 68, 68, 0.28);
  animation: lm-pulse-ring 2.2s ease-out infinite;
}
@keyframes lm-pulse-ring {
  0%   { transform: scale(0.55); opacity: 0.9; }
  100% { transform: scale(1.55); opacity: 0;   }
}
</style>

<style scoped>
.gps-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: currentColor; display: inline-block; flex-shrink: 0;
}
.gps-dot--active { animation: gps-blink 1.2s ease-in-out infinite; }
@keyframes gps-blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.2; } }
</style>
