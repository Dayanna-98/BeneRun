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
            <div class="fs-5 fw-bold">Missions</div>
          </div>
        </div>
        <p class="text-white-50 small mb-0">Trouvez votre prochaine mission à Genève</p>
      </div>
    </header>

    <div class="px-3 pt-3 mx-auto" style="max-width:576px">

      <!-- Recherche -->
      <div class="mb-3 position-relative">
        <Search class="position-absolute text-muted"
          style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
        <input v-model="searchQuery" type="text" class="form-control ps-5 shadow-sm"
          placeholder="Rechercher une mission..." />
      </div>

      <div class="d-flex gap-2 mb-3">
        <button
          class="btn btn-sm"
          :class="timelineFilter === 'upcoming' ? 'btn-primary' : 'btn-outline-primary'"
          @click="timelineFilter = 'upcoming'"
        >
          À venir
        </button>
        <button
          class="btn btn-sm"
          :class="timelineFilter === 'past' ? 'btn-primary' : 'btn-outline-primary'"
          @click="timelineFilter = 'past'"
        >
          Passées
        </button>
        <button
          class="btn btn-sm"
          :class="timelineFilter === 'all' ? 'btn-primary' : 'btn-outline-primary'"
          @click="timelineFilter = 'all'"
        >
          Toutes
        </button>
      </div>

      <div v-if="eventFilterId" class="alert alert-info d-flex align-items-center justify-content-between py-2">
        <div class="small">
          Filtre actif: missions liées à <strong>{{ eventFilterName }}</strong>
        </div>
        <button class="btn btn-sm btn-outline-primary" @click="clearFilters">
          Retirer
        </button>
      </div>

      <!-- Filtres -->
      <div class="mb-3">
        <button class="btn btn-light w-100 d-flex align-items-center justify-content-between shadow-sm"
          @click="showFilters = true">
          <div class="d-flex align-items-center gap-2">
            <Filter style="width:20px;height:20px" />
            <span>Filtres</span>
          </div>
          <span v-if="activeFiltersCount > 0" class="badge bg-primary rounded-pill">
            {{ activeFiltersCount }}
          </span>
        </button>
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

          <div class="position-relative" style="height:160px">
            <img
              :src="mission.imageUrl || 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop'"
              :alt="mission.name" class="w-100 h-100 object-fit-cover" />
            <div class="position-absolute d-flex gap-2" style="top:8px;right:8px">
              <button class="btn rounded-circle p-2 shadow"
                :class="favorites.includes(mission.id) ? 'btn-danger' : 'btn-light'"
                style="opacity:.9"
                @click.stop="toggleFavorite(mission.id)">
                <Star :style="favorites.includes(mission.id) ? 'fill:white' : ''"
                  style="width:20px;height:20px" />
              </button>
              <button class="btn btn-light rounded-circle p-2 shadow" style="opacity:.9"
                @click.stop="alert('Partager la mission')">
                <Share2 style="width:20px;height:20px" />
              </button>
            </div>
            <div v-if="!canApply(mission)"
              class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center"
              style="top:0;left:0;background:rgba(0,0,0,.6)">
              <span class="badge bg-danger fs-6">Compétences requises manquantes</span>
            </div>
          </div>

          <div class="card-body d-flex flex-column gap-3">
            <div>
              <h5 class="fw-bold mb-1">{{ mission.name }}</h5>
              <div class="d-flex align-items-center gap-2">
                <Calendar style="width:16px;height:16px;color:#6b7280" />
                <button class="btn btn-link btn-sm p-0 text-decoration-none small"
                  @click.stop="router.push(`/event/${mission.eventId}`)">
                  {{ mission.eventName }}
                </button>
              </div>
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
                <span>{{ new Date(mission.date).toLocaleDateString('fr-FR') }} • {{ mission.startTime }} - {{ mission.endTime }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Users style="width:16px;height:16px" />
                <span>
                  {{ mission.currentVolunteers }}/{{ mission.maxVolunteers }} bénévoles
                  <span v-if="spotsLeft(mission) > 0 && spotsLeft(mission) <= 3"
                    class="text-warning fw-semibold ms-1">
                    • {{ spotsLeft(mission) }} places restantes
                  </span>
                </span>
              </div>
            </div>

            <button class="btn btn-primary w-100"
              @click.stop="router.push(`/mission/${mission.id}`)">
              Détails
            </button>
          </div>
        </div>
      </div>

      <div v-if="!isLoading && !loadError && filteredMissions.length === 0" class="text-center py-5 text-muted">
        Aucune mission trouvée
      </div>
    </div>

    <!-- Offcanvas Filtres (remplace Sheet) -->
    <Teleport to="body">
      <div v-if="showFilters" class="offcanvas-backdrop fade show" @click="showFilters = false"
        style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1040"></div>
      <div v-if="showFilters"
        class="offcanvas offcanvas-bottom show d-block rounded-top-4 p-4"
        style="position:fixed;bottom:0;left:0;right:0;z-index:1045;max-height:80vh;overflow-y:auto;background:white">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h5 class="mb-0">Filtres</h5>
          <button class="btn btn-link p-0 text-muted" @click="showFilters = false">
            <X style="width:20px;height:20px" />
          </button>
        </div>
        <p class="small text-muted mb-3">Personnalisez vos recherches</p>

        <div class="d-flex flex-column gap-3">
          <div>
            <label class="form-label small fw-medium">Type de mission</label>
            <select v-model="filterType" class="form-select">
              <option v-for="t in missionTypes" :key="t" :value="t">
                {{ t === 'all' ? 'Tous' : t }}
              </option>
            </select>
          </div>
          <div>
            <label class="form-label small fw-medium">Catégorie d'événement</label>
            <select v-model="filterCategory" class="form-select">
              <option v-for="c in eventCategories" :key="c" :value="c">
                {{ c === 'all' ? 'Toutes' : c }}
              </option>
            </select>
          </div>
          <div>
            <label class="form-label small fw-medium">Date</label>
            <select v-model="filterDate" class="form-select">
              <option value="all">Toutes</option>
              <option value="today">Aujourd'hui</option>
              <option value="week">Cette semaine</option>
              <option value="month">Ce mois</option>
            </select>
          </div>
          <div>
            <label class="form-label small fw-medium">Visibilité</label>
            <select v-model="filterVisibility" class="form-select">
              <option value="all">Toutes</option>
              <option value="public">Publiques</option>
              <option value="private">Privées</option>
            </select>
          </div>

          <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2"
            @click="clearFilters">
            <X style="width:18px;height:18px" />
            Effacer les filtres
          </button>
          <button class="btn btn-primary" @click="showFilters = false">Appliquer</button>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Search, MapPin, Users, Star, Share2, Clock, Calendar, Filter, X } from 'lucide-vue-next'
import { skills } from '@/data/mockData'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()
const searchQuery      = ref('')
const timelineFilter   = ref('upcoming')
const filterType       = ref('all')
const filterCategory   = ref('all')
const filterDate       = ref('all')
const filterVisibility = ref('all')
const showFilters      = ref(false)
const favorites        = ref([])
const missions         = ref([])
const events           = ref([])
const isLoading        = ref(false)
const loadError        = ref('')
const eventFilterId    = ref('')

const userSkillNames   = skills.map(s => s.name)
const missionTypes     = computed(() => ['all', ...new Set(missions.value.map(m => m.type))])
const eventCategories  = computed(() => ['all', ...new Set(events.value.map(e => e.category))])

const toTime = (value) => {
  if (!value || typeof value !== 'string') return ''
  return value.slice(0, 5)
}

const buildEventMap = (apiEvents) => {
  const map = new Map()
  for (const event of apiEvents) {
    map.set(event.id_evenement, {
      id: String(event.id_evenement),
      name: event.nom_evenement,
      category: 'Non défini',
    })
  }
  return map
}

const buildAffectationCountMap = (affectations) => {
  const map = new Map()
  for (const affectation of affectations) {
    const missionId = String(affectation.id_mission)
    map.set(missionId, (map.get(missionId) || 0) + 1)
  }
  return map
}

const mapMissionFromApi = (mission, eventMap, affectationCountMap) => {
  const event = eventMap.get(mission.id_evenement) || (mission.evenement
    ? {
        id: String(mission.evenement.id_evenement),
        name: mission.evenement.nom_evenement,
        category: 'Non défini',
      }
    : null)
  const missionId = String(mission.id_mission)

  return {
    id: missionId,
    eventId: event?.id || String(mission.id_evenement),
    eventName: event?.name || `Événement #${mission.id_evenement}`,
    name: mission.titre_mission,
    date: mission.date_mission,
    startTime: toTime(mission.heure_debut_mission),
    endTime: toTime(mission.heure_fin_mission),
    location: mission.lieu_mission,
    description: mission.description_mission,
    type: mission.type_mission || 'General',
    requiredSkills: [],
    currentVolunteers: Number(mission.current_volunteers_count ?? affectationCountMap.get(missionId) ?? 0),
    maxVolunteers: Number(mission.nombre_benevoles_max) || 0,
    imageUrl: null,
    isFavorite: false,
    visibility: mission.visibilite_mission === 'publique' ? 'public' : 'private',
  }
}

const loadMissions = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const [missionsResponse, eventsResponse, affectationsResponse] = await Promise.all([
      api.get('/missions'),
      api.get('/evenements'),
      api.get('/affectations'),
    ])

    const apiEvents = eventsResponse.data ?? []
    const eventMap = buildEventMap(apiEvents)
    const affectationCountMap = buildAffectationCountMap(affectationsResponse.data ?? [])

    events.value = apiEvents.map(event => ({
      id: String(event.id_evenement),
      name: event.nom_evenement,
      category: 'Non défini',
    }))

    missions.value = (missionsResponse.data ?? []).map(mission =>
      mapMissionFromApi(mission, eventMap, affectationCountMap)
    )

    favorites.value = missions.value
      .filter(mission => mission.isFavorite)
      .map(mission => mission.id)
  } catch (error) {
    console.error('Erreur lors du chargement des missions:', error)
    loadError.value = 'Impossible de charger les missions depuis le backend.'
  } finally {
    isLoading.value = false
  }
}

const activeFiltersCount = computed(() =>
  [filterType.value, filterCategory.value, filterDate.value, filterVisibility.value, eventFilterId.value ? 'event' : 'all']
    .filter(f => f !== 'all').length
)

const eventFilterName = computed(() =>
  events.value.find((event) => event.id === eventFilterId.value)?.name || `Événement #${eventFilterId.value}`
)

const filteredMissions = computed(() => {
  const today     = new Date()
  const nextWeek  = new Date(today); nextWeek.setDate(today.getDate() + 7)
  const nextMonth = new Date(today); nextMonth.setMonth(today.getMonth() + 1)
  const nowTs = Date.now()

  return missions.value.filter(m => {
    const q = searchQuery.value.toLowerCase()
    const matchSearch = [m.name, m.eventName, m.location].some(f => f.toLowerCase().includes(q))
    const matchType   = filterType.value === 'all' || m.type === filterType.value
    const ev          = events.value.find(e => e.id === m.eventId)
    const matchCat    = filterCategory.value === 'all' || ev?.category === filterCategory.value
    const d           = new Date(m.date)
    let matchDate     = true
    if (filterDate.value === 'today')  matchDate = d.toDateString() === today.toDateString()
    if (filterDate.value === 'week')   matchDate = d >= today && d <= nextWeek
    if (filterDate.value === 'month')  matchDate = d >= today && d <= nextMonth

    const missionEndDate = new Date(`${m.date}T${m.endTime || '23:59'}:00`)
    const isPast = missionEndDate.getTime() < nowTs
    const matchTimeline = timelineFilter.value === 'all'
      ? true
      : timelineFilter.value === 'past'
        ? isPast
        : !isPast

    const matchVis    = filterVisibility.value === 'all' || m.visibility === filterVisibility.value
    const matchEvent  = !eventFilterId.value || m.eventId === eventFilterId.value
    return matchSearch && matchType && matchCat && matchDate && matchTimeline && matchVis && matchEvent
  })
})

const spotsLeft  = (m) => m.maxVolunteers - m.currentVolunteers
const canApply   = (m) => m.requiredSkills.every(s => userSkillNames.includes(s)) && spotsLeft(m) > 0
const toggleFavorite = (id) => {
  const i = favorites.value.indexOf(id)
  i === -1 ? favorites.value.push(id) : favorites.value.splice(i, 1)
}
const clearFilters = () => {
  filterType.value = filterCategory.value = filterDate.value = filterVisibility.value = 'all'
  eventFilterId.value = ''
  if (route.query.eventId) {
    router.replace({ path: '/missions', query: {} })
  }
}

const applyEventFilterFromRoute = () => {
  const rawEventId = route.query.eventId
  const nextEventId = Array.isArray(rawEventId) ? String(rawEventId[0] || '') : String(rawEventId || '')
  eventFilterId.value = nextEventId
}

watch(() => route.query.eventId, applyEventFilterFromRoute)

onMounted(applyEventFilterFromRoute)
onMounted(loadMissions)
</script>