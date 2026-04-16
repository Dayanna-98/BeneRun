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

      <div v-if="isLoading" class="text-center text-muted py-3">
        Chargement des événements...
      </div>

      <div v-else-if="loadError" class="alert alert-danger mb-0">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <span>{{ loadError }}</span>
          <button class="btn btn-sm btn-outline-danger" @click="loadEvents">Réessayer</button>
        </div>
      </div>

      <template v-else>

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

      <!-- Liste -->
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Tous les événements</h5></div>
        <div class="card-body d-flex flex-column gap-3">
          <div v-for="event in eventsList" :key="event.id"
            class="border rounded overflow-hidden">

            <img :src="event.imageUrl || 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=1200&h=700&fit=crop'" :alt="event.name" class="w-100 object-fit-cover" style="height:128px" />

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
                    @click="handleDeleteEvent(event.id)">
                    <Trash2 style="width:16px;height:16px" />
                  </button>
                </div>
              </div>

              <div class="row g-2 small text-muted mb-3">
                <div class="col-6 d-flex align-items-center gap-2">
                  <Calendar style="width:16px;height:16px" />
                  <span class="x-small">{{ formatDate(event.date) }}</span>
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
import { ArrowLeft, Plus, Edit, Trash2, Calendar, MapPin, Users, Briefcase } from 'lucide-vue-next'
import api from '@/services/api'
import { getCurrentUser, hasMinRole } from '@/utils/auth'

const router = useRouter()
const user = getCurrentUser()
if (!user || !hasMinRole('admin')) router.push('/')

const eventsList = ref([])
const isLoading = ref(false)
const loadError = ref('')

const totalMissions   = computed(() => eventsList.value.reduce((s, e) => s + e.missionsCount, 0))
const totalVolunteers = computed(() => eventsList.value.reduce((s, e) => s + e.currentVolunteers, 0))
const totalPlaces     = computed(() => eventsList.value.reduce((s, e) => s + e.totalVolunteersNeeded, 0))

const completionRate = (e) => Math.round((e.currentVolunteers / e.totalVolunteersNeeded) * 100)

const formatDate = (d) =>
  new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })

const categoryBadgeClass = (cat) => ({
  Sport:     'bg-primary-subtle text-primary border-primary',
  Festival:  'bg-info-subtle text-info border-info',
  Salon:     'bg-warning-subtle text-warning border-warning',
  Écologie:  'bg-success-subtle text-success border-success',
}[cat] || 'bg-secondary-subtle text-secondary border-secondary')

const loadEvents = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const [eventsResponse, missionsResponse, affectationsResponse] = await Promise.all([
      api.get('/evenements'),
      api.get('/missions'),
      api.get('/affectations'),
    ])

    const events = eventsResponse.data ?? []
    const missions = missionsResponse.data ?? []
    const affectations = affectationsResponse.data ?? []

    const affectationsByMission = new Map()
    for (const affectation of affectations) {
      const missionId = Number(affectation.id_mission)
      affectationsByMission.set(missionId, (affectationsByMission.get(missionId) || 0) + 1)
    }

    eventsList.value = events.map((event) => {
      const eventMissions = missions.filter(m => Number(m.id_evenement) === Number(event.id_evenement))
      const currentVolunteers = eventMissions.reduce((sum, mission) => {
        return sum + (affectationsByMission.get(Number(mission.id_mission)) || 0)
      }, 0)

      return {
        id: String(event.id_evenement),
        name: event.nom_evenement,
        description: event.description_evenement,
        date: event.date_debut_evenement,
        location: event.lieu_evenement,
        imageUrl: event.image_evenement,
        category: 'Sport',
        organizer: event.organisateur_evenement || 'BeneRun',
        missionsCount: eventMissions.length,
        currentVolunteers,
        totalVolunteersNeeded: Number(event.nombre_benevoles_requis) || 0,
      }
    })
  } catch (error) {
    console.error('Erreur lors du chargement des événements:', error)
    loadError.value = 'Impossible de charger les événements depuis le backend.'
  } finally {
    isLoading.value = false
  }
}

const handleDeleteEvent = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ? Toutes les missions associées seront également supprimées.')) {
    try {
      await api.delete(`/evenements/${id}`)
      eventsList.value = eventsList.value.filter(e => e.id !== String(id))
      alert('Événement supprimé avec succès')
    } catch (error) {
      console.error('Erreur suppression événement:', error)
      alert('Impossible de supprimer cet événement.')
    }
  }
}

onMounted(loadEvents)
</script>