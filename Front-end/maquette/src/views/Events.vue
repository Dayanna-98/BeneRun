<template>
  <div class="events-view min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="events-hero text-white p-4 pb-4 position-relative overflow-hidden"
      style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">
      <div class="position-relative" style="z-index:1">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="bg-white rounded-3 p-2 shadow">
            <img src="@/assets/logo.png" alt="Béné'Run" style="height:40px;width:auto" />
          </div>
          <div>
            <div class="small text-white-50">Béné'Run</div>
            <div class="fs-4 fw-bold">Événements</div>
          </div>
        </div>

        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
          <div class="events-hero__copy">
            <p class="text-white-50 small text-uppercase fw-semibold mb-2">Vue d'ensemble</p>
            <p class="events-hero__lead mb-0">Explorez les temps forts à venir, repérez ceux qui ont besoin de renfort et plongez rapidement dans les missions liées.</p>
          </div>

          <div class="events-hero__summary">
            <div class="events-hero__metric">
              <strong>{{ filteredEvents.length }}</strong>
              <span>résultats</span>
            </div>
            <div class="events-hero__metric">
              <strong>{{ upcomingEventsCount }}</strong>
              <span>à venir</span>
            </div>
            <div class="events-hero__metric">
              <strong>{{ totalAssignedVolunteers }}</strong>
              <span>bénévoles</span>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="px-3 pt-3 mx-auto" style="max-width:576px">

      <section class="events-toolbar card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
          <div class="mb-3 position-relative">
            <Search class="position-absolute text-muted" style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
            <input
              v-model="searchQuery"
              type="text"
              class="form-control ps-5 shadow-sm"
              placeholder="Rechercher un événement, un lieu, un organisateur..."
            />
          </div>

          <div class="filter-pills mb-2">
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

          <div class="events-toolbar__caption small text-muted">
            {{ filteredEvents.length }} événement{{ filteredEvents.length > 1 ? 's' : '' }} visible{{ filteredEvents.length > 1 ? 's' : '' }}
          </div>
        </div>
      </section>

      <CardListSkeleton v-if="isLoading" :count="3" :image-height="160" />

      <div v-else-if="loadError" class="alert alert-danger" role="alert">
        {{ loadError }}
      </div>

      <!-- Event Cards -->
      <div v-if="!isLoading && !loadError" class="d-flex flex-column gap-4">
        <div v-for="event in visibleEvents" :key="event.id"
          class="event-card card border-0 shadow overflow-hidden">

          <!-- Image -->
          <div class="position-relative event-card__media" style="height:180px">
            <img
              :src="event.imageUrl || 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=800&h=400&fit=crop'"
              :alt="event.name"
              class="w-100 h-100 object-fit-cover"
            />
            <div class="event-card__gradient"></div>
            <div class="event-card__chips">
              <span class="badge text-bg-light border-0">{{ isPastEvent(event) ? 'Terminé' : 'Ouvert' }}</span>
              <span class="badge" :class="eventFillRate(event) >= 80 ? 'text-bg-warning' : 'text-bg-dark'">
                {{ eventFillRate(event) }}% pourvu
              </span>
            </div>
          </div>

          <!-- Content -->
          <div class="card-body d-flex flex-column gap-3">
            <div>
              <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                <div>
                  <h5 class="fw-bold mb-1">{{ event.name }}</h5>
                  <p class="small text-muted mb-0">{{ event.description }}</p>
                </div>
                <span class="event-card__date-badge">{{ formatEventBadge(event) }}</span>
              </div>
            </div>

            <div class="event-card__facts d-flex flex-column gap-2 small text-muted">
              <div class="d-flex align-items-center gap-2">
                <MapPin style="width:16px;height:16px" />
                <span>{{ event.location }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Clock style="width:16px;height:16px" />
                <span>{{ formatEventPeriod(event) }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Users style="width:16px;height:16px" />
                <span>
                  {{ event.currentVolunteers }}/{{ event.totalVolunteersNeeded }} bénévoles affectés
                </span>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button
                class="btn btn-primary flex-fill"
                @click="router.push(`/event/${event.id}`)">
                Voir l'événement
              </button>
              <button
                class="btn btn-outline-primary"
                @click="router.push(`/missions?eventId=${event.id}`)">
                Missions
              </button>
            </div>
          </div>
        </div>

        <div v-if="hasMoreEvents" ref="sentinelRef" class="text-center py-3 text-muted small">
          Faites défiler ou chargez plus d'événements.
        </div>
        <button v-if="hasMoreEvents" class="btn btn-outline-primary btn-sm w-100" @click="loadMoreEvents">
          Voir plus d'événements
        </button>
      </div>

      <!-- Empty state -->
      <EmptyState
        v-if="!isLoading && !loadError && filteredEvents.length === 0"
        icon="📅"
        title="Aucun événement trouvé"
        description="Essayez un autre mot-clé ou changez le filtre temporel."
      />

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Search, MapPin, Users, Clock } from 'lucide-vue-next'
import api from '@/services/api'
import eventService from '@/services/eventService'
import CardListSkeleton from '@/components/ui/CardListSkeleton.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import { useInfiniteScroll } from '@/composables/useInfiniteScroll'

const router = useRouter()
const searchQuery = ref('')
const timelineFilter = ref('upcoming')
const events = ref([])
const isLoading = ref(false)
const loadError = ref('')
const PAGE_SIZE = 6
const visibleCount = ref(PAGE_SIZE)

const loadEvents = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const [eventsResponse, missionsResponse, affectationsResponse] = await Promise.all([
      api.get('/evenements'),
      api.get('/missions'),
      api.get('/affectations'),
    ])

    const rawEvents = eventsResponse.data ?? []
    const rawMissions = missionsResponse.data ?? []
    const rawAffectations = affectationsResponse.data ?? []

    const assignmentByMission = new Map()
    for (const affectation of rawAffectations) {
      if (!['assigne', 'confirme', 'present'].includes(affectation.statut_affectation)) continue
      const missionId = String(affectation.id_mission)
      assignmentByMission.set(missionId, (assignmentByMission.get(missionId) || 0) + 1)
    }

    const assignmentsByEvent = new Map()
    for (const mission of rawMissions) {
      const missionId = String(mission.id_mission)
      const eventId = String(mission.id_evenement)
      const volunteersCount = Number(mission.current_volunteers_count ?? assignmentByMission.get(missionId) ?? 0)
      assignmentsByEvent.set(eventId, (assignmentsByEvent.get(eventId) || 0) + volunteersCount)
    }

    events.value = rawEvents.map((rawEvent) => {
      const mappedEvent = eventService.mapApiEvent(rawEvent)
      return {
        ...mappedEvent,
        currentVolunteers: assignmentsByEvent.get(mappedEvent.id) || 0,
      }
    })
  } catch (error) {
    console.error('Erreur lors du chargement des événements:', error)
    loadError.value = "Impossible de charger les événements depuis le backend."
  } finally {
    isLoading.value = false
  }
}

const getEventEndDate = (event) => {
  const endDate = event.endDate || event.startDate || event.date
  const endTime = event.endTime || '23:59'
  return new Date(`${endDate}T${endTime}:00`)
}

const isPastEvent = (event) => getEventEndDate(event).getTime() < Date.now()

const upcomingEventsCount = computed(() => events.value.filter((event) => !isPastEvent(event)).length)
const totalAssignedVolunteers = computed(() =>
  events.value.reduce((sum, event) => sum + Number(event.currentVolunteers || 0), 0)
)

const filteredEvents = computed(() =>
  events.value.filter((event) => {
    const matchesSearch = [event.name, event.description, event.location, event.organizer].some((field) =>
      String(field || '').toLowerCase().includes(searchQuery.value.toLowerCase())
    )

    const matchesTimeline = timelineFilter.value === 'all'
      ? true
      : timelineFilter.value === 'past'
        ? isPastEvent(event)
        : !isPastEvent(event)

    return matchesSearch && matchesTimeline
  })
)

const visibleEvents = computed(() => filteredEvents.value.slice(0, visibleCount.value))
const hasMoreEvents = computed(() => visibleCount.value < filteredEvents.value.length)
const loadMoreEvents = () => {
  if (hasMoreEvents.value) visibleCount.value += PAGE_SIZE
}
const { sentinelRef } = useInfiniteScroll({ canLoadMore: () => hasMoreEvents.value, onLoadMore: loadMoreEvents })

const formatDateLabel = (dateValue) => {
  if (!dateValue) return 'Date à définir'
  return new Date(dateValue).toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

const formatEventPeriod = (event) => {
  const startLabel = formatDateLabel(event.startDate || event.date)
  const endLabel = formatDateLabel(event.endDate || event.startDate || event.date)
  const startTime = event.startTime || '00:00'
  const endTime = event.endTime || '23:59'

  if ((event.startDate || event.date) === (event.endDate || event.startDate || event.date)) {
    return `${startLabel} • ${startTime} - ${endTime}`
  }

  return `${startLabel} au ${endLabel} • ${startTime} - ${endTime}`
}

const formatEventBadge = (event) => {
  const dateValue = event.startDate || event.date
  if (!dateValue) return 'À définir'

  const date = new Date(dateValue)
  const day = date.toLocaleDateString('fr-FR', { day: '2-digit' })
  const month = date.toLocaleDateString('fr-FR', { month: 'short' }).replace('.', '')
  return `${day} ${month}`
}

const eventFillRate = (event) => {
  const totalNeeded = Number(event.totalVolunteersNeeded || 0)
  if (!totalNeeded) return 0
  return Math.min(100, Math.round((Number(event.currentVolunteers || 0) / totalNeeded) * 100))
}

onMounted(loadEvents)

watch([searchQuery, timelineFilter], () => {
  visibleCount.value = PAGE_SIZE
})
</script>

<style scoped>
.events-hero__copy {
  max-width: 28rem;
}

.events-hero__lead {
  color: rgba(255,255,255,0.86);
  line-height: 1.5;
  max-width: 32rem;
}

.events-hero__summary {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.65rem;
  min-width: min(100%, 260px);
}

.events-hero__metric {
  padding: 0.9rem 0.8rem;
  border-radius: 18px;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.08);
  backdrop-filter: blur(10px);
}

.events-hero__metric strong {
  display: block;
  font-size: 1.1rem;
  line-height: 1;
}

.events-hero__metric span {
  display: block;
  margin-top: 0.35rem;
  font-size: 0.72rem;
  color: rgba(255,255,255,0.72);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.events-toolbar {
  margin-top: 0.35rem;
  position: relative;
  z-index: 2;
}

.events-toolbar__caption {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
}

.event-card {
  transition: transform var(--t-normal), box-shadow var(--t-normal);
}

.event-card:hover {
  transform: translateY(-3px);
}

.event-card__media {
  overflow: hidden;
}

.event-card__gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(26,34,48,0.05) 0%, rgba(26,34,48,0.45) 100%);
}

.event-card__chips {
  position: absolute;
  left: 12px;
  bottom: 12px;
  z-index: 1;
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
}

.event-card__date-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 56px;
  padding: 0.5rem 0.7rem;
  border-radius: 14px;
  background: rgba(44,53,73,0.08);
  color: var(--primary);
  font-size: 0.74rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
}

.event-card__facts {
  padding: 0.85rem;
  border-radius: 16px;
  background: rgba(44,53,73,0.035);
}

@media (max-width: 576px) {
  .events-hero__summary {
    width: 100%;
  }

  .events-toolbar {
    margin-top: 0.25rem;
  }
}
</style>