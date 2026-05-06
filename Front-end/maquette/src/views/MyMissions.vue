<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="text-white p-4 pb-4 position-relative overflow-hidden"
      style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">
      <div class="position-relative" style="z-index:1">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="bg-white rounded-3 p-2 shadow">
            <img src="@/assets/logo.png" alt="Running Geneva" style="height:40px;width:auto" />
          </div>
          <div>
            <div class="small text-white-50">Running Geneva</div>
            <div class="fs-5 fw-bold">Mes Missions</div>
          </div>
        </div>
        <span class="badge fw-bold px-3 py-2 text-dark"
          style="background:linear-gradient(135deg,#d4e645,#a3c200)">
          {{ myActiveMissions.length }} en cours
        </span>
      </div>
    </header>

    <div class="px-3 pt-3 d-flex flex-column gap-3 mx-auto" style="max-width:576px">

      <div v-if="isLoading" class="text-center py-4 text-muted">
        Chargement des missions en cours...
      </div>

      <div v-else-if="loadError" class="alert alert-danger" role="alert">
        {{ loadError }}
      </div>

      <!-- Empty state -->
      <div v-if="!isLoading && !loadError && myActiveMissions.length === 0" class="text-center py-5">
        <Calendar class="text-muted mb-3" style="width:64px;height:64px" />
        <p class="text-muted mb-3">Aucune mission en cours</p>
        <button class="btn btn-primary" @click="router.push('/events')">
          Découvrir les événements
        </button>
      </div>

      <!-- Mission Cards -->
      <div v-for="mission in myActiveMissions" :key="mission.id"
        class="card overflow-hidden">

        <div class="position-relative">
          <img :src="mission.imageUrl" :alt="mission.name"
            class="w-100 object-fit-cover" style="height:160px" />

          <div v-if="daysUntil(mission) > 0 && daysUntil(mission) <= 7"
            class="position-absolute" style="top:8px;left:8px">
            <span class="badge bg-warning text-dark px-3 py-2">
              Dans {{ daysUntil(mission) }} jour{{ daysUntil(mission) > 1 ? 's' : '' }}
            </span>
          </div>
          <div v-else-if="daysUntil(mission) === 0"
            class="position-absolute" style="top:8px;left:8px">
            <span class="badge bg-danger px-3 py-2">Aujourd'hui !</span>
          </div>
        </div>

        <div class="card-body d-flex flex-column gap-3">
          <div>
            <div class="x-small text-muted mb-1">{{ mission.eventName }}</div>
            <h5 class="fw-semibold mb-0">{{ mission.name }}</h5>
          </div>

          <div class="d-flex flex-column gap-2 small text-muted">
            <div class="d-flex align-items-center gap-2">
              <Calendar style="width:16px;height:16px" />
              <span>{{ formatDate(mission.date) }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <Clock style="width:16px;height:16px" />
              <span>{{ mission.startTime }} - {{ mission.endTime }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <MapPin style="width:16px;height:16px" />
              <span>{{ mission.location }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <Users style="width:16px;height:16px" />
              <span>{{ mission.currentVolunteers }}/{{ mission.maxVolunteers }} bénévoles inscrits</span>
            </div>
          </div>

          <div class="rounded p-3" style="background:#eff6ff;border:1px solid #bfdbfe">
            <div class="x-small fw-medium mb-1" style="color:#1e3a5f">Responsable</div>
            <div class="small" style="color:#1d4ed8">{{ mission.responsible.name }}</div>
            <div class="x-small mt-1" style="color:#2563eb">{{ mission.responsible.phone }}</div>
          </div>

          <button class="btn btn-primary w-100"
            @click="router.push(`/mission/${mission.id}`)">
            Voir les détails et la carte
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { MapPin, Calendar, Users, Clock } from 'lucide-vue-next'
import api from '@/services/api'
import { getCurrentUser } from '@/utils/auth'

const router = useRouter()
const currentUser = getCurrentUser()
const allMissions = ref([])
const isLoading = ref(false)
const loadError = ref('')

const toTime = (value) => {
  if (!value || typeof value !== 'string') return ''
  return value.slice(0, 5)
}

const normalizeDate = (value) => {
  if (!value) return ''
  return String(value).slice(0, 10)
}

const parseLocalDateTime = (dateValue, timeValue = '00:00') => {
  const datePart = normalizeDate(dateValue)
  if (!datePart) return null

  const [year, month, day] = datePart.split('-').map(Number)
  const [hours, minutes] = String(timeValue || '00:00').split(':').map(Number)

  if ([year, month, day, hours, minutes].some(Number.isNaN)) return null

  return new Date(year, month - 1, day, hours, minutes, 0, 0)
}

const buildEventMap = (rows) => {
  const map = new Map()
  for (const event of rows) {
    map.set(String(event.id_evenement), event.nom_evenement)
  }
  return map
}

const missionStartDateTime = (mission) => {
  if (!mission.date) return null
  const startTime = mission.startTime || '00:00'
  return parseLocalDateTime(mission.date, startTime)
}

const missionEndDateTime = (mission) => {
  if (!mission.date) return null
  const endTime = mission.endTime || '23:59'
  const end = parseLocalDateTime(mission.date, endTime)
  const start = missionStartDateTime(mission)

  if (!end) return null

  // Gère les créneaux qui passent minuit (ex: 22:00 -> 02:00)
  if (start && end < start) {
    end.setDate(end.getDate() + 1)
  }

  return end
}

const isMissionActiveNow = (mission) => {
  if (!mission.date) return false

  const start = missionStartDateTime(mission)
  const end = missionEndDateTime(mission)
  const now = new Date()
  if (!start || !end) return false

  return now >= start && now <= end
}

const myActiveMissions = computed(() => allMissions.value.filter(isMissionActiveNow))

const mapMission = (mission, eventMap, currentVolunteersMap) => {
  const missionId = String(mission.id_mission)
  return {
    id: missionId,
    eventName: eventMap.get(String(mission.id_evenement)) || `Événement #${mission.id_evenement}`,
    name: mission.titre_mission,
    date: normalizeDate(mission.date_mission),
    startTime: toTime(mission.heure_debut_mission),
    endTime: toTime(mission.heure_fin_mission),
    location: mission.lieu_mission,
    imageUrl: mission.image_mission || 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop',
    currentVolunteers: Number(mission.current_volunteers_count ?? currentVolunteersMap.get(missionId) ?? 0),
    maxVolunteers: Number(mission.nombre_benevoles_max || 0),
    status: mission.statut_mission || 'À venir',
    responsible: {
      name: [mission.responsable?.prenom_utilisateur, mission.responsable?.nom_utilisateur].filter(Boolean).join(' ') || 'Responsable non défini',
      phone: mission.responsable?.telephone_utilisateur || 'N/A',
    },
  }
}

const loadMyMissions = async () => {
  if (!currentUser?.id) {
    allMissions.value = []
    return
  }

  isLoading.value = true
  loadError.value = ''

  try {
    const [missionsResponse, affectationsResponse, eventsResponse] = await Promise.all([
      api.get('/missions'),
      api.get('/affectations'),
      api.get('/evenements'),
    ])

    const missions = Array.isArray(missionsResponse.data) ? missionsResponse.data : []
    const affectations = Array.isArray(affectationsResponse.data) ? affectationsResponse.data : []
    const events = Array.isArray(eventsResponse.data) ? eventsResponse.data : []

    const myAffectations = affectations.filter((row) =>
      String(row.id_utilisateur) === String(currentUser.id)
      && ['assigne', 'confirme', 'present'].includes(row.statut_affectation)
    )

    const missionIds = new Set(myAffectations.map((row) => String(row.id_mission)))

    const currentVolunteersMap = new Map()
    for (const affectation of affectations) {
      if (!['assigne', 'confirme', 'present'].includes(affectation.statut_affectation)) continue
      const missionId = String(affectation.id_mission)
      currentVolunteersMap.set(missionId, (currentVolunteersMap.get(missionId) || 0) + 1)
    }

    const eventMap = buildEventMap(events)

    allMissions.value = missions
      .filter((mission) => missionIds.has(String(mission.id_mission)))
      .map((mission) => mapMission(mission, eventMap, currentVolunteersMap))
      .sort((a, b) => {
        const aStart = missionStartDateTime(a)?.getTime() || 0
        const bStart = missionStartDateTime(b)?.getTime() || 0
        return aStart - bStart
      })
  } catch (error) {
    console.error('Erreur chargement mes missions:', error)
    loadError.value = 'Impossible de charger vos missions en cours.'
  } finally {
    isLoading.value = false
  }
}

const daysUntil = (m) => {
  const parsed = parseLocalDateTime(m.date)
  if (!parsed) return 0
  return Math.ceil((parsed.getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24))
}

const formatDate = (d) => {
  const parsed = parseLocalDateTime(d)
  if (!parsed) return 'Date non définie'
  return parsed.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

onMounted(loadMyMissions)
</script>