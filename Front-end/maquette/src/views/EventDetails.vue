<template>
  <div class="min-vh-100 bg-light pb-5">
    <div v-if="isLoading" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center text-muted">Chargement de l'événement...</div>
    </div>

    <div v-else-if="loadError" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-danger mb-3">{{ loadError }}</p>
        <button class="btn btn-primary" @click="loadEventDetails">Réessayer</button>
      </div>
    </div>

    <div v-else-if="!event" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-muted mb-3">Événement non trouvé</p>
        <button class="btn btn-primary" @click="router.push('/events')">Retour aux événements</button>
      </div>
    </div>

    <template v-else>
      <div class="position-relative" style="height:256px">
        <img
          :src="event.imageUrl || 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=800&h=400&fit=crop'"
          :alt="event.name"
          class="w-100 h-100 object-fit-cover"
        />
        <div
          class="position-absolute inset-0 w-100 h-100"
          style="background:linear-gradient(to top, rgba(0,0,0,.62), transparent)"
        ></div>

        <button
          class="btn btn-light position-absolute rounded-circle p-2 shadow"
          style="top:16px;left:16px;opacity:.92"
          @click="router.push('/events')"
        >
          <ArrowLeft style="width:24px;height:24px" />
        </button>

        <div class="position-absolute text-white px-3" style="bottom:16px;left:0;right:0">
          <h1 class="fs-4 fw-bold mb-1">{{ event.name }}</h1>
          <p class="small mb-0 text-white-50">Organisé par {{ event.organizer }}</p>
        </div>
      </div>

      <div class="px-3 pt-3 mx-auto d-flex flex-column gap-3" style="max-width:576px">
        <div v-if="actionSuccess" class="alert alert-success small mb-0">{{ actionSuccess }}</div>
        <div v-if="actionError" class="alert alert-danger small mb-0">{{ actionError }}</div>

        <div class="card">
          <div class="card-body d-flex flex-column gap-3">
            <p class="text-secondary mb-0">{{ event.description }}</p>

            <div class="d-flex flex-column gap-2 small text-muted">
              <div class="d-flex align-items-center gap-2">
                <Calendar style="width:16px;height:16px" />
                <span>{{ formatEventPeriod(event) }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <MapPin style="width:16px;height:16px" />
                <span>{{ event.location }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Users style="width:16px;height:16px" />
                <span>{{ event.currentVolunteers }}/{{ event.totalVolunteersNeeded }} bénévoles affectés</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Briefcase style="width:16px;height:16px" />
                <span>{{ eventMissions.length }} missions disponibles</span>
              </div>
            </div>

            <div>
              <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Progression de l'événement</span>
                <span>{{ completionPercentage }}%</span>
              </div>
              <div class="progress" style="height:10px">
                <div
                  class="progress-bar"
                  :style="`width:${completionPercentage}%;background:linear-gradient(to right,#d4e645,#a3c200)`"
                ></div>
              </div>
            </div>

            <div class="alert alert-warning small mb-0">
              <div class="d-flex align-items-start gap-2">
                <AlertTriangle style="width:16px;height:16px;flex-shrink:0;margin-top:2px" />
                <div class="d-flex flex-column gap-1">
                  <strong>Règles d'inscription à lire avant de cliquer</strong>
                  <span>1. S'inscrire à l'événement vous place en liste d'attente (pool) gérée par un superadmin.</span>
                  <span>2. Le superadmin vous dispatchera ensuite vers l'une des missions liées à cet événement.</span>
                  <span>3. Tant que vous êtes en liste d'attente, vous ne pouvez pas vous inscrire à un autre événement ou une autre mission sur le même créneau.</span>
                  <span>4. Une fois assigné à une mission, vous ne pouvez plus changer de mission ni vous désinscrire.</span>
                </div>
              </div>
            </div>

            <div class="d-grid gap-2">
              <button
                class="btn btn-primary"
                :disabled="isSubmittingEventSignup || !canSignupEvent"
                @click="handleEventSignup"
              >
                {{ isSubmittingEventSignup ? 'Inscription en cours...' : "S'inscrire à l'événement" }}
              </button>
              <button class="btn btn-outline-primary" @click="goToEventMissions">
                Voir/s'inscrire aux missions
              </button>
            </div>
          </div>
        </div>

        <div class="card" v-if="event.googleMapsUrl">
          <div class="card-header">
            <h5 class="mb-0">Carte de l'événement</h5>
          </div>
          <div class="card-body">
            <LeafletPreview
              :maps-url="event.googleMapsUrl"
              :radius-meters="event.radiusMeters"
              link-label="Ouvrir la carte de l'événement dans Google Maps"
            />
          </div>
        </div>

        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Missions de l'événement</h5>
            <span class="badge bg-secondary">{{ eventMissions.length }}</span>
          </div>
          <div class="card-body d-flex flex-column gap-3">
            <div v-if="eventMissions.length === 0" class="text-center text-muted py-4 small">
              Aucune mission disponible pour cet événement.
            </div>

            <div
              v-for="mission in eventMissions"
              :key="mission.id"
              class="border rounded p-3"
            >
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-semibold mb-0">{{ mission.name }}</h6>
                <span v-if="spotsLeft(mission) === 0" class="badge bg-secondary">Complet</span>
              </div>

              <div class="d-flex flex-column gap-1 small text-muted mb-3">
                <div class="d-flex align-items-center gap-2">
                  <Calendar style="width:12px;height:12px" />
                  <span>{{ formatMissionDate(mission.date) }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <Clock style="width:12px;height:12px" />
                  <span>{{ mission.startTime || 'Horaire à définir' }} - {{ mission.endTime || 'Horaire à définir' }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <MapPin style="width:12px;height:12px" />
                  <span>{{ mission.location || 'Lieu à définir' }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <Users style="width:12px;height:12px" />
                  <span>{{ mission.currentVolunteers }}/{{ mission.maxVolunteers }} bénévoles</span>
                </div>
              </div>

              <button class="btn btn-outline-primary btn-sm w-100" @click="router.push(`/mission/${mission.id}`)">
                Détails
              </button>
            </div>
          </div>
        </div>

        <div class="text-end">
          <a
            v-if="event.googleMapsUrl"
            :href="event.googleMapsUrl"
            class="small text-decoration-none"
            target="_blank"
            rel="noopener noreferrer"
          >
            Ouvrir le lieu dans Google Maps <ExternalLink style="width:14px;height:14px" />
          </a>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, MapPin, Calendar, Users, Briefcase, AlertTriangle, Clock, ExternalLink } from 'lucide-vue-next'
import api from '@/services/api'
import eventService from '@/services/eventService'
import { getCurrentUser } from '@/utils/auth'
import LeafletPreview from '@/components/maps/LeafletPreview.vue'

const router = useRouter()
const route = useRoute()

const event = ref(null)
const eventMissions = ref([])
const isLoading = ref(false)
const loadError = ref('')
const actionError = ref('')
const actionSuccess = ref('')
const isSubmittingEventSignup = ref(false)
const currentUser = ref(getCurrentUser())

const toTime = (value) => {
  if (!value || typeof value !== 'string') return ''
  return value.slice(0, 5)
}

const buildAffectationCountMap = (affectations) => {
  const map = new Map()
  for (const affectation of affectations) {
    if (!['assigne', 'confirme', 'present'].includes(affectation.statut_affectation)) continue
    const missionId = String(affectation.id_mission)
    map.set(missionId, (map.get(missionId) || 0) + 1)
  }
  return map
}

const mapMissionFromApi = (rawMission, affectationCountMap) => {
  const missionId = String(rawMission.id_mission)

  return {
    id: missionId,
    name: rawMission.titre_mission,
    date: rawMission.date_mission,
    startTime: toTime(rawMission.heure_debut_mission),
    endTime: toTime(rawMission.heure_fin_mission),
    location: rawMission.lieu_mission,
    currentVolunteers: Number(rawMission.current_volunteers_count ?? affectationCountMap.get(missionId) ?? 0),
    maxVolunteers: Number(rawMission.nombre_benevoles_max || 0),
  }
}

const loadEventDetails = async () => {
  isLoading.value = true
  loadError.value = ''
  actionError.value = ''
  actionSuccess.value = ''

  try {
    const eventId = String(route.params.id || '')

    const [eventResponse, missionsResponse, affectationsResponse] = await Promise.all([
      api.get(`/evenements/${eventId}`),
      api.get('/missions', { params: { id_evenement: eventId } }),
      api.get('/affectations'),
    ])

    const mappedEvent = eventService.mapApiEvent(eventResponse.data)
    if (!mappedEvent) {
      event.value = null
      return
    }

    const affectationCountMap = buildAffectationCountMap(affectationsResponse.data ?? [])
    const missions = (missionsResponse.data ?? []).map((mission) => mapMissionFromApi(mission, affectationCountMap))
    const assignedCount = missions.reduce((total, mission) => total + mission.currentVolunteers, 0)

    event.value = {
      ...mappedEvent,
      currentVolunteers: assignedCount,
    }
    eventMissions.value = missions
  } catch (error) {
    console.error('Erreur lors du chargement de l\'événement:', error)
    loadError.value = error.response?.data?.message || 'Impossible de charger les détails de l\'événement.'
  } finally {
    isLoading.value = false
  }
}

const completionPercentage = computed(() => {
  if (!event.value) return 0
  if (!event.value.totalVolunteersNeeded) return 0

  return Math.min(
    100,
    Math.round((event.value.currentVolunteers / event.value.totalVolunteersNeeded) * 100)
  )
})

const canSignupEvent = computed(() =>
  !!currentUser.value?.id
  && !!event.value
  && !event.value.isCancelled
)

const spotsLeft = (mission) => mission.maxVolunteers - mission.currentVolunteers

const formatMissionDate = (date) => {
  if (!date) return 'Date à définir'
  return new Date(date).toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

const formatEventPeriod = (targetEvent) => {
  if (!targetEvent) return ''

  const from = targetEvent.startDate || targetEvent.date
  const to = targetEvent.endDate || from
  const fromLabel = formatMissionDate(from)
  const toLabel = formatMissionDate(to)

  const startTime = targetEvent.startTime || '00:00'
  const endTime = targetEvent.endTime || '23:59'

  if (from === to) {
    return `${fromLabel} • ${startTime} - ${endTime}`
  }

  return `${fromLabel} au ${toLabel} • ${startTime} - ${endTime}`
}

const handleEventSignup = async () => {
  if (!event.value || !currentUser.value?.id) return

  actionError.value = ''
  actionSuccess.value = ''
  isSubmittingEventSignup.value = true

  try {
    await api.post(`/evenements/${event.value.id}/inscriptions`, {
      id_utilisateur: Number(currentUser.value.id),
      remarque: 'Inscription en liste d\'attente événement depuis la vue détails.',
    })

    actionSuccess.value = 'Vous êtes inscrit en liste d\'attente pour cet événement. Un superadmin vous assignera ensuite à une mission.'
  } catch (error) {
    actionError.value = error.response?.data?.message || 'Inscription impossible pour le moment.'
  } finally {
    isSubmittingEventSignup.value = false
  }
}

const goToEventMissions = () => {
  if (!event.value) return
  router.push({ path: '/missions', query: { eventId: event.value.id } })
}

onMounted(loadEventDetails)
</script>