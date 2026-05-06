<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Gestion des Événements</h1>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
          @click="router.push('/manage-events/create')">
          <Plus style="width:16px;height:16px" /> Créer
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:1024px">

      <!-- Stats -->
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary">{{ eventsList.length }}</div>
              <div class="x-small text-muted">Total événements</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-success">{{ totalMissions }}</div>
              <div class="x-small text-muted">Total missions</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary" style="color:#2563eb!important">{{ totalVolunteers }}</div>
              <div class="x-small text-muted">Bénévoles inscrits</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-warning">{{ totalPlaces }}</div>
              <div class="x-small text-muted">Places totales</div>
            </div>
          </div>
        </div>
      </div>

      <div class="filter-pills">
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
          Passés
        </button>
        <button
          class="btn btn-sm"
          :class="timelineFilter === 'all' ? 'btn-primary' : 'btn-outline-primary'"
          @click="timelineFilter = 'all'"
        >
          Tous
        </button>
      </div>

      <!-- Liste -->
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Tous les événements</h5></div>
        <div class="card-body d-flex flex-column gap-3">
          <CardListSkeleton v-if="isLoading" :count="3" :image-height="128" />
          <div v-else-if="errorMessage" class="alert alert-danger small mb-0">{{ errorMessage }}</div>
          <div v-for="event in visibleEvents" :key="event.id"
            class="border rounded overflow-hidden">

            <img :src="event.imageUrl" :alt="event.name" class="w-100 object-fit-cover" style="height:128px" />

            <div class="p-3">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="flex-fill">
                  <h6 class="fw-semibold text-primary mb-1">{{ event.name }}</h6>
                  <p class="small text-muted mb-0"
                    style="-webkit-line-clamp:2;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden">
                    {{ event.description }}
                  </p>
                </div>
                <div class="d-flex gap-1 ms-2">
                  <button class="btn btn-link p-1 text-secondary"
                    @click="router.push(`/manage-events/edit/${event.id}`)">
                    <Edit style="width:16px;height:16px" />
                  </button>
                  <button class="btn btn-link p-1 text-danger"
                    :disabled="!canDeleteEvent(event)"
                    :title="!canDeleteEvent(event) ? 'Suppression interdite pour traçabilité (événement en cours ou passé)' : 'Supprimer l\'événement'"
                    @click="handleDeleteEvent(event.id)">
                    <Trash2 style="width:16px;height:16px" />
                  </button>
                </div>
              </div>

              <div class="row g-2 small text-muted mb-3">
                <div class="col-6 d-flex align-items-center gap-2">
                  <Calendar style="width:16px;height:16px" />
                  <span class="x-small">{{ formatDateRange(event.startDate, event.endDate) }}</span>
                </div>
                <div class="col-6 d-flex align-items-center gap-2">
                  <MapPin style="width:16px;height:16px" />
                  <span class="x-small text-truncate">{{ event.location }}</span>
                </div>
                <div class="col-6 d-flex align-items-center gap-2">
                  <Briefcase style="width:16px;height:16px" />
                  <span class="x-small">{{ event.missionsCount }} missions</span>
                </div>
                <div class="col-6 d-flex align-items-center gap-2">
                  <Users style="width:16px;height:16px" />
                  <span class="x-small">{{ event.currentVolunteers }}/{{ event.totalVolunteersNeeded }}</span>
                </div>
                <div class="col-6 d-flex align-items-center gap-2">
                  <span class="badge" :class="event.isPublished ? 'bg-success-subtle text-success border border-success' : 'bg-secondary-subtle text-secondary border border-secondary'">
                    {{ event.isPublished ? 'Publié' : 'Brouillon' }}
                  </span>
                </div>
                <div class="col-6 d-flex align-items-center gap-2" v-if="event.isCancelled">
                  <span class="badge bg-danger-subtle text-danger border border-danger">Annulé</span>
                </div>
              </div>

              <!-- Progress -->
              <div class="mb-3">
                <div class="d-flex justify-content-between x-small text-muted mb-1">
                  <span>Taux de remplissage</span>
                  <span class="fw-medium">{{ completionRate(event) }}%</span>
                </div>
                <div class="progress" style="height:8px">
                  <div class="progress-bar bg-primary" :style="`width:${completionRate(event)}%`"></div>
                </div>
              </div>

              <div class="d-flex flex-wrap gap-2">
                <span :class="`badge border ${categoryBadgeClass(event.category)}`">{{ event.category }}</span>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary">{{ event.organizer }}</span>
              </div>

              <div class="d-flex flex-wrap gap-2 mt-3">
                <button
                  class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2"
                  @click="openWaitingListForEvent(event)"
                >
                  <Users style="width:14px;height:14px" />
                  Liste d'attente
                </button>
              </div>
            </div>
          </div>
          <div v-if="hasMoreEvents" ref="sentinelRef" class="text-center py-2 text-muted small">
            Faites défiler ou chargez plus d'événements.
          </div>
          <button v-if="hasMoreEvents" class="btn btn-outline-primary btn-sm w-100" @click="loadMoreEvents">
            Voir plus d'événements
          </button>
          <EmptyState
            v-if="!isLoading && !errorMessage && displayedEvents.length === 0"
            icon="🗂"
            title="Aucun événement à afficher"
            description="Créez un nouvel événement ou changez de filtre temporel."
          />
        </div>
      </div>

    </div>

    <!-- Modal Liste d'attente événement -->
    <Teleport to="body">
      <div v-if="showWaitingListModal" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
          <div class="modal-content border-0 shadow">
            <div class="modal-header">
              <div>
                <h5 class="modal-title mb-1">Liste d'attente de l'événement</h5>
                <div class="small text-muted">{{ selectedEventForQueue?.name }}</div>
              </div>
              <button class="btn-close" @click="closeWaitingListModal"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-3">
              <div class="alert alert-info small mb-0">
                Choisissez une mission rattachée à cet événement, puis assignez les bénévoles en attente en un clic.
              </div>

              <div class="row g-2">
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-medium">Mission de destination</label>
                  <select v-model="selectedMissionIdForQueue" class="form-select">
                    <option value="">Sélectionner une mission...</option>
                    <option v-for="mission in eventMissions" :key="mission.id" :value="mission.id">
                      {{ mission.name }} ({{ mission.currentVolunteers }}/{{ mission.maxVolunteers }})
                    </option>
                  </select>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-medium">Rechercher un bénévole</label>
                  <input
                    v-model="queueSearchQuery"
                    class="form-control"
                    type="text"
                    placeholder="Nom ou email..."
                  />
                </div>
              </div>

              <div v-if="waitingListSuccess" class="alert alert-success small mb-0">{{ waitingListSuccess }}</div>
              <div v-if="waitingListError" class="alert alert-danger small mb-0">{{ waitingListError }}</div>

              <div v-if="waitingListLoading" class="text-muted small">Chargement de la liste d'attente...</div>

              <div v-else-if="filteredWaitingList.length === 0" class="text-muted small">
                Aucun bénévole en attente pour cet événement.
              </div>

              <div v-else class="d-flex flex-column gap-2">
                <div
                  v-for="entry in filteredWaitingList"
                  :key="entry.id"
                  class="border rounded p-3 d-flex flex-column gap-2"
                >
                  <div class="d-flex align-items-start justify-content-between gap-2">
                    <div>
                      <div class="fw-semibold">{{ entry.fullName }}</div>
                      <div class="small text-muted">{{ entry.email || 'Email non disponible' }}</div>
                    </div>
                    <span class="badge bg-light text-dark border">En attente</span>
                  </div>

                  <div class="small text-muted d-flex flex-wrap gap-3">
                    <span>Inscription: {{ formatDateTime(entry.appliedAt) }}</span>
                    <span v-if="entry.remark">Remarque: {{ entry.remark }}</span>
                  </div>

                  <div class="d-flex justify-content-end">
                    <button
                      class="btn btn-primary btn-sm"
                      :disabled="!canAssignToSelectedMission || assigningPostulationId === entry.id"
                      @click="assignVolunteerToSelectedMission(entry)"
                    >
                      {{ assigningPostulationId === entry.id ? 'Assignation...' : 'Assigner à la mission sélectionnée' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <div v-if="selectedMissionIdForQueue && selectedMissionIsFull" class="small text-danger me-auto">
                La mission sélectionnée est complète.
              </div>
              <button class="btn btn-outline-secondary" @click="closeWaitingListModal">Fermer</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Plus, Edit, Trash2, Calendar, MapPin, Users, Briefcase } from 'lucide-vue-next'
import eventService from '@/services/eventService'
import missionService from '@/services/missionService'
import api from '@/services/api'
import chatService from '@/services/chatService'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import CardListSkeleton from '@/components/ui/CardListSkeleton.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import { useInfiniteScroll } from '@/composables/useInfiniteScroll'

const router = useRouter()
const user = getCurrentUser()
if (!user || !hasMinRole('admin')) router.push('/')

const eventsList = ref([])
const isLoading = ref(true)
const errorMessage = ref('')
const timelineFilter = ref('upcoming')
const showWaitingListModal = ref(false)
const selectedEventForQueue = ref(null)
const selectedMissionIdForQueue = ref('')
const eventMissions = ref([])
const waitingList = ref([])
const waitingListLoading = ref(false)
const waitingListError = ref('')
const waitingListSuccess = ref('')
const queueSearchQuery = ref('')
const assigningPostulationId = ref('')
const PAGE_SIZE = 6
const visibleCount = ref(PAGE_SIZE)

const totalMissions   = computed(() => eventsList.value.reduce((s, e) => s + e.missionsCount, 0))
const totalVolunteers = computed(() => eventsList.value.reduce((s, e) => s + e.currentVolunteers, 0))
const totalPlaces     = computed(() => eventsList.value.reduce((s, e) => s + e.totalVolunteersNeeded, 0))

const getEventStartDate = (event) => {
  const startDate = event.startDate || event.date
  const startTime = event.startTime || '00:00'
  return new Date(`${startDate}T${startTime}:00`)
}

const getEventEndDate = (event) => {
  const endDate = event.endDate || event.startDate || event.date
  const endTime = event.endTime || '23:59'
  return new Date(`${endDate}T${endTime}:00`)
}

const hasEventStarted = (event) => getEventStartDate(event).getTime() <= Date.now()
const isPastEvent = (event) => getEventEndDate(event).getTime() < Date.now()
const canDeleteEvent = (event) => !hasEventStarted(event)

const displayedEvents = computed(() => {
  if (timelineFilter.value === 'all') return eventsList.value
  if (timelineFilter.value === 'past') return eventsList.value.filter(isPastEvent)
  return eventsList.value.filter((event) => !isPastEvent(event))
})

const visibleEvents = computed(() => displayedEvents.value.slice(0, visibleCount.value))
const hasMoreEvents = computed(() => visibleCount.value < displayedEvents.value.length)
const loadMoreEvents = () => {
  if (hasMoreEvents.value) visibleCount.value += PAGE_SIZE
}
const { sentinelRef } = useInfiniteScroll({ canLoadMore: () => hasMoreEvents.value, onLoadMore: loadMoreEvents })

const selectedMissionForQueue = computed(() =>
  eventMissions.value.find((mission) => mission.id === selectedMissionIdForQueue.value) || null
)

const selectedMissionIsFull = computed(() => {
  const mission = selectedMissionForQueue.value
  if (!mission) return false
  return Number(mission.currentVolunteers || 0) >= Number(mission.maxVolunteers || 0)
})

const canAssignToSelectedMission = computed(() =>
  !!selectedMissionForQueue.value && !selectedMissionIsFull.value
)

const filteredWaitingList = computed(() => {
  const query = queueSearchQuery.value.trim().toLowerCase()
  if (!query) return waitingList.value

  return waitingList.value.filter((entry) =>
    [entry.fullName, entry.email].some((value) => String(value || '').toLowerCase().includes(query))
  )
})

const completionRate = (e) => {
  if (!e.totalVolunteersNeeded) return 0
  return Math.round((e.currentVolunteers / e.totalVolunteersNeeded) * 100)
}

const formatDate = (d) => {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

const formatDateRange = (start, end) => {
  const startLabel = formatDate(start)
  const endLabel = formatDate(end)
  if (startLabel === endLabel) return startLabel
  return `${startLabel} - ${endLabel}`
}

const formatDateTime = (value) => {
  if (!value) return 'Date inconnue'
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return 'Date inconnue'

  return parsed.toLocaleString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const categoryBadgeClass = (cat) => ({
  Sport:     'bg-primary-subtle text-primary border-primary',
  Festival:  'bg-info-subtle text-info border-info',
  Salon:     'bg-warning-subtle text-warning border-warning',
  Écologie:  'bg-success-subtle text-success border-success',
}[cat] || 'bg-secondary-subtle text-secondary border-secondary')

const loadEvents = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    eventsList.value = await eventService.getAll()
  } catch (error) {
    errorMessage.value = error.message || 'Impossible de charger les événements'
  } finally {
    isLoading.value = false
  }
}

const handleDeleteEvent = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ? Toutes les missions associées seront également supprimées.')) {
    try {
      await eventService.delete(id)
      eventsList.value = eventsList.value.filter(e => e.id !== id)
      alert('Événement supprimé avec succès')
    } catch (error) {
      alert(error.message || 'Erreur lors de la suppression de l\'événement')
    }
  }
}

const mapWaitingPostulation = (postulation) => {
  const userPayload = postulation.utilisateur || {}
  const firstName = userPayload.prenom_utilisateur || ''
  const lastName = userPayload.nom_utilisateur || ''
  const fullName = `${firstName} ${lastName}`.trim() || `Utilisateur #${postulation.id_utilisateur}`

  return {
    id: String(postulation.id_postulation),
    postulationId: Number(postulation.id_postulation),
    userId: String(postulation.id_utilisateur || ''),
    fullName,
    email: userPayload.email || '',
    appliedAt: postulation.date_postulation,
    remark: postulation.remarque || '',
  }
}

const loadEventQueueContext = async (event) => {
  waitingListLoading.value = true
  waitingListError.value = ''
  waitingListSuccess.value = ''

  try {
    const [missionsPayload, postulationsResponse] = await Promise.all([
      missionService.getAll({ id_evenement: event.id }),
      api.get('/postulations'),
    ])

    eventMissions.value = missionsPayload
    selectedMissionIdForQueue.value = missionsPayload[0]?.id || ''

    const payload = postulationsResponse.data
    const rows = Array.isArray(payload)
      ? payload
      : Array.isArray(payload?.data)
        ? payload.data
        : []

    waitingList.value = rows
      .filter((postulation) => String(postulation.id_evenement) === event.id)
      .filter((postulation) => !postulation.id_mission)
      .filter((postulation) => postulation.statut_postulation === 'en_attente')
      .map(mapWaitingPostulation)
      .sort((a, b) => {
        const dateA = new Date(a.appliedAt || 0).getTime()
        const dateB = new Date(b.appliedAt || 0).getTime()
        return dateA - dateB
      })
  } catch (error) {
    waitingListError.value = error.response?.data?.message || error.message || 'Impossible de charger la liste d\'attente.'
  } finally {
    waitingListLoading.value = false
  }
}

const openWaitingListForEvent = async (event) => {
  selectedEventForQueue.value = event
  queueSearchQuery.value = ''
  waitingList.value = []
  eventMissions.value = []
  showWaitingListModal.value = true
  await loadEventQueueContext(event)
}

const closeWaitingListModal = () => {
  showWaitingListModal.value = false
  selectedEventForQueue.value = null
  selectedMissionIdForQueue.value = ''
  eventMissions.value = []
  waitingList.value = []
  waitingListError.value = ''
  waitingListSuccess.value = ''
  queueSearchQuery.value = ''
  assigningPostulationId.value = ''
}

const assignVolunteerToSelectedMission = async (entry) => {
  if (!canAssignToSelectedMission.value) {
    waitingListError.value = 'Veuillez sélectionner une mission avec des places disponibles.'
    return
  }

  waitingListError.value = ''
  waitingListSuccess.value = ''
  assigningPostulationId.value = entry.id

  try {
    await api.put(`/postulations/${entry.postulationId}`, {
      id_mission: Number(selectedMissionForQueue.value.id),
      statut_postulation: 'accepte',
      remarque: `Assigné à la mission ${selectedMissionForQueue.value.name} depuis la gestion des événements.`,
    })

    chatService.addUserToMissionGroup({
      missionId: selectedMissionForQueue.value.id,
      eventId: selectedEventForQueue.value?.id || null,
      missionName: selectedMissionForQueue.value.name,
      eventName: selectedEventForQueue.value?.name || null,
      userId: entry.userId,
    })

    waitingList.value = waitingList.value.filter((postulation) => postulation.id !== entry.id)

    const missionIndex = eventMissions.value.findIndex((mission) => mission.id === selectedMissionForQueue.value.id)
    if (missionIndex !== -1) {
      const mission = eventMissions.value[missionIndex]
      eventMissions.value[missionIndex] = {
        ...mission,
        currentVolunteers: Number(mission.currentVolunteers || 0) + 1,
      }
    }

    waitingListSuccess.value = `${entry.fullName} a été assigné à ${selectedMissionForQueue.value.name}.`
    await loadEvents()
  } catch (error) {
    waitingListError.value = error.response?.data?.message || error.message || 'Assignation impossible pour le moment.'
  } finally {
    assigningPostulationId.value = ''
  }
}

onMounted(loadEvents)
watch(timelineFilter, () => {
  visibleCount.value = PAGE_SIZE
})
</script>