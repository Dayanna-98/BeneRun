<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div>
        <div class="small fw-semibold">
          {{ isActiveMission ? "Carte en temps réel" : "Localisation de la mission" }}
        </div>
        <div v-if="missionId" class="x-small text-muted">
          {{ visibleParticipants.length }} participant{{ visibleParticipants.length !== 1 ? "s" : "" }} visible{{ visibleParticipants.length !== 1 ? "s" : "" }}
          <span v-if="lastSyncLabel" :class="['sync-status', syncStatusClass]"> • {{ lastSyncLabel }}</span>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button
          v-if="missionId"
          @click="toggleGps"
          :class="[`btn btn-sm d-flex align-items-center gap-1 px-2 py-1`, gpsActive ? `btn-success` : `btn-outline-secondary`]"
          style="font-size:11px;line-height:1.5"
        >
          <span :class="[`gps-dot`, gpsActive ? `gps-dot--active` : ``]"></span>
          {{ gpsActive ? "GPS actif" : "Partager ma position" }}
        </button>
        <button
          v-if="missionId && isDebugUser"
          @click="testGpsNow"
          class="btn btn-sm btn-outline-primary px-2 py-1"
          style="font-size:11px;line-height:1.5"
        >
          Tester GPS
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
      v-else-if="fallbackEmbedUrl"
      class="rounded overflow-hidden border"
      style="height:300px;width:100%"
    >
      <iframe
        :src="fallbackEmbedUrl"
        width="100%"
        height="100%"
        frameborder="0"
        style="border:0"
        allowfullscreen
      />
    </div>
    <div
      v-else
      class="rounded border bg-light d-flex align-items-center justify-content-center text-muted small"
      style="height:110px"
    >
      Aucune coordonnée disponible pour cette mission.
    </div>

    <div v-if="missionId && gpsDiagnostic" class="mt-2 alert alert-warning py-2 mb-0 small">
      {{ gpsDiagnostic }}
    </div>
    <div v-if="missionId && apiDiagnostic" class="mt-2 alert alert-danger py-2 mb-0 small">
      {{ apiDiagnostic }}
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import 'leaflet/dist/leaflet.css'
import api from '@/services/api'
import { buildGoogleMapsEmbedUrl } from '@/utils/googleMaps'

const props = defineProps({
  missionId:       { type: [String, Number], default: null },
  missionPoint:    { type: Object, default: null },
  eventCenter:     { type: Object, default: null },
  radiusMeters:    { type: Number, default: 0 },
  currentUserId:   { type: [String, Number], default: '' },
  isActiveMission: { type: Boolean, default: false },
  googleMapsUrl:   { type: String, default: '' },
  participants:    { type: Array, default: () => [] },
  liveLocationSharingEnabled: { type: Boolean, default: false },
  currentUserRole: { type: String, default: '' },
})

const mapEl = ref(null)
const gpsActive = ref(false)
const visibleParticipants = ref([])
const gpsDiagnostic = ref('')
const apiDiagnostic = ref('')
const lastSyncAt = ref(null)
const syncClockTick = ref(Date.now())

let L = null
let leafletMap = null
let pollInterval = null
let watchId = null
let lastPush = 0
let hasCenteredOnSelf = false
let localSelfPosition = null
let syncClockInterval = null
const participantMarkers = new Map()

const center = computed(() => props.missionPoint || props.eventCenter)
const firstParticipantCenter = computed(() => {
  if (!visibleParticipants.value.length) return null

  const latitude = Number(visibleParticipants.value[0]?.latitude)
  const longitude = Number(visibleParticipants.value[0]?.longitude)

  if (Number.isNaN(latitude) || Number.isNaN(longitude)) return null

  return { latitude, longitude }
})
const normalizedParticipantFallback = computed(() => {
  if (!Array.isArray(props.participants)) return []

  return props.participants
    .map((participant) => {
      const latitude = Number(participant?.latitude)
      const longitude = Number(participant?.longitude)

      if (Number.isNaN(latitude) || Number.isNaN(longitude)) return null
      if (latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) return null

      return {
        id_utilisateur: participant?.id,
        name: participant?.name || 'Participant',
        latitude,
        longitude,
        updated_at: participant?.updatedAt || null,
      }
    })
    .filter(Boolean)
})
const effectiveCenter = computed(() => center.value || firstParticipantCenter.value)
const hasCoords = computed(() => !!effectiveCenter.value)
const fallbackEmbedUrl = computed(() => buildGoogleMapsEmbedUrl(props.googleMapsUrl))
const localCurrentUserKey = computed(() => String(props.currentUserId || 'me'))
const isDebugUser = computed(() => ['admin', 'superadmin'].includes(String(props.currentUserRole || '').toLowerCase()))
const lastSyncLabel = computed(() => {
  syncClockTick.value

  if (!lastSyncAt.value) return ''

  const diffSec = Math.max(0, Math.floor((Date.now() - lastSyncAt.value.getTime()) / 1000))
  if (diffSec < 60) return `Synchro il y a ${diffSec}s`

  const diffMin = Math.floor(diffSec / 60)
  return `Synchro il y a ${diffMin} min`
})
const syncStatusClass = computed(() => {
  syncClockTick.value

  if (!lastSyncAt.value) return 'text-muted'

  const diffSec = Math.max(0, Math.floor((Date.now() - lastSyncAt.value.getTime()) / 1000))
  return diffSec > 30 ? 'text-warning' : 'text-muted'
})

const externalLink = computed(() => {
  if (props.googleMapsUrl) return props.googleMapsUrl
  if (effectiveCenter.value) return `https://www.google.com/maps?q=${effectiveCenter.value.latitude},${effectiveCenter.value.longitude}`
  return null
})

const initials = (name) => {
  if (!name) return '?'
  return name.trim().split(/\s+/).map(w => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase()
}

const isMe = (p) => String(p.id_utilisateur) === localCurrentUserKey.value

const relativeTime = (ts) => {
  if (!ts) return ''
  const diff = Math.floor((Date.now() - new Date(ts).getTime()) / 1000)
  if (diff < 60) return "à l'instant"
  if (diff < 3600) return `il y a ${Math.floor(diff / 60)} min`
  return `il y a ${Math.floor(diff / 3600)} h`
}

const getCurrentUserName = () => {
  const currentId = localCurrentUserKey.value

  const fromVisible = visibleParticipants.value.find((participant) => String(participant.id_utilisateur) === currentId)
  if (fromVisible?.name) return fromVisible.name

  const fromFallback = normalizedParticipantFallback.value.find((participant) => String(participant.id_utilisateur) === currentId)
  if (fromFallback?.name) return fromFallback.name

  return 'Vous'
}

const upsertCurrentUserPositionLocally = (latitude, longitude) => {
  const currentId = localCurrentUserKey.value

  const lat = Number(latitude)
  const lng = Number(longitude)
  if (Number.isNaN(lat) || Number.isNaN(lng)) return

  const next = {
    id_utilisateur: currentId,
    name: getCurrentUserName(),
    latitude: lat,
    longitude: lng,
    updated_at: new Date().toISOString(),
  }

  localSelfPosition = next

  const others = visibleParticipants.value.filter((participant) => String(participant.id_utilisateur) !== currentId)
  visibleParticipants.value = [next, ...others]

  if (!leafletMap && mapEl.value && effectiveCenter.value) {
    initMap().then(() => syncMarkers(visibleParticipants.value))
    return
  }

  if (leafletMap && !hasCenteredOnSelf) {
    leafletMap.setView([lat, lng], Math.max(leafletMap.getZoom(), 15))
    hasCenteredOnSelf = true
  }

  syncMarkers(visibleParticipants.value)
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
  if (!mapEl.value || !effectiveCenter.value) return

  const leaflet = await import('leaflet')
  L = leaflet.default ?? leaflet

  leafletMap = L.map(mapEl.value, {
    center: [effectiveCenter.value.latitude, effectiveCenter.value.longitude],
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

}

const fetchPositions = async () => {
  const fallbackByUser = new Map(
    normalizedParticipantFallback.value.map((participant) => [
      String(participant.id_utilisateur),
      participant,
    ])
  )

  if (localSelfPosition) {
    fallbackByUser.set(String(localSelfPosition.id_utilisateur), localSelfPosition)
  }

  if (!props.missionId) {
    visibleParticipants.value = Array.from(fallbackByUser.values())

    if (!leafletMap && mapEl.value && effectiveCenter.value) {
      await initMap()
    }

    syncMarkers(visibleParticipants.value)
    return
  }

  try {
    const res = await api.get(`/missions/${props.missionId}/positions`)
    const positions = Array.isArray(res.data) ? res.data : []

    for (const position of positions) {
      fallbackByUser.set(String(position.id_utilisateur), position)
    }

    visibleParticipants.value = Array.from(fallbackByUser.values())

    if (!leafletMap && mapEl.value && effectiveCenter.value) {
      await initMap()
    }

    syncMarkers(visibleParticipants.value)
    apiDiagnostic.value = ''
    lastSyncAt.value = new Date()
  } catch (error) {
    const status = error?.response?.status
    if (status === 403) {
      apiDiagnostic.value = 'Acces refuse aux positions live pour cette mission (participant/responsable requis).'
    } else if (status === 401) {
      apiDiagnostic.value = 'Session expiree: reconnecte-toi puis reessaie.'
    }

    visibleParticipants.value = Array.from(fallbackByUser.values())

    if (!leafletMap && mapEl.value && effectiveCenter.value) {
      await initMap()
    }

    syncMarkers(visibleParticipants.value)
  }
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

const formatGeolocationError = (error) => {
  if (!error) return 'Erreur de geolocalisation inconnue.'

  if (error.code === 1) {
    return 'Geolocalisation refusee. Autorise la localisation dans ton navigateur.'
  }

  if (error.code === 2) {
    return 'Position indisponible. Verifie GPS/reseau puis reessaie.'
  }

  if (error.code === 3) {
    return 'Delai depasse pour recuperer la position. Reessaie.'
  }

  return error.message || 'Erreur de geolocalisation inconnue.'
}

const resetDiagnostics = () => {
  gpsDiagnostic.value = ''
  apiDiagnostic.value = ''
}

const startGps = () => {
  resetDiagnostics()

  if (!window.isSecureContext) {
    gpsDiagnostic.value = 'Geolocalisation bloquee: cette page doit etre en HTTPS (ou localhost).'
    return
  }

  if (!navigator.geolocation) {
    gpsDiagnostic.value = 'Geolocalisation non disponible sur cet appareil/navigateur.'
    return
  }

  if (watchId !== null) return

  watchId = navigator.geolocation.watchPosition(
    (pos) => {
      // Reflect the user's exact GPS point immediately on the map.
      upsertCurrentUserPositionLocally(pos.coords.latitude, pos.coords.longitude)
      pushPosition(pos.coords.latitude, pos.coords.longitude)
      gpsDiagnostic.value = ''
    },
    (error) => {
      gpsDiagnostic.value = formatGeolocationError(error)
      gpsActive.value = false
    },
    { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 }
  )
  gpsActive.value = true
}

const stopGps = () => {
  if (watchId !== null) { navigator.geolocation.clearWatch(watchId); watchId = null }
  gpsActive.value = false
}

const ensurePollingState = async () => {
  if (props.missionId) {
    await fetchPositions()

    if (!pollInterval) {
      pollInterval = setInterval(fetchPositions, 15000)
    }

    if (props.liveLocationSharingEnabled && !gpsActive.value) {
      startGps()
    }

    return
  }

  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }

  if (gpsActive.value) {
    stopGps()
  }
}

const pushPosition = async (lat, lng) => {
  const now = Date.now()
  if (now - lastPush < 10000) return
  lastPush = now
  if (!props.missionId) return
  if (!props.currentUserId) {
    apiDiagnostic.value = 'Identifiant utilisateur introuvable dans la session. Recharge la page ou reconnecte-toi.'
    return
  }
  try {
    await api.post(`/missions/${props.missionId}/positions`, {
      latitude: lat,
      longitude: lng,
    })
    apiDiagnostic.value = ''
    lastSyncAt.value = new Date()
  } catch (error) {
    const status = error?.response?.status
    if (status === 401) {
      apiDiagnostic.value = 'Session expiree: reconnecte-toi puis reessaie.'
      return
    }
    if (status === 403) {
      apiDiagnostic.value = 'Acces refuse a la position live pour cette mission (participant/responsable requis).'
      return
    }
  }
}

const testGpsNow = () => {
  resetDiagnostics()

  if (!window.isSecureContext) {
    gpsDiagnostic.value = 'Geolocalisation bloquee: cette page doit etre en HTTPS (ou localhost).'
    return
  }

  if (!navigator.geolocation) {
    gpsDiagnostic.value = 'Geolocalisation non disponible sur cet appareil/navigateur.'
    return
  }

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      upsertCurrentUserPositionLocally(pos.coords.latitude, pos.coords.longitude)
      pushPosition(pos.coords.latitude, pos.coords.longitude)
      gpsDiagnostic.value = ''
    },
    (error) => {
      gpsDiagnostic.value = formatGeolocationError(error)
    },
    { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 }
  )
}

onMounted(async () => {
  syncClockInterval = setInterval(() => {
    syncClockTick.value = Date.now()
  }, 1000)

  await ensurePollingState()

  if (!leafletMap && hasCoords.value) {
    await initMap()
  }
})

watch(
  () => [props.isActiveMission, props.missionId],
  async () => {
    await ensurePollingState()
  }
)

watch(
  () => props.liveLocationSharingEnabled,
  (enabled) => {
    if (!props.missionId) return

    if (enabled) {
      startGps()
      return
    }

    stopGps()
  }
)

watch(
  () => props.participants,
  async () => {
    if (props.missionId) {
      await fetchPositions()
      return
    }

    visibleParticipants.value = normalizedParticipantFallback.value
    if (!leafletMap && mapEl.value && effectiveCenter.value) {
      await initMap()
    }
    syncMarkers(visibleParticipants.value)
  },
  { deep: true }
)

onUnmounted(() => {
  stopGps()
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
  if (leafletMap) { leafletMap.remove(); leafletMap = null }
  if (syncClockInterval) {
    clearInterval(syncClockInterval)
    syncClockInterval = null
  }
  hasCenteredOnSelf = false
  localSelfPosition = null
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

.sync-status {
  font-weight: 600;
}
</style>
