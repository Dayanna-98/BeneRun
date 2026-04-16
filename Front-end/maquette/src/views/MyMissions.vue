<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Loading state -->
    <div v-if="isLoading" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center text-muted">Chargement de vos missions...</div>
    </div>

    <!-- Error state -->
    <div v-else-if="loadError" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-danger mb-3">{{ loadError }}</p>
        <button class="btn btn-primary" @click="loadMyMissions">Réessayer</button>
      </div>
    </div>

    <template v-else>
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

        <!-- Empty state -->
        <div v-if="myActiveMissions.length === 0" class="text-center py-5">
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
            <img :src="mission.imageUrl || 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=800&h=400&fit=crop'"
              :alt="mission.name"
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
              <div class="small" style="color:#1d4ed8">{{ mission.responsible.name || 'N/D' }}</div>
              <div class="x-small mt-1" style="color:#2563eb">{{ mission.responsible.phone || 'N/D' }}</div>
            </div>

            <button class="btn btn-primary w-100"
              @click="router.push(`/mission/${mission.id}`)">
              Voir les détails et la carte
            </button>
          </div>
        </div>

      </div>
    </template>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ref, onMounted, computed } from 'vue'
import { MapPin, Calendar, Users, Clock } from 'lucide-vue-next'
import { myActiveMissions as mockMissions } from '@/data/mockData'
import api from '@/services/api'
import { getCurrentUser } from '@/utils/auth'

const router = useRouter()

// Data
const missions = ref([])
const isLoading = ref(true)
const loadError = ref(null)
const eventMap = ref({})
const userMap = ref({})
const affectationCountMap = ref({})

// Computed
const startOfToday = () => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return today
}

const myActiveMissions = computed(() =>
  missions.value.filter(m => m.date && new Date(m.date) >= startOfToday())
    .sort((a, b) => new Date(a.date) - new Date(b.date))
)

// Helper functions
const toTime = (timeStr) => {
  if (!timeStr) return ''
  const [h, m] = timeStr.split(':')
  return `${h}:${m}`
}

const formatDate = (d) =>
  new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })

const daysUntil = (m) => {
  if (!m.date) return 999
  return Math.ceil((new Date(m.date).getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24))
}

// Build maps
const buildEventMap = async () => {
  try {
    const { data } = await api.get('/evenements')
    const map = {}
    data.forEach(event => {
      map[event.id_evenement] = event.nom_evenement || 'Événement'
    })
    eventMap.value = map
  } catch (err) {
    console.error('Error loading events:', err)
  }
}

const buildUserMap = async () => {
  try {
    const { data } = await api.get('/users')
    const map = {}
    data.forEach(user => {
      map[user.id_utilisateur] = {
        name: `${user.prenom_utilisateur || ''} ${user.nom_utilisateur || ''}`.trim(),
        phone: user.telephone_utilisateur || ''
      }
    })
    userMap.value = map
  } catch (err) {
    console.error('Error loading users:', err)
  }
}

const buildAffectationCountMap = async () => {
  try {
    const { data } = await api.get('/affectations')
    const map = {}
    data.forEach(aff => {
      if (!map[aff.id_mission]) map[aff.id_mission] = 0
      map[aff.id_mission]++
    })
    affectationCountMap.value = map
  } catch (err) {
    console.error('Error loading affectations:', err)
  }
}

// Map mission from API
const mapMissionFromApi = (apiMission) => {
  return {
    id: apiMission.id_mission,
    name: apiMission.titre_mission,
    eventName: eventMap.value[apiMission.id_evenement] || 'Événement',
    date: apiMission.date_mission,
    imageUrl: '', // Not available in backend
    startTime: toTime(apiMission.heure_debut_mission),
    endTime: toTime(apiMission.heure_fin_mission),
    location: apiMission.lieu_mission,
    currentVolunteers: affectationCountMap.value[apiMission.id_mission] || 0,
    maxVolunteers: apiMission.nombre_benevoles_max || 0,
    responsible: userMap.value[apiMission.responsable_utilisateur_id] || { name: '', phone: '' }
  }
}

// Load data
const loadMyMissions = async () => {
  try {
    isLoading.value = true
    loadError.value = null

    const currentUser = getCurrentUser()
    const currentEmail = currentUser?.email || localStorage.getItem('userEmail')
    if (!currentEmail) {
      // Fallback to mock data
      missions.value = mockMissions
      isLoading.value = false
      return
    }

    // Load all support data in parallel
    await Promise.all([
      buildEventMap(),
      buildUserMap(),
      buildAffectationCountMap(),
    ])

    const [usersResponse, affectationsResponse, missionsResponse] = await Promise.all([
      api.get('/users'),
      api.get('/affectations'),
      api.get('/missions'),
    ])

    const users = usersResponse.data ?? []
    const affectations = affectationsResponse.data ?? []
    const allMissions = missionsResponse.data ?? []

    const rawUser = users.find(u => (u.email || '').toLowerCase() === currentEmail.toLowerCase())
    if (!rawUser) {
      missions.value = []
      isLoading.value = false
      return
    }

    // Load affectations for current user
    const userAffectations = affectations.filter(
      aff => Number(aff.id_utilisateur) === Number(rawUser.id_utilisateur)
    )
    
    if (userAffectations.length === 0) {
      missions.value = []
      isLoading.value = false
      return
    }

    // Get mission IDs from affectations
    const missionIds = new Set(userAffectations.map(aff => Number(aff.id_mission)))
    const userMissions = allMissions.filter(m => missionIds.has(Number(m.id_mission)))

    // Map missions
    const mappedMissions = userMissions.map(m => mapMissionFromApi(m))
    missions.value = mappedMissions.filter(m => m !== null)
  } catch (err) {
    console.error('Error loading my missions:', err)
    loadError.value = err.message || 'Erreur lors du chargement des missions'
    missions.value = mockMissions // Fallback
  } finally {
    isLoading.value = false
  }
}

// Load on mount
onMounted(() => loadMyMissions())
</script>