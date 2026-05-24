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
            <div class="x-small" style="color:rgba(255,255,255,.55)">Responsable</div>
            <h1 class="fs-5 fw-semibold mb-0">Mes missions</h1>
          </div>
        </div>
        <button class="btn btn-sm fw-semibold d-flex align-items-center gap-2 px-3 rounded-pill flex-shrink-0"
          style="background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230;border:none"
          @click="router.push('/manage-missions/create')">
          <Plus style="width:16px;height:16px" /> Créer
        </button>
      </div>

      <!-- Timeline tabs -->
      <div class="px-3 pb-3 d-flex gap-2">
        <button
          class="btn btn-sm rounded-pill px-3"
          :class="timelineFilter === 'upcoming' ? 'btn-light text-dark fw-semibold' : 'btn-outline-light'"
          @click="timelineFilter = 'upcoming'"
        >
          À venir <span v-if="upcomingCount > 0" class="badge bg-primary ms-1">{{ upcomingCount }}</span>
        </button>
        <button
          class="btn btn-sm rounded-pill px-3"
          :class="timelineFilter === 'past' ? 'btn-light text-dark fw-semibold' : 'btn-outline-light'"
          @click="timelineFilter = 'past'"
        >
          Passées <span v-if="pastCount > 0" class="badge bg-secondary ms-1">{{ pastCount }}</span>
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:800px">

      <!-- Barre de recherche -->
      <div class="card border-0 shadow-sm">
        <div class="card-body py-2">
          <input
            v-model="searchQuery"
            class="form-control"
            type="text"
            placeholder="Rechercher une mission, un lieu ou un événement..."
          />
        </div>
      </div>

      <!-- Stats -->
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card border-0 text-center" style="background:#e0e7ff">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary">{{ filteredMissions.length }}</div>
              <div class="x-small text-muted">Missions</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 text-center" style="background:#d1fae5">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-success">{{ availableCount }}</div>
              <div class="x-small text-muted">Places dispo</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 text-center" style="background:#fef3c7">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-warning">{{ fullCount }}</div>
              <div class="x-small text-muted">Complets</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 text-center" style="background:#dbeafe">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold" style="color:#2563eb">{{ totalVolunteers }}</div>
              <div class="x-small text-muted">Bénévoles</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Liste -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
          <h5 class="mb-0">
            {{ timelineFilter === 'upcoming' ? 'Missions à venir' : 'Missions passées' }}
          </h5>
          <span class="badge bg-secondary">{{ filteredMissions.length }}</span>
        </div>
        <div class="card-body d-flex flex-column gap-3">
          <CardListSkeleton v-if="isLoading" :count="3" :image-height="72" />
          <div v-else-if="errorMessage" class="alert alert-danger small mb-0">{{ errorMessage }}</div>

          <div v-for="mission in visibleMissions" :key="mission.id"
            class="border rounded p-3"
            :class="{ 'opacity-75': timelineFilter === 'past' }">

            <!-- En-tête mission -->
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="flex-fill">
                <h6 class="fw-semibold text-primary mb-1">{{ mission.name }}</h6>
                <p class="small text-muted mb-0">{{ mission.eventName }}</p>
              </div>
              <div class="d-flex gap-1 ms-2">
                <button class="btn btn-sm rounded-pill px-2" style="background:#f3f4f6;color:#4b5563;border:none"
                  @click="router.push(`/mission/${mission.id}`)">
                  <Eye style="width:16px;height:16px" />
                </button>
                <button class="btn btn-sm rounded-pill px-2" style="background:#eff6ff;color:#3b82f6;border:none"
                  @click="router.push(`/manage-missions/edit/${mission.id}`)">
                  <Edit style="width:16px;height:16px" />
                </button>
              </div>
            </div>

            <!-- Infos -->
            <div class="row g-2 small text-muted mb-2">
              <div class="col-6 d-flex align-items-center gap-2">
                <Calendar style="width:16px;height:16px" />
                <span class="x-small">{{ formatDate(mission.date) }}</span>
              </div>
              <div class="col-6 d-flex align-items-center gap-2">
                <Clock style="width:16px;height:16px" />
                <span class="x-small">{{ mission.startTime }} – {{ mission.endTime }}</span>
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

            <!-- Barre de progression bénévoles -->
            <div class="mb-2">
              <div class="progress" style="height:6px">
                <div class="progress-bar"
                  :class="mission.currentVolunteers >= mission.maxVolunteers ? 'bg-warning' : 'bg-success'"
                  :style="`width:${Math.min((mission.currentVolunteers / mission.maxVolunteers) * 100, 100)}%`">
                </div>
              </div>
            </div>

            <!-- Badges statut -->
            <div class="d-flex flex-wrap gap-2">
              <span class="badge border"
                :style="mission.postable ? 'background:#ecfdf5;color:#065f46;border-color:#6ee7b7' : 'background:#fff7ed;color:#9a3412;border-color:#fdba74'">
                {{ mission.postable ? 'Postable' : 'Inscriptions fermées' }}
              </span>
              <span class="badge border"
                :style="mission.visibility === 'public' ? 'background:#ecfeff;color:#0e7490;border-color:#67e8f9' : 'background:#f5f3ff;color:#5b21b6;border-color:#c4b5fd'">
                {{ mission.visibility === 'public' ? 'Publique' : 'Privée' }}
              </span>
              <span v-if="mission.currentVolunteers >= mission.maxVolunteers"
                class="badge bg-warning-subtle text-warning border border-warning">
                Complet
              </span>
              <span v-if="timelineFilter === 'past'"
                class="badge bg-secondary-subtle text-secondary border border-secondary">
                Terminée
              </span>
            </div>

          </div>

          <div v-if="hasMoreMissions" class="text-center py-2">
            <button class="btn btn-outline-primary btn-sm px-4" @click="loadMore">
              Voir plus
            </button>
          </div>

          <EmptyState
            v-if="!isLoading && !errorMessage && filteredMissions.length === 0"
            icon="📋"
            :title="timelineFilter === 'upcoming' ? 'Aucune mission à venir' : 'Aucune mission passée'"
            :description="timelineFilter === 'upcoming'
              ? 'Vous n\'avez pas de missions à venir sous votre responsabilité.'
              : 'Vous n\'avez pas encore de missions passées.'"
          />
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Plus, Edit, Eye, Users, Calendar, MapPin, Clock } from 'lucide-vue-next'
import missionService from '@/services/missionService'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import CardListSkeleton from '@/components/ui/CardListSkeleton.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import { parseLocalDateTime } from '@/utils/dateTime'

const router = useRouter()
const user = getCurrentUser()
if (!user || !hasMinRole('organizer')) router.push('/')

const missions = ref([])
const isLoading = ref(true)
const errorMessage = ref('')
const searchQuery = ref('')
const timelineFilter = ref('upcoming')

const PAGE_SIZE = 10
const visibleCount = ref(PAGE_SIZE)

const normalizedSearch = computed(() => searchQuery.value.trim().toLowerCase())

const isMissionPast = (mission) => {
  const end = parseLocalDateTime(mission.date, mission.endTime || '23:59')
  return end ? end.getTime() < Date.now() : false
}

const allUpcoming = computed(() => missions.value.filter(m => !isMissionPast(m)))
const allPast = computed(() => missions.value.filter(m => isMissionPast(m)))

const upcomingCount = computed(() => allUpcoming.value.length)
const pastCount = computed(() => allPast.value.length)

const filteredMissions = computed(() => {
  const base = timelineFilter.value === 'upcoming' ? allUpcoming.value : allPast.value
  if (!normalizedSearch.value) return base
  return base.filter(m =>
    [m.name, m.eventName, m.location].some(v => String(v || '').toLowerCase().includes(normalizedSearch.value))
  )
})

const visibleMissions = computed(() => filteredMissions.value.slice(0, visibleCount.value))
const hasMoreMissions = computed(() => visibleCount.value < filteredMissions.value.length)
const loadMore = () => { visibleCount.value += PAGE_SIZE }

const availableCount = computed(() => filteredMissions.value.filter(m => m.currentVolunteers < m.maxVolunteers).length)
const fullCount = computed(() => filteredMissions.value.filter(m => m.currentVolunteers >= m.maxVolunteers).length)
const totalVolunteers = computed(() => filteredMissions.value.reduce((sum, m) => sum + m.currentVolunteers, 0))

const formatDate = (d) =>
  (parseLocalDateTime(d, '00:00') || new Date()).toLocaleDateString('fr-FR', {
    day: 'numeric', month: 'short', year: 'numeric',
  })

const loadData = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    missions.value = await missionService.getAll({ responsable_id: user.id })
  } catch (err) {
    errorMessage.value = err.message || 'Impossible de charger les missions'
  } finally {
    isLoading.value = false
  }
}

// Reset pagination when filters change
watch([timelineFilter, searchQuery], () => { visibleCount.value = PAGE_SIZE })

onMounted(loadData)
</script>
