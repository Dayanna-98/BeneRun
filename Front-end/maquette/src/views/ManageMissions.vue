<template>
  <div class="min-vh-100 pb-5" style="background:#f0f4f8">

    <!-- Header -->
    <header class="text-white sticky-top overflow-hidden"
      style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-white" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <div>
            <div class="x-small" style="color:rgba(255,255,255,.55)">Administration</div>
            <h1 class="fs-5 fw-semibold mb-0">Gestion des Missions</h1>
          </div>
        </div>
      </div>
      <div class="px-3 pb-3 d-flex justify-content-end">
        <button class="btn btn-sm fw-semibold d-flex align-items-center gap-2 px-3 rounded-pill flex-shrink-0"
          style="background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230;border:none"
          @click="router.push('/manage-missions/create')">
          <Plus style="width:16px;height:16px" /> Créer
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:1024px">

      <!-- Filtres -->
      <div class="card border-0 shadow-sm">
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
              class="btn btn-sm rounded-pill px-3"
              :class="timelineFilter === 'upcoming' ? 'btn-primary' : 'btn-outline-primary'"
              @click="timelineFilter = 'upcoming'"
            >
              À venir
            </button>
            <button
              class="btn btn-sm rounded-pill px-3"
              :class="timelineFilter === 'past' ? 'btn-primary' : 'btn-outline-primary'"
              @click="timelineFilter = 'past'"
            >
              Passées
            </button>
            <button
              class="btn btn-sm rounded-pill px-3"
              :class="timelineFilter === 'all' ? 'btn-primary' : 'btn-outline-primary'"
              @click="timelineFilter = 'all'"
            >
              Toutes
            </button>
          </div>

          <div class="d-flex flex-wrap gap-3 mt-3 align-items-center">
            <div class="form-check mb-0">
              <input id="postableOnly" v-model="quickFilters.postableOnly" class="form-check-input" type="checkbox" />
              <label class="form-check-label small" for="postableOnly">Postables uniquement</label>
            </div>
            <div class="form-check mb-0">
              <input id="publicOnly" v-model="quickFilters.publicOnly" class="form-check-input" type="checkbox" />
              <label class="form-check-label small" for="publicOnly">Publiques uniquement</label>
            </div>
            <div class="form-check mb-0">
              <input id="availableOnly" v-model="quickFilters.availableOnly" class="form-check-input" type="checkbox" />
              <label class="form-check-label small" for="availableOnly">Avec places disponibles</label>
            </div>
            <button
              v-if="activeQuickFiltersCount > 0"
              class="btn btn-link btn-sm p-0"
              @click="resetQuickFilters"
            >
              Réinitialiser ({{ activeQuickFiltersCount }})
            </button>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary">{{ filteredMissions.length }}</div>
              <div class="x-small text-muted">Missions visibles</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-success">{{ availableCount }}</div>
              <div class="x-small text-muted">Places disponibles</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-warning">{{ fullCount }}</div>
              <div class="x-small text-muted">Complets</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold" style="color:#2563eb">{{ totalVolunteers }}</div>
              <div class="x-small text-muted">Bénévoles inscrits</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Liste -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0">Toutes les missions</h5></div>
        <div class="card-body d-flex flex-column gap-3">
          <CardListSkeleton v-if="isLoading" :count="3" :image-height="72" />
          <div v-else-if="errorMessage" class="alert alert-danger small mb-0">{{ errorMessage }}</div>
          <div v-for="mission in visibleMissions" :key="mission.id"
            class="border rounded p-3 mission-row">

            <div class="d-flex align-items-start justify-content-between mb-3">
              <div class="flex-fill">
                <h6 class="fw-semibold text-primary mb-1">{{ mission.name }}</h6>
                <p class="small text-muted mb-0">{{ mission.eventName }}</p>
              </div>
              <div class="d-flex gap-1">
                <button class="btn btn-sm rounded-pill px-2" style="background:#f3f4f6;color:#4b5563;border:none"
                  @click="router.push(`/manage-missions/edit/${mission.id}`)">
                  <Edit style="width:16px;height:16px" />
                </button>
                <button class="btn btn-sm rounded-pill px-2" style="background:#fff1f2;color:#ef4444;border:none"
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
              <span
                class="badge border"
                :style="mission.postable ? 'background:#ecfdf5;color:#065f46;border-color:#6ee7b7' : 'background:#fff7ed;color:#9a3412;border-color:#fdba74'"
              >
                {{ mission.postable ? 'Postable' : 'Inscriptions fermées' }}
              </span>
              <span
                class="badge border"
                :style="mission.visibility === 'public' ? 'background:#ecfeff;color:#0e7490;border-color:#67e8f9' : 'background:#f5f3ff;color:#5b21b6;border-color:#c4b5fd'"
              >
                {{ mission.visibility === 'public' ? 'Publique' : 'Privée' }}
              </span>
              <span v-if="mission.currentVolunteers === mission.maxVolunteers"
                class="badge bg-warning-subtle text-warning border border-warning">
                Complet
              </span>
            </div>

            <div class="mt-3 d-flex justify-content-end">
              <button
                class="btn btn-sm"
                :class="mission.postable ? 'btn-outline-warning' : 'btn-outline-success'"
                :disabled="isMissionPast(mission)"
                :title="isMissionPast(mission) ? 'Mission terminée' : ''"
                @click="handleTogglePostable(mission)"
              >
                {{ mission.postable ? 'Fermer les inscriptions' : 'Activer postable' }}
              </button>
              <button
                class="btn btn-sm"
                :class="mission.visibility === 'public' ? 'btn-outline-secondary' : 'btn-outline-info'"
                :disabled="isMissionPast(mission)"
                :title="isMissionPast(mission) ? 'Mission terminée' : ''"
                @click="handleToggleVisibility(mission)"
              >
                {{ mission.visibility === 'public' ? 'Passer en privée' : 'Activer publique' }}
              </button>
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
import { parseLocalDateTime } from '@/utils/dateTime'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const user = getCurrentUser()
const toast = useToast()
if (!user || !hasMinRole('organizer')) router.push('/')

const missions = ref([])
const events = ref([])
const isLoading = ref(true)
const errorMessage = ref('')
const searchQuery = ref('')
const selectedEventId = ref('')
const timelineFilter = ref('upcoming')
const quickFilters = ref({
  postableOnly: false,
  publicOnly: false,
  availableOnly: false,
})
const PAGE_SIZE = 8
const visibleCount = ref(PAGE_SIZE)

const normalizedMissionSearch = computed(() => searchQuery.value.trim().toLowerCase())

const filteredMissions = computed(() => {
  return missions.value.filter((mission) => {
    const matchEvent = !selectedEventId.value || mission.eventId === selectedEventId.value
    const matchSearch = !normalizedMissionSearch.value || [mission.name, mission.eventName, mission.location]
      .some((value) => String(value || '').toLowerCase().includes(normalizedMissionSearch.value))
    const missionEndDate = parseLocalDateTime(mission.date, mission.endTime || '23:59')
    const isPast = missionEndDate ? missionEndDate.getTime() < Date.now() : false
    const matchTimeline = timelineFilter.value === 'all'
      ? true
      : timelineFilter.value === 'past'
        ? isPast
        : !isPast

    const matchPostable = !quickFilters.value.postableOnly || mission.postable !== false
    const matchVisibility = !quickFilters.value.publicOnly || mission.visibility === 'public'
    const matchAvailability = !quickFilters.value.availableOnly || mission.currentVolunteers < mission.maxVolunteers

    return matchEvent && matchSearch && matchTimeline && matchPostable && matchVisibility && matchAvailability
  })
})

const visibleMissions = computed(() => filteredMissions.value.slice(0, visibleCount.value))
const hasMoreMissions = computed(() => visibleCount.value < filteredMissions.value.length)
const loadMoreMissions = () => {
  if (hasMoreMissions.value) visibleCount.value += PAGE_SIZE
}
const { sentinelRef } = useInfiniteScroll({ canLoadMore: () => hasMoreMissions.value, onLoadMore: loadMoreMissions })

const getMissionStartDate = (mission) => parseLocalDateTime(mission.date, mission.startTime || '00:00')
const isMissionPast = (mission) => {
  const end = parseLocalDateTime(mission.date, mission.endTime || '23:59')
  return end ? end.getTime() < Date.now() : false
}
const canDeleteMission = (mission) => {
  const start = getMissionStartDate(mission)
  return start ? start.getTime() > Date.now() : false
}

const availableCount = computed(() => filteredMissions.value.filter(m => m.currentVolunteers < m.maxVolunteers).length)
const fullCount = computed(() => filteredMissions.value.filter(m => m.currentVolunteers === m.maxVolunteers).length)
const totalVolunteers = computed(() => filteredMissions.value.reduce((sum, mission) => sum + mission.currentVolunteers, 0))
const activeQuickFiltersCount = computed(() =>
  [quickFilters.value.postableOnly, quickFilters.value.publicOnly, quickFilters.value.availableOnly]
    .filter(Boolean)
    .length
)

const formatDate = (d) =>
  (parseLocalDateTime(d, '00:00') || new Date()).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })

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
      toast.success('Mission supprimée avec succès.')
    } catch (error) {
      toast.error(error.message || 'Erreur lors de la suppression de la mission.')
    }
  }
}

const handleTogglePostable = async (mission) => {
  const nextPostable = !(mission.postable !== false)
  const confirmationMessage = nextPostable
    ? `Activer les inscriptions pour "${mission.name}" ?`
    : `Fermer les inscriptions pour "${mission.name}" ?`

  if (!confirm(confirmationMessage)) return

  try {
    await missionService.update(mission.id, {
      ...mission,
      postable: nextPostable,
    })

    mission.postable = nextPostable
    toast.success(nextPostable ? 'Option postable activée.' : 'Inscriptions fermées pour cette mission.')
  } catch (error) {
    toast.error(error.message || 'Impossible de mettre à jour l\'option postable.')
  }
}

const handleToggleVisibility = async (mission) => {
  const nextVisibility = mission.visibility === 'public' ? 'private' : 'public'
  const confirmationMessage = nextVisibility === 'public'
    ? `Rendre la mission "${mission.name}" publique ?`
    : `Passer la mission "${mission.name}" en privée ?`

  if (!confirm(confirmationMessage)) return

  try {
    await missionService.update(mission.id, {
      ...mission,
      public: nextVisibility === 'public',
    })

    mission.visibility = nextVisibility
    mission.public = nextVisibility === 'public'
    toast.success(nextVisibility === 'public' ? 'Mission rendue publique.' : 'Mission passée en privée.')
  } catch (error) {
    toast.error(error.message || 'Impossible de mettre à jour la visibilité.')
  }
}

const resetQuickFilters = () => {
  quickFilters.value.postableOnly = false
  quickFilters.value.publicOnly = false
  quickFilters.value.availableOnly = false
}

onMounted(loadData)
watch([
  searchQuery,
  selectedEventId,
  timelineFilter,
  () => quickFilters.value.postableOnly,
  () => quickFilters.value.publicOnly,
  () => quickFilters.value.availableOnly,
], () => {
  visibleCount.value = PAGE_SIZE
})
</script>

<style scoped>
.filter-pills {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.stat-card {
  border: 0;
  box-shadow: 0 6px 18px rgba(26, 34, 48, 0.08);
}

.mission-row {
  background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
  border-color: #e5e7eb !important;
}
</style>