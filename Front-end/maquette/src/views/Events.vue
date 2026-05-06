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
        <p class="text-white-50 small mb-0">Découvrez les événements à venir</p>
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
          placeholder="Rechercher un événement..."
        />
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

      <div v-if="isLoading" class="text-center py-4 text-muted">
        Chargement des événements...
      </div>

      <div v-else-if="loadError" class="alert alert-danger" role="alert">
        {{ loadError }}
      </div>

      <!-- Event Cards -->
      <div v-if="!isLoading && !loadError" class="d-flex flex-column gap-4">
        <div v-for="event in filteredEvents" :key="event.id"
          class="card border-0 shadow overflow-hidden">

          <!-- Image -->
          <div class="position-relative" style="height:160px">
            <img
              :src="event.imageUrl || 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=800&h=400&fit=crop'"
              :alt="event.name"
              class="w-100 h-100 object-fit-cover"
            />
          </div>

          <!-- Content -->
          <div class="card-body d-flex flex-column gap-3">
            <div>
              <h5 class="fw-bold mb-1">{{ event.name }}</h5>
              <p class="small text-muted mb-0">{{ event.description }}</p>
            </div>

            <div class="d-flex flex-column gap-2 small text-muted">
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

            <button
              class="btn btn-primary w-100"
              @click="router.push(`/event/${event.id}`)">
              Détails
            </button>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="!isLoading && !loadError && filteredEvents.length === 0" class="text-center py-5 text-muted">
        Aucun événement trouvé
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Search, MapPin, Users, Clock } from 'lucide-vue-next'
import api from '@/services/api'
import eventService from '@/services/eventService'

const router = useRouter()
const searchQuery = ref('')
const timelineFilter = ref('upcoming')
const events = ref([])
const isLoading = ref(false)
const loadError = ref('')

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

onMounted(loadEvents)
</script>