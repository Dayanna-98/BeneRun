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
            <div class="fs-5 fw-bold">Événements</div>
          </div>
        </div>
        <p class="text-white-50 small mb-0">Découvrez les missions disponibles</p>
      </div>
    </header>

    <div class="px-3 pt-3 mx-auto" style="max-width:576px">

      <!-- Recherche -->
      <div class="mb-3 position-relative">
        <Search class="position-absolute text-muted" style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
        <input
          v-model="searchQuery"
          type="text"
          class="form-control ps-5 shadow-sm"
          placeholder="Rechercher une mission..."
        />
      </div>

      <div v-if="isLoading" class="text-center py-4 text-muted">
        Chargement des missions...
      </div>

      <div v-else-if="loadError" class="alert alert-danger" role="alert">
        {{ loadError }}
      </div>

      <!-- Mission Cards -->
      <div v-if="!isLoading && !loadError" class="d-flex flex-column gap-4">
        <div v-for="mission in filteredMissions" :key="mission.id"
          class="card border-0 shadow overflow-hidden"
          style="cursor:pointer"
          @click="router.push(`/mission/${mission.id}`)">

          <!-- Image -->
          <div class="position-relative" style="height:160px">
            <img
              :src="mission.imageUrl || 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop'"
              :alt="mission.name"
              class="w-100 h-100 object-fit-cover"
            />
            <div class="position-absolute d-flex gap-2" style="top:8px;right:8px">
              <button
                class="btn rounded-circle p-2 shadow"
                :class="favorites.includes(mission.id) ? 'btn-danger' : 'btn-light'"
                style="opacity:.9"
                @click.stop="toggleFavorite(mission.id)">
                <Star :style="favorites.includes(mission.id) ? 'fill:white' : ''" style="width:20px;height:20px" />
              </button>
              <button class="btn btn-light rounded-circle p-2 shadow" style="opacity:.9"
                @click.stop="alert('Partager la mission')">
                <Share2 style="width:20px;height:20px" />
              </button>
            </div>
            <div v-if="!canApply(mission)"
              class="position-absolute inset-0 w-100 h-100 d-flex align-items-center justify-content-center"
              style="background:rgba(0,0,0,.6)">
              <span class="badge bg-danger fs-6">Compétences requises manquantes</span>
            </div>
          </div>

          <!-- Content -->
          <div class="card-body d-flex flex-column gap-3">
            <div>
              <h5 class="fw-bold mb-1">{{ mission.name }}</h5>
              <p class="small text-muted mb-0">{{ mission.eventName }}</p>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <span v-for="skill in mission.requiredSkills" :key="skill"
                :class="['badge', userSkillNames.includes(skill) ? 'text-dark fw-semibold' : 'bg-secondary']"
                :style="userSkillNames.includes(skill) ? 'background:#d4e645' : ''">
                {{ skill }}
              </span>
            </div>

            <div class="d-flex flex-column gap-2 small text-muted">
              <div class="d-flex align-items-center gap-2">
                <MapPin style="width:16px;height:16px" />
                <span>{{ mission.location }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Clock style="width:16px;height:16px" />
                <span>{{ new Date(mission.date).toLocaleDateString('fr-FR') }} • {{ mission.startTime }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Users style="width:16px;height:16px" />
                <span>
                  {{ mission.currentVolunteers }}/{{ mission.maxVolunteers }} bénévoles
                  <span v-if="spotsLeft(mission) > 0 && spotsLeft(mission) <= 3" class="text-warning fw-semibold ms-1">
                    • {{ spotsLeft(mission) }} places restantes
                  </span>
                </span>
              </div>
            </div>

            <button
              class="btn btn-primary w-100"
              :disabled="!canApply(mission)"
              @click.stop="router.push(`/mission/${mission.id}`)">
              {{ canApply(mission) ? "S'inscrire" : 'Voir les détails' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="!isLoading && !loadError && filteredMissions.length === 0" class="text-center py-5 text-muted">
        Aucune mission trouvée
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Search, MapPin, Users, Star, Share2, Clock } from 'lucide-vue-next'
import { skills } from '@/data/mockData'
import api from '@/services/api'

const router = useRouter()
const searchQuery = ref('')
const missions = ref([])
const favorites = ref([])
const isLoading = ref(false)
const loadError = ref('')
const userSkillNames = skills.map(s => s.name)

const toTime = (value) => {
  if (!value || typeof value !== 'string') return ''
  return value.slice(0, 5)
}

const buildEventMap = (apiEvents) => {
  const map = new Map()
  for (const event of apiEvents) {
    map.set(event.id_evenement, event.nom_evenement)
  }
  return map
}

const mapMissionFromApi = (mission, eventNames) => ({
  id: String(mission.id_mission),
  name: mission.titre_mission,
  eventName: mission.evenement?.nom_evenement || eventNames.get(mission.id_evenement) || `Événement #${mission.id_evenement}`,
  location: mission.lieu_mission,
  date: mission.date_mission,
  startTime: toTime(mission.heure_debut_mission),
  currentVolunteers: Number(mission.current_volunteers_count || 0),
  maxVolunteers: Number(mission.nombre_benevoles_max) || 0,
  requiredSkills: [],
  imageUrl: null,
  isFavorite: false,
})

const loadMissions = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const [missionsResponse, eventsResponse] = await Promise.all([
      api.get('/missions'),
      api.get('/evenements'),
    ])

    const eventNames = buildEventMap(eventsResponse.data ?? [])

    missions.value = (missionsResponse.data ?? [])
      .filter(mission => mission.visibilite_mission === 'publique')
      .map(mission => mapMissionFromApi(mission, eventNames))

    favorites.value = missions.value
      .filter(mission => mission.isFavorite)
      .map(mission => mission.id)
  } catch (error) {
    console.error('Erreur lors du chargement des missions:', error)
    loadError.value = "Impossible de charger les missions depuis le backend."
  } finally {
    isLoading.value = false
  }
}

const filteredMissions = computed(() =>
  missions.value.filter(m =>
    [m.name, m.eventName, m.location].some(f =>
      f.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  )
)

const spotsLeft = (m) => m.maxVolunteers - m.currentVolunteers

const canApply = (m) =>
  m.requiredSkills.every(s => userSkillNames.includes(s)) && spotsLeft(m) > 0

const toggleFavorite = (id) => {
  const i = favorites.value.indexOf(id)
  i === -1 ? favorites.value.push(id) : favorites.value.splice(i, 1)
}

onMounted(loadMissions)
</script>