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

      <!-- Filtres -->
      <div class="card">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-12 col-md-7">
              <input
                v-model="searchQuery"
                class="form-control"
                type="text"
                placeholder="Rechercher une mission, un lieu ou un événement..."
              />
            </div>
            <div class="col-12 col-md-5">
              <select v-model="selectedEventId" class="form-select">
                <option value="">Tous les événements</option>
                <option v-for="event in events" :key="event.id" :value="event.id">
                  {{ event.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="filter-pills mt-3">
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
        </div>
      </div>

      <!-- Stats -->
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary">{{ filteredMissions.length }}</div>
              <div class="x-small text-muted">Missions visibles</div>
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
          <CardListSkeleton v-if="isLoading" :count="3" :image-height="72" />
          <div v-else-if="errorMessage" class="alert alert-danger small mb-0">{{ errorMessage }}</div>
          <div v-for="mission in visibleMissions" :key="mission.id"
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
                  :disabled="!canDeleteMission(mission)"
                  :title="!canDeleteMission(mission) ? 'Suppression interdite pour traçabilité (mission en cours ou passée)' : 'Supprimer la mission'"
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
          <div v-if="hasMoreMissions" ref="sentinelRef" class="text-center py-2 text-muted small">
            Faites défiler ou chargez plus de missions.
          </div>
          <button v-if="hasMoreMissions" class="btn btn-outline-primary btn-sm w-100" @click="loadMoreMissions">
            Voir plus de missions
          </button>
          <EmptyState
            v-if="!isLoading && !errorMessage && filteredMissions.length === 0"
            icon="📌"
            title="Aucune mission trouvée"
            description="Ajustez la recherche ou le filtre d'événement pour voir plus de résultats."
          />
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Plus, Edit, Trash2, Users, Calendar, MapPin, Clock } from 'lucide-vue-next'
import missionService from '@/services/missionService'
import eventService from '@/services/eventService'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import CardListSkeleton from '@/components/ui/CardListSkeleton.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import { useInfiniteScroll } from '@/composables/useInfiniteScroll'

const router = useRouter()
const user = getCurrentUser()
if (!user || !hasMinRole('organizer')) router.push('/')

const missions = ref([])
const events = ref([])
const isLoading = ref(true)
const errorMessage = ref('')
const searchQuery = ref('')
const selectedEventId = ref('')
const timelineFilter = ref('upcoming')
const PAGE_SIZE = 8
const visibleCount = ref(PAGE_SIZE)

const normalizedMissionSearch = computed(() => searchQuery.value.trim().toLowerCase())

const filteredMissions = computed(() => {
  return missions.value.filter((mission) => {
    const matchEvent = !selectedEventId.value || mission.eventId === selectedEventId.value
    const matchSearch = !normalizedMissionSearch.value || [mission.name, mission.eventName, mission.location]
      .some((value) => String(value || '').toLowerCase().includes(normalizedMissionSearch.value))
    const missionEndDate = new Date(`${mission.date}T${mission.endTime || '23:59'}:00`)
    const isPast = missionEndDate.getTime() < Date.now()
    const matchTimeline = timelineFilter.value === 'all'
      ? true
      : timelineFilter.value === 'past'
        ? isPast
        : !isPast

    return matchEvent && matchSearch && matchTimeline
  })
})

const visibleMissions = computed(() => filteredMissions.value.slice(0, visibleCount.value))
const hasMoreMissions = computed(() => visibleCount.value < filteredMissions.value.length)
const loadMoreMissions = () => {
  if (hasMoreMissions.value) visibleCount.value += PAGE_SIZE
}
const { sentinelRef } = useInfiniteScroll({ canLoadMore: () => hasMoreMissions.value, onLoadMore: loadMoreMissions })

const getMissionStartDate = (mission) => new Date(`${mission.date}T${mission.startTime || '00:00'}:00`)
const canDeleteMission = (mission) => getMissionStartDate(mission).getTime() > Date.now()

const availableCount = computed(() => filteredMissions.value.filter(m => m.currentVolunteers < m.maxVolunteers).length)
const fullCount = computed(() => filteredMissions.value.filter(m => m.currentVolunteers === m.maxVolunteers).length)
const totalVolunteers = computed(() => filteredMissions.value.reduce((sum, mission) => sum + mission.currentVolunteers, 0))

const formatDate = (d) =>
  new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })

const loadData = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const [missionsPayload, eventsPayload] = await Promise.all([
      missionService.getAll(),
      eventService.getAll(),
    ])

    missions.value = missionsPayload
    events.value = eventsPayload
  } catch (error) {
    errorMessage.value = error.message || 'Impossible de charger les missions'
  } finally {
    isLoading.value = false
  }
}

const handleDeleteMission = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette mission ?')) {
    try {
      await missionService.delete(id)
      missions.value = missions.value.filter(m => m.id !== id)
      alert('Mission supprimée avec succès')
    } catch (error) {
      alert(error.message || 'Erreur lors de la suppression de la mission')
    }
  }
}

onMounted(loadData)
watch([searchQuery, selectedEventId, timelineFilter], () => {
  visibleCount.value = PAGE_SIZE
})
</script>