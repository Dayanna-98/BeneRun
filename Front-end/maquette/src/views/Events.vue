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

          <div class="d-flex flex-wrap gap-2 mb-2">
            <button class="btn btn-sm rounded-pill" :class="quickNeedsVolunteers ? 'btn-success' : 'btn-outline-success'" @click="quickNeedsVolunteers = !quickNeedsVolunteers">
              Besoin de renfort
            </button>
            <button class="btn btn-sm rounded-pill" :class="quickThisWeek ? 'btn-info' : 'btn-outline-info'" @click="quickThisWeek = !quickThisWeek">
              Cette semaine
            </button>
            <button class="btn btn-sm rounded-pill" :class="quickLargeEvents ? 'btn-warning' : 'btn-outline-warning'" @click="quickLargeEvents = !quickLargeEvents">
              Capacité 20+
            </button>

            <button class="btn btn-light btn-sm d-flex align-items-center gap-2 ms-auto" @click="showFilters = !showFilters">
              <Filter style="width:16px;height:16px" />
              Filtres avancés
            </button>
          </div>

          <div v-if="showFilters" class="events-advanced-filters border rounded-3 p-3 mb-2">
            <div class="row g-2">
              <div class="col-12 col-md-6">
                <label class="form-label x-small text-muted mb-1">Organisateur</label>
                <select v-model="filterOrganizer" class="form-select form-select-sm">
                  <option value="all">Tous</option>
                  <option v-for="organizer in organizerOptions" :key="organizer" :value="organizer">{{ organizer }}</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label x-small text-muted mb-1">Période</label>
                <select v-model="filterDate" class="form-select form-select-sm">
                  <option value="all">Toutes les dates</option>
                  <option value="today">Aujourd'hui</option>
                  <option value="week">Cette semaine</option>
                  <option value="month">Ce mois</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label x-small text-muted mb-1">Besoin bénévoles</label>
                <select v-model="filterNeed" class="form-select form-select-sm">
                  <option value="all">Tous</option>
                  <option value="needs_volunteers">Besoin de renfort</option>
                  <option value="almost_full">Presque complet</option>
                  <option value="full">Complet</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label x-small text-muted mb-1">Trier par</label>
                <select v-model="sortBy" class="form-select form-select-sm">
                  <option value="date_asc">Date la plus proche</option>
                  <option value="fill_rate_asc">Priorité renfort</option>
                  <option value="volunteers_desc">Plus de bénévoles</option>
                </select>
              </div>
            </div>
          </div>

          <div v-if="activeFilterChips.length > 0" class="active-filters mb-2">
            <button
              v-for="chip in activeFilterChips"
              :key="chip.key"
              class="active-filter-chip"
              @click="clearSingleFilter(chip.key)">
              {{ chip.label }} ×
            </button>
            <button class="btn btn-link btn-sm text-decoration-none px-1" @click="clearAllFilters">
              Réinitialiser tout
            </button>
          </div>

          <div class="events-toolbar__caption small text-muted">
            {{ filteredEvents.length }} événement{{ filteredEvents.length > 1 ? 's' : '' }} visible{{ filteredEvents.length > 1 ? 's' : '' }}
            <span v-if="activeFilterChips.length > 0">• {{ activeFilterChips.length }} filtre{{ activeFilterChips.length > 1 ? 's' : '' }} actif{{ activeFilterChips.length > 1 ? 's' : '' }}</span>
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
import { Search, MapPin, Users, Clock, Filter } from 'lucide-vue-next'
import api from '@/services/api'
import eventService from '@/services/eventService'
import CardListSkeleton from '@/components/ui/CardListSkeleton.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import { useInfiniteScroll } from '@/composables/useInfiniteScroll'

const router = useRouter()
const searchQuery = ref('')
const timelineFilter = ref('upcoming')
const showFilters = ref(false)
const filterDate = ref('all')
const filterNeed = ref('all')
const filterOrganizer = ref('all')
const sortBy = ref('date_asc')
const quickNeedsVolunteers = ref(false)
const quickThisWeek = ref(false)
const quickLargeEvents = ref(false)
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
const organizerOptions = computed(() =>
  [...new Set(events.value.map((event) => String(event.organizer || '').trim()).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'fr'))
)

const activeFilterChips = computed(() => {
  const chips = []
  if (searchQuery.value.trim()) chips.push({ key: 'search', label: `Recherche: ${searchQuery.value.trim()}` })
  if (timelineFilter.value !== 'upcoming') chips.push({ key: 'timeline', label: timelineFilter.value === 'past' ? 'Passés' : 'Toutes périodes' })
  if (quickNeedsVolunteers.value) chips.push({ key: 'quickNeeds', label: 'Besoin de renfort' })
  if (quickThisWeek.value) chips.push({ key: 'quickWeek', label: 'Cette semaine' })
  if (quickLargeEvents.value) chips.push({ key: 'quickLarge', label: 'Capacité >= 20 bénévoles' })
  if (filterDate.value !== 'all') chips.push({ key: 'date', label: `Date: ${filterDate.value}` })
  if (filterNeed.value !== 'all') chips.push({ key: 'need', label: `Besoin: ${filterNeed.value}` })
  if (filterOrganizer.value !== 'all') chips.push({ key: 'organizer', label: `Organisateur: ${filterOrganizer.value}` })
  if (sortBy.value !== 'date_asc') chips.push({ key: 'sort', label: 'Tri personnalisé' })
  return chips
})

const eventStartDate = (event) => new Date(`${event.startDate || event.date}T${event.startTime || '00:00'}:00`)

const isWithinCurrentWeek = (event) => {
  const now = new Date()
  const start = new Date(now)
  const day = start.getDay()
  const offset = day === 0 ? 6 : day - 1
  start.setDate(start.getDate() - offset)
  start.setHours(0, 0, 0, 0)

  const end = new Date(start)
  end.setDate(end.getDate() + 7)
  end.setHours(23, 59, 59, 999)

  const eventDate = eventStartDate(event)
  return eventDate >= start && eventDate <= end
}

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

    const today = new Date()
    const thisMonthEnd = new Date(today)
    thisMonthEnd.setMonth(today.getMonth() + 1)

    const startDate = eventStartDate(event)
    const matchesDate = filterDate.value === 'all'
      ? true
      : filterDate.value === 'today'
        ? startDate.toDateString() === today.toDateString()
        : filterDate.value === 'week'
          ? isWithinCurrentWeek(event)
          : startDate >= today && startDate <= thisMonthEnd

    const fill = eventFillRate(event)
    const spotsOpen = Number(event.currentVolunteers || 0) < Number(event.totalVolunteersNeeded || 0)
    const matchesNeed = filterNeed.value === 'all'
      ? true
      : filterNeed.value === 'needs_volunteers'
        ? spotsOpen
        : filterNeed.value === 'almost_full'
          ? spotsOpen && fill >= 80
          : !spotsOpen

    const matchesOrganizer = filterOrganizer.value === 'all' || event.organizer === filterOrganizer.value

    const matchesQuickNeeds = !quickNeedsVolunteers.value || spotsOpen
    const matchesQuickWeek = !quickThisWeek.value || isWithinCurrentWeek(event)
    const matchesQuickLarge = !quickLargeEvents.value || Number(event.totalVolunteersNeeded || 0) >= 20

    return matchesSearch && matchesTimeline && matchesDate && matchesNeed && matchesOrganizer && matchesQuickNeeds && matchesQuickWeek && matchesQuickLarge
  })
)

const sortedEvents = computed(() => {
  const rows = [...filteredEvents.value]
  if (sortBy.value === 'fill_rate_asc') {
    return rows.sort((a, b) => eventFillRate(a) - eventFillRate(b))
  }
  if (sortBy.value === 'volunteers_desc') {
    return rows.sort((a, b) => Number(b.currentVolunteers || 0) - Number(a.currentVolunteers || 0))
  }
  return rows.sort((a, b) => eventStartDate(a).getTime() - eventStartDate(b).getTime())
})

const visibleEvents = computed(() => sortedEvents.value.slice(0, visibleCount.value))
const hasMoreEvents = computed(() => visibleCount.value < sortedEvents.value.length)
const loadMoreEvents = () => {
  if (hasMoreEvents.value) visibleCount.value += PAGE_SIZE
}
const { sentinelRef } = useInfiniteScroll({ canLoadMore: () => hasMoreEvents.value, onLoadMore: loadMoreEvents })

const clearSingleFilter = (key) => {
  if (key === 'search') searchQuery.value = ''
  if (key === 'timeline') timelineFilter.value = 'upcoming'
  if (key === 'quickNeeds') quickNeedsVolunteers.value = false
  if (key === 'quickWeek') quickThisWeek.value = false
  if (key === 'quickLarge') quickLargeEvents.value = false
  if (key === 'date') filterDate.value = 'all'
  if (key === 'need') filterNeed.value = 'all'
  if (key === 'organizer') filterOrganizer.value = 'all'
  if (key === 'sort') sortBy.value = 'date_asc'
}

const clearAllFilters = () => {
  searchQuery.value = ''
  timelineFilter.value = 'upcoming'
  showFilters.value = false
  filterDate.value = 'all'
  filterNeed.value = 'all'
  filterOrganizer.value = 'all'
  sortBy.value = 'date_asc'
  quickNeedsVolunteers.value = false
  quickThisWeek.value = false
  quickLargeEvents.value = false
}

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

watch([searchQuery, timelineFilter, filterDate, filterNeed, filterOrganizer, sortBy, quickNeedsVolunteers, quickThisWeek, quickLargeEvents], () => {
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
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.65rem;
  min-width: min(100%, 190px);
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

.events-advanced-filters {
  background: #fafcff;
  border-color: #dbe7f4 !important;
}

.active-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.active-filter-chip {
  border: 1px solid #cfdcf0;
  background: #f1f6ff;
  color: #274472;
  border-radius: 999px;
  font-size: 0.75rem;
  padding: 0.2rem 0.55rem;
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