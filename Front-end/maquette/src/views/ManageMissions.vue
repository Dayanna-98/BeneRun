<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Gestion des Missions</h1>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
          @click="router.push('/manage-missions/create')">
          <Plus style="width:16px;height:16px" /> Créer
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:1024px">

      <div v-if="isLoading" class="text-center text-muted py-3">
        Chargement des missions...
      </div>

      <div v-else-if="loadError" class="alert alert-danger mb-0">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <span>{{ loadError }}</span>
          <button class="btn btn-sm btn-outline-danger" @click="loadMissions">Réessayer</button>
        </div>
      </div>

      <template v-else>

      <!-- Stats -->
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary">{{ missions.length }}</div>
              <div class="x-small text-muted">Total missions</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-success">{{ availableCount }}</div>
              <div class="x-small text-muted">Places disponibles</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-warning">{{ fullCount }}</div>
              <div class="x-small text-muted">Complets</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold" style="color:#2563eb">{{ totalVolunteers }}</div>
              <div class="x-small text-muted">Bénévoles inscrits</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Liste -->
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Toutes les missions</h5></div>
        <div class="card-body d-flex flex-column gap-3">
          <div v-for="mission in missions" :key="mission.id"
            class="border rounded p-3">

            <div class="d-flex align-items-start justify-content-between mb-3">
              <div class="flex-fill">
                <h6 class="fw-semibold text-primary mb-1">{{ mission.name }}</h6>
                <p class="small text-muted mb-0">{{ mission.eventName }}</p>
              </div>
              <div class="d-flex gap-1">
                <button class="btn btn-link p-1 text-secondary"
                  @click="router.push(`/manage-missions/edit/${mission.id}`)">
                  <Edit style="width:16px;height:16px" />
                </button>
                <button class="btn btn-link p-1 text-danger"
                  @click="handleDeleteMission(mission.id)">
                  <Trash2 style="width:16px;height:16px" />
                </button>
              </div>
            </div>

            <div class="row g-2 small text-muted mb-3">
              <div class="col-6 d-flex align-items-center gap-2">
                <Calendar style="width:16px;height:16px" />
                <span class="x-small">{{ formatDate(mission.date) }}</span>
              </div>
              <div class="col-6 d-flex align-items-center gap-2">
                <Clock style="width:16px;height:16px" />
                <span class="x-small">{{ mission.startTime }} - {{ mission.endTime }}</span>
              </div>
              <div class="col-6 d-flex align-items-center gap-2">
                <MapPin style="width:16px;height:16px" />
                <span class="x-small text-truncate">{{ mission.location }}</span>
              </div>
              <div class="col-6 d-flex align-items-center gap-2">
                <Users style="width:16px;height:16px" />
                <span class="x-small">{{ mission.currentVolunteers }}/{{ mission.maxVolunteers }}</span>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <span v-for="skill in mission.requiredSkills" :key="skill"
                class="badge border"
                style="background:rgba(26,34,48,.05);color:#1a2230;border-color:rgba(26,34,48,.2)">
                {{ skill }}
              </span>
              <span v-if="mission.currentVolunteers === mission.maxVolunteers"
                class="badge bg-warning-subtle text-warning border border-warning">
                Complet
              </span>
            </div>

          </div>
        </div>
      </div>

      </template>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Plus, Edit, Trash2, Users, Calendar, MapPin, Clock } from 'lucide-vue-next'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import api from '@/services/api'

const router = useRouter()
const user = getCurrentUser()
if (!user || !hasMinRole('organizer')) router.push('/')

const missions = ref([])
const isLoading = ref(false)
const loadError = ref('')

const availableCount = computed(() => missions.value.filter(m => m.currentVolunteers < m.maxVolunteers).length)
const fullCount       = computed(() => missions.value.filter(m => m.currentVolunteers === m.maxVolunteers).length)
const totalVolunteers = computed(() => missions.value.reduce((s, m) => s + m.currentVolunteers, 0))

const toTime = (value) => {
  if (!value || typeof value !== 'string') return ''
  return value.slice(0, 5)
}

const buildEvenementMap = (evenements) => {
  const map = new Map()
  evenements.forEach(evenement => {
    map.set(Number(evenement.id_evenement), evenement.nom_evenement || 'Evenement')
  })
  return map
}

const buildAffectationCountMap = (affectations) => {
  const map = new Map()
  affectations.forEach(affectation => {
    const missionId = Number(affectation.id_mission)
    map.set(missionId, (map.get(missionId) || 0) + 1)
  })
  return map
}

const mapMissionFromApi = (mission, courseNames, affectationCountMap) => {
  const maxVolunteers = Number(mission.nombre_benevoles_max) || 0
  const currentVolunteers = affectationCountMap.get(Number(mission.id_mission)) || 0

  return {
    id: String(mission.id_mission),
    name: mission.titre_mission,
    eventName: courseNames.get(Number(mission.id_evenement)) || `Evenement #${mission.id_evenement}`,
    date: mission.date_mission,
    startTime: toTime(mission.heure_debut_mission),
    endTime: toTime(mission.heure_fin_mission),
    location: mission.lieu_mission,
    currentVolunteers,
    maxVolunteers,
    requiredSkills: [],
  }
}

const loadMissions = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const [missionsResponse, evenementsResponse, affectationsResponse] = await Promise.all([
      api.get('/missions'),
      api.get('/evenements'),
      api.get('/affectations'),
    ])

    const missionsData = missionsResponse.data ?? []
    const evenementsData = evenementsResponse.data ?? []
    const affectationsData = affectationsResponse.data ?? []

    const courseNames = buildEvenementMap(evenementsData)
    const affectationCountMap = buildAffectationCountMap(affectationsData)

    missions.value = missionsData.map(mission =>
      mapMissionFromApi(mission, courseNames, affectationCountMap)
    )
  } catch (error) {
    console.error('Erreur lors du chargement des missions:', error)
    loadError.value = 'Impossible de charger les missions depuis le backend.'
  } finally {
    isLoading.value = false
  }
}

const formatDate = (d) =>
  new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })

const handleDeleteMission = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette mission ?')) {
    try {
      await api.delete(`/missions/${id}`)
      missions.value = missions.value.filter(m => m.id !== String(id))
      alert('Mission supprimée avec succès')
    } catch (error) {
      console.error('Erreur suppression mission:', error)
      alert('Impossible de supprimer la mission.')
    }
  }
}

onMounted(loadMissions)
</script>