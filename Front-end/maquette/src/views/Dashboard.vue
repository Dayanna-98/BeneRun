<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="text-white p-4 pb-5 position-relative overflow-hidden"
      style="background: linear-gradient(135deg, #1a2230 0%, #2d3a4a 100%)">
      <div class="position-relative" style="z-index:1">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="bg-white rounded-3 p-2 shadow">
            <img src="@/assets/logo.png" alt="Béné'Run" style="height:40px;width:auto" />
          </div>
          <div>
            <div class="small text-white-50 fw-medium">Béné'Run</div>
            <div class="small fw-bold text-warning">{{ user.accountType }}</div>
          </div>
        </div>
        <h1 class="fs-4 fw-bold mb-1">Bonjour {{ user.firstName }} 👋</h1>
        <p class="text-white-50 small mb-0">
          <span v-if="user.role === 'volunteer'">Prêt(e) à faire la différence aujourd'hui ?</span>
          <span v-else-if="user.role === 'mission_manager'">Gérez vos missions efficacement</span>
          <span v-else-if="user.role === 'admin'">Pilotez vos événements avec succès</span>
          <span v-else-if="user.role === 'superadmin'">Gérez la plateforme en toute simplicité</span>
        </p>
      </div>
    </header>

    <div class="px-3 pt-3 mx-auto" style="max-width:576px">

      <!-- Stats -->
      <div class="row g-3 mb-4">
        <div class="col-4">
          <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-primary">{{ totalMissions }}</div>
            <div class="small text-muted">Missions</div>
          </div>
        </div>
        <div class="col-4">
          <div class="card border-0 shadow-sm text-center p-3" style="background:linear-gradient(135deg,#e8f5a3,#d4e645)">
            <div class="fs-2 fw-bold text-dark">{{ badges.length }}</div>
            <div class="small text-dark opacity-75">Badges</div>
          </div>
        </div>
        <div class="col-4">
          <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-warning">{{ myActiveMissions.length }}</div>
            <div class="small text-muted">En cours</div>
          </div>
        </div>
      </div>

      <!-- Prochaine mission -->
      <div v-if="nextMission" class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="text-white p-3" style="background:linear-gradient(135deg,#1a2230,#2d3a4a)">
          <div class="d-flex align-items-center gap-2 mb-1">
            <Calendar style="width:20px;height:20px;color:#d4e645" />
            <h2 class="fs-5 fw-bold mb-0">Prochaine Mission</h2>
          </div>
          <p class="small text-white-50 mb-0">Votre prochaine aventure vous attend</p>
        </div>
        <div class="card-body d-flex flex-column gap-2">
          <div>
            <div class="fw-medium">{{ nextMission.name }}</div>
            <div class="small text-muted">{{ nextMission.eventName }}</div>
          </div>
          <div class="d-flex align-items-center gap-2 small text-muted">
            <Calendar style="width:16px;height:16px" />
            <span>{{ formatDate(nextMission.date) }} • {{ nextMission.startTime }}</span>
          </div>
          <div class="d-flex align-items-center gap-2 small text-muted">
            <MapPin style="width:16px;height:16px" />
            <span>{{ nextMission.location }}</span>
          </div>
          <button class="btn btn-primary w-100 mt-1" @click="router.push(`/mission/${nextMission.id}`)">
            Voir les détails
          </button>
        </div>
      </div>

      <!-- Missions suggérées (bénévoles) -->
      <div v-if="user.role === 'volunteer' && suggestedMissions.length > 0" class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="p-3" style="background:linear-gradient(135deg,#e8f5a3,#d4e645)">
          <div class="d-flex align-items-center gap-2 mb-1">
            <Star style="width:20px;height:20px" />
            <h2 class="fs-5 fw-bold mb-0">Missions Recommandées</h2>
          </div>
          <p class="small opacity-75 mb-0">Ces missions pourraient vous intéresser</p>
        </div>
        <div class="card-body p-0">
          <div v-for="(mission, index) in suggestedMissions" :key="mission.id"
            :class="['p-3', index < suggestedMissions.length - 1 ? 'border-bottom' : '']">
            <div class="d-flex gap-3">
              <img :src="mission.imageUrl" :alt="mission.name" class="rounded object-fit-cover" style="width:80px;height:80px" />
              <div class="flex-fill min-w-0">
                <div class="fw-medium small mb-1">{{ mission.name }}</div>
                <div class="x-small text-muted mb-2">{{ mission.eventName }}</div>
                <div class="d-flex align-items-center gap-1 x-small text-muted mb-2">
                  <Calendar style="width:12px;height:12px" />
                  <span>{{ formatDateShort(mission.date) }} • {{ mission.startTime }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-fill" style="height:6px">
                    <div class="progress-bar" :style="`width:${(mission.currentVolunteers/mission.maxVolunteers)*100}%`"></div>
                  </div>
                  <span class="x-small text-muted text-nowrap">{{ mission.currentVolunteers }}/{{ mission.maxVolunteers }}</span>
                </div>
              </div>
            </div>
            <button class="btn btn-outline-primary btn-sm w-100 mt-2" @click="router.push(`/mission/${mission.id}`)">
              Voir détails
            </button>
          </div>
          <div class="p-3">
            <button class="btn btn-primary w-100" @click="router.push('/missions')">Voir toutes les missions</button>
          </div>
        </div>
      </div>

      <!-- Missions gérées (responsable/admin/superadmin) -->
      <div v-if="isManager && managedMissions.length > 0" class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="text-white p-3" style="background:linear-gradient(135deg,#1a2230,#2d3a4a)">
          <div class="d-flex align-items-center gap-2 mb-1">
            <Shield style="width:20px;height:20px;color:#d4e645" />
            <h2 class="fs-5 fw-bold mb-0">Missions sous votre responsabilité</h2>
          </div>
          <p class="small text-white-50 mb-0">Suivez vos missions en cours</p>
        </div>
        <div class="card-body p-0">
          <div v-for="(mission, index) in managedMissions" :key="mission.id"
            :class="['p-3', index < managedMissions.length - 1 ? 'border-bottom' : '']">
            <div class="d-flex gap-3">
              <img :src="mission.imageUrl" :alt="mission.name" class="rounded object-fit-cover" style="width:80px;height:80px" />
              <div class="flex-fill min-w-0">
                <div class="fw-medium small mb-1">{{ mission.name }}</div>
                <div class="x-small text-muted mb-2">{{ mission.eventName }}</div>
                <div class="d-flex align-items-center gap-1 x-small text-muted mb-2">
                  <Calendar style="width:12px;height:12px" />
                  <span>{{ formatDateShort(mission.date) }} • {{ mission.startTime }}</span>
                </div>
                <div class="d-flex justify-content-between x-small mb-1">
                  <span class="text-muted">Progression</span>
                  <span class="fw-medium text-primary">{{ mission.progress }}%</span>
                </div>
                <div class="progress mb-1" style="height:8px">
                  <div class="progress-bar" :style="`width:${mission.progress}%`"></div>
                </div>
                <div class="x-small text-muted">{{ mission.currentVolunteers }}/{{ mission.maxVolunteers }} bénévoles</div>
              </div>
            </div>
            <button class="btn btn-outline-primary btn-sm w-100 mt-2" @click="router.push(`/mission/${mission.id}`)">
              Gérer la mission
            </button>
          </div>
          <div class="p-3">
            <button class="btn btn-primary w-100" @click="router.push('/manage-missions')">Toutes mes missions</button>
          </div>
        </div>
      </div>

      <!-- Derniers badges -->
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
          <Award style="width:20px;height:20px;color:#ca8a04" />
          <h5 class="mb-0">Derniers Badges</h5>
        </div>
        <div class="card-body">
          <div class="d-flex gap-3 overflow-auto pb-2">
            <div v-for="badge in badges.slice(0, 4)" :key="badge.id"
              class="d-flex flex-column align-items-center text-center flex-shrink-0" style="min-width:80px">
              <div class="rounded-circle d-flex align-items-center justify-content-center mb-2 fs-2"
                style="width:64px;height:64px;background:linear-gradient(135deg,#fef9c3,#fef08a)">
                {{ badge.icon }}
              </div>
              <div class="x-small fw-medium">{{ badge.name }}</div>
            </div>
          </div>
          <button class="btn btn-link w-100 mt-2 text-decoration-none" @click="router.push('/profile')">
            Voir tous les badges
          </button>
        </div>
      </div>

      <!-- Actions rapides -->
      <div class="row g-3 mb-4">
        <div class="col-6">
          <button class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-2 py-3" @click="router.push('/events')">
            <TrendingUp style="width:24px;height:24px" />
            <span class="small">Nouveaux Événements</span>
          </button>
        </div>
        <div class="col-6">
          <button class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-2 py-3" @click="router.push('/my-missions')">
            <Briefcase style="width:24px;height:24px" />
            <span class="small">Mes Missions</span>
          </button>
        </div>
        <div class="col-12">
          <button class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-2 py-3" @click="router.push('/favorites')">
            <Heart style="width:24px;height:24px" />
            <span class="small">Mes Favoris</span>
          </button>
        </div>
      </div>

      <!-- Actions rôles -->
      <div v-if="isManager" class="card mb-4 border-primary border-opacity-25">
        <div class="card-header d-flex align-items-center gap-2">
          <Settings style="width:20px;height:20px;color:#1a2230" />
          <h5 class="mb-0">Espace gestion</h5>
        </div>
        <div class="card-body">
          <div v-if="user.role === 'superadmin'" class="d-flex gap-2 mb-3">
            <button
              class="btn btn-sm flex-fill"
              :class="superAdminPanelTab === 'management' ? 'btn-dark' : 'btn-outline-secondary'"
              @click="superAdminPanelTab = 'management'">
              Gestion
            </button>
            <button
              class="btn btn-sm flex-fill position-relative"
              :class="superAdminPanelTab === 'emergency' ? 'btn-danger' : 'btn-outline-danger'"
              @click="superAdminPanelTab = 'emergency'">
              Urgence
              <span v-if="openEmergencyCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                {{ openEmergencyCount }}
              </span>
            </button>
          </div>

          <div v-if="user.role !== 'superadmin' || superAdminPanelTab === 'management'" class="row g-3">
            <div class="col-6">
              <button class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-2 py-3" @click="router.push('/manage-missions')">
                <Briefcase style="width:24px;height:24px;color:#1a2230" />
                <span class="x-small">Gérer Missions</span>
              </button>
            </div>
            <div v-if="user.role === 'admin' || user.role === 'superadmin'" class="col-6">
              <button class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-2 py-3" @click="router.push('/manage-events')">
                <Calendar style="width:24px;height:24px;color:#1a2230" />
                <span class="x-small">Gérer Événements</span>
              </button>
            </div>
            <template v-if="user.role === 'superadmin'">
              <div class="col-6">
                <button class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-2 py-3" @click="router.push('/manage-users')">
                  <Users style="width:24px;height:24px;color:#1a2230" />
                  <span class="x-small">Gérer Comptes</span>
                </button>
              </div>
              <div class="col-6">
                <button class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-2 py-3" @click="router.push('/statistics')">
                  <BarChart3 style="width:24px;height:24px;color:#1a2230" />
                  <span class="x-small">Statistiques</span>
                </button>
              </div>
              <div class="col-6">
                <button class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-2 py-3" @click="router.push('/manage-badges')">
                  <Award style="width:24px;height:24px;color:#1a2230" />
                  <span class="x-small">Gérer Badges</span>
                </button>
              </div>
              <div class="col-6">
                <button class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-2 py-3" @click="router.push('/manage-certificates')">
                  <FileText style="width:24px;height:24px;color:#1a2230" />
                  <span class="x-small">Gérer Certificats</span>
                </button>
              </div>
            </template>
            <div v-if="user.role === 'admin' || user.role === 'superadmin'" class="col-6">
              <button class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center gap-2 py-3" @click="router.push('/manage-competences')">
                <Star style="width:24px;height:24px;color:#1a2230" />
                <span class="x-small">Gérer Compétences</span>
              </button>
            </div>
          </div>

          <div v-if="user.role === 'superadmin' && superAdminPanelTab === 'emergency'" class="d-flex flex-column gap-3">
            <div class="d-flex align-items-center justify-content-between">
              <div class="small text-muted">Messages d'urgence centralisés pour tous les superadmins</div>
              <button class="btn btn-sm btn-outline-secondary" @click="loadEmergencyMessages">Actualiser</button>
            </div>

            <div v-if="emergencyLoading" class="text-muted small">Chargement des urgences...</div>
            <div v-else-if="emergencyError" class="alert alert-danger small mb-0">{{ emergencyError }}</div>
            <div v-else-if="emergencyItems.length === 0" class="text-muted small">Aucun message d'urgence reçu.</div>

            <div v-else class="d-flex flex-column gap-3">
              <div v-for="item in emergencyItems" :key="item.id" class="border rounded p-3">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                  <div>
                    <div class="fw-semibold small text-danger">Urgence {{ item.category || 'general' }}</div>
                    <div class="small">
                      <strong>{{ item.sender.fullName }}</strong> • {{ item.missionName }} • {{ item.eventName }}
                    </div>
                    <div class="x-small text-muted">Envoyé le {{ formatDateTime(item.sentAt) }}</div>
                  </div>
                  <span class="badge" :class="item.owner ? 'text-bg-success' : 'text-bg-secondary'">
                    {{ item.owner ? `Pris en charge par ${item.owner.fullName}` : 'Non attribué' }}
                  </span>
                </div>

                <div class="alert alert-danger small mb-2">{{ item.message }}</div>

                <div class="small mb-2">
                  <strong>Consultations:</strong>
                  <span v-if="!item.views.length" class="text-muted"> Aucune consultation enregistrée.</span>
                </div>
                <div v-if="item.views.length" class="d-flex flex-column gap-1 mb-3">
                  <div v-for="view in item.views" :key="`${item.id}-${view.viewer.id}-${view.viewedAt}`" class="x-small text-muted">
                    {{ view.viewer.fullName }} a consulté le {{ formatDateTime(view.viewedAt) }}
                  </div>
                </div>

                <div class="d-flex gap-2">
                  <button
                    class="btn btn-outline-primary btn-sm"
                    :disabled="emergencyProcessingId === item.id"
                    @click="markEmergencyViewed(item)">
                    Marquer comme consulté
                  </button>
                  <button
                    class="btn btn-danger btn-sm"
                    :disabled="emergencyProcessingId === item.id || (item.owner && item.owner.id !== String(user.id))"
                    @click="takeEmergencyOwnership(item)">
                    Prendre en charge
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-4 pt-3 border-top text-center">
        <p class="x-small text-muted">
          <button class="btn btn-link btn-sm p-0 text-primary text-decoration-none"
            @click="alert('Page des conditions d\'utilisation à venir')">
            Conditions d'utilisation
          </button>
          &nbsp;•&nbsp;© 2025 Béné'Run
        </p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Calendar, Award, TrendingUp, MapPin, Briefcase, Heart, Settings, Users, BarChart3, Shield, Star, FileText } from 'lucide-vue-next'
import { myActiveMissions, availableMissions } from '@/data/mockData'
import { getCurrentUser } from '@/utils/auth'
import emergencyService from '@/services/emergencyService'

const router = useRouter()
const user = getCurrentUser()
if (!user) router.push('/login')

const nextMission = myActiveMissions[0]
const badges = computed(() => user?.badges || [])
const missionHistory = computed(() => user?.missionHistory || [])
const totalMissions = computed(() => (missionHistory.value?.length || 0) + (myActiveMissions?.length || 0))

const isManager = computed(() =>
  ['mission_manager', 'admin', 'superadmin'].includes(user.role)
)

const suggestedMissions = computed(() =>
  user.role === 'volunteer'
    ? availableMissions.filter(m => m.visibility === 'public' && m.inscription).slice(0, 3)
    : []
)

const managedMissions = computed(() =>
  isManager.value
    ? availableMissions.filter(m => m.missionManagers.some(mgr => mgr.id === user.id)).slice(0, 3)
    : []
)

const superAdminPanelTab = ref('management')
const emergencyItems = ref([])
const emergencyLoading = ref(false)
const emergencyError = ref('')
const emergencyProcessingId = ref('')

const openEmergencyCount = computed(() =>
  emergencyItems.value.filter((item) => !item.owner).length
)

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

const sortEmergencyItems = (items) =>
  [...items].sort((a, b) => new Date(b.sentAt || 0).getTime() - new Date(a.sentAt || 0).getTime())

const loadEmergencyMessages = async () => {
  if (user.role !== 'superadmin') return

  emergencyLoading.value = true
  emergencyError.value = ''

  try {
    const rows = await emergencyService.listForSuperadmin()
    emergencyItems.value = sortEmergencyItems(rows)
  } catch (error) {
    emergencyError.value = error.message || 'Impossible de charger les urgences.'
  } finally {
    emergencyLoading.value = false
  }
}

const replaceEmergency = (nextEmergency) => {
  const index = emergencyItems.value.findIndex((item) => item.id === nextEmergency.id)
  if (index === -1) {
    emergencyItems.value = sortEmergencyItems([nextEmergency, ...emergencyItems.value])
    return
  }

  const nextItems = [...emergencyItems.value]
  nextItems[index] = nextEmergency
  emergencyItems.value = sortEmergencyItems(nextItems)
}

const markEmergencyViewed = async (item) => {
  if (user.role !== 'superadmin') return

  emergencyProcessingId.value = item.id
  emergencyError.value = ''

  try {
    const response = await emergencyService.markViewed({
      emergencyId: item.id,
      userId: user.id,
    })

    if (response.urgence) {
      replaceEmergency(response.urgence)
    }
  } catch (error) {
    emergencyError.value = error.message || 'Impossible d\'enregistrer la consultation.'
  } finally {
    emergencyProcessingId.value = ''
  }
}

const takeEmergencyOwnership = async (item) => {
  if (user.role !== 'superadmin') return

  emergencyProcessingId.value = item.id
  emergencyError.value = ''

  try {
    const response = await emergencyService.takeOwnership({
      emergencyId: item.id,
      userId: user.id,
    })

    if (response.urgence) {
      replaceEmergency(response.urgence)
    }
  } catch (error) {
    emergencyError.value = error.message || 'Impossible de prendre en charge cette urgence.'
  } finally {
    emergencyProcessingId.value = ''
  }
}

const formatDate = (date) =>
  new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })

const formatDateShort = (date) =>
  new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })

onMounted(async () => {
  if (user.role === 'superadmin') {
    await loadEmergencyMessages()
  }
})
</script>