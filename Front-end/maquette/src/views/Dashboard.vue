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

      <div v-if="isLoading" class="alert alert-info small" role="status">
        Chargement des donnees du tableau de bord...
      </div>

      <div v-else-if="loadError" class="alert alert-danger small d-flex align-items-center justify-content-between gap-2" role="alert">
        <span>{{ loadError }}</span>
        <button class="btn btn-sm btn-outline-danger" @click="loadDashboardData">Reessayer</button>
      </div>

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
          <h5 class="mb-0">
            Gestion
            <span v-if="user.role === 'mission_manager'"> Responsable de mission</span>
            <span v-else-if="user.role === 'admin'"> Admin</span>
            <span v-else> Super-Admin</span>
          </h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
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
            </template>
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Calendar, Award, TrendingUp, MapPin, Briefcase, Heart, Settings, Users, BarChart3, Shield, Star } from 'lucide-vue-next'
import { getCurrentUser, saveBackendUserSession } from '@/utils/auth'
import api from '@/services/api'

const router = useRouter()
const defaultUser = {
  id: '',
  firstName: 'Benevole',
  lastName: '',
  email: '',
  role: 'volunteer',
  accountType: 'Benevole',
}

const user = ref(getCurrentUser() || defaultUser)
if (!user.value?.email) router.push('/login')

const isLoading = ref(false)
const loadError = ref('')
const myActiveMissions = ref([])
const availableMissions = ref([])
const badges = ref([])
const missionHistory = ref([])

const roleAccountType = {
  volunteer: 'Benevole',
  mission_manager: 'Responsable de mission',
  admin: 'Admin',
  superadmin: 'Super-admin',
}

const toTime = (value) => {
  if (!value || typeof value !== 'string') return ''
  return value.slice(0, 5)
}

const resolveRole = (userId, adminUserIds, managerUserIds, firstAdminId) => {
  if (adminUserIds.has(userId)) {
    return userId === firstAdminId ? 'superadmin' : 'admin'
  }
  if (managerUserIds.has(userId)) {
    return 'mission_manager'
  }
  return 'volunteer'
}

const loadDashboardData = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const [
      usersResponse,
      adminsResponse,
      benevolesResponse,
      coursesResponse,
      missionsResponse,
      affectationsResponse,
      badgesResponse,
    ] = await Promise.all([
      api.get('/users'),
      api.get('/admins'),
      api.get('/benevoles'),
      api.get('/courses'),
      api.get('/missions'),
      api.get('/affectations'),
      api.get('/badges'),
    ])

    const users = usersResponse.data ?? []
    const admins = adminsResponse.data ?? []
    const benevoles = benevolesResponse.data ?? []
    const courses = coursesResponse.data ?? []
    const missions = missionsResponse.data ?? []
    const affectations = affectationsResponse.data ?? []
    const badgesData = badgesResponse.data ?? []

    const userEmail = user.value.email || localStorage.getItem('userEmail')
    const rawCurrentUser = users.find(u => u.email === userEmail)
    if (!rawCurrentUser) {
      loadError.value = 'Utilisateur introuvable sur le backend.'
      return
    }

    const adminUserIds = new Set(admins.map(admin => Number(admin.id_utilisateur)))
    const managerBenevoleIds = new Set(missions.map(mission => Number(mission.id_benevole)))
    const managerUserIds = new Set(
      benevoles
        .filter(benevole => managerBenevoleIds.has(Number(benevole.id_benevole)))
        .map(benevole => Number(benevole.id_utilisateur))
    )
    const firstAdminId = admins.length > 0 ? Number(admins[0].id_utilisateur) : null

    const resolvedRole = resolveRole(Number(rawCurrentUser.id_utilisateur), adminUserIds, managerUserIds, firstAdminId)

    user.value = {
      ...user.value,
      id: String(rawCurrentUser.id_utilisateur),
      firstName: rawCurrentUser.prenom_utilisateur,
      lastName: rawCurrentUser.nom_utilisateur,
      email: rawCurrentUser.email,
      role: resolvedRole,
      accountType: roleAccountType[resolvedRole],
    }

    saveBackendUserSession(user.value)

    const currentBenevole = benevoles.find(
      benevole => Number(benevole.id_utilisateur) === Number(rawCurrentUser.id_utilisateur)
    )

    const courseNames = new Map(courses.map(course => [Number(course.id_course), course.nom_course]))

    const affectationsByMission = new Map()
    for (const affectation of affectations) {
      const missionId = String(affectation.id_mission)
      affectationsByMission.set(missionId, (affectationsByMission.get(missionId) || 0) + 1)
    }

    const mappedMissions = missions.map((mission) => {
      const missionId = String(mission.id_mission)
      const maxVolunteers = Number(mission.nombre_mission) || 0
      const currentVolunteers = affectationsByMission.get(missionId) || 0

      return {
        id: missionId,
        eventId: String(mission.id_course),
        eventName: courseNames.get(Number(mission.id_course)) || `Course #${mission.id_course}`,
        name: mission.titre_mission,
        date: mission.date_debut_mission,
        startTime: toTime(mission.heure_debut_mission),
        endTime: toTime(mission.heure_fin_mission),
        location: mission.lieu_mission,
        description: mission.description_mission,
        type: mission.type_mission || 'General',
        imageUrl: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop',
        currentVolunteers,
        maxVolunteers,
        progress: maxVolunteers > 0 ? Math.round((currentVolunteers / maxVolunteers) * 100) : 0,
        visibility: mission.publie_mission ? 'public' : 'private',
        inscription: true,
        missionManagers: [
          {
            id: String(mission.id_benevole),
          },
        ],
      }
    })

    availableMissions.value = mappedMissions

    if (currentBenevole) {
      const myMissionIds = new Set(
        affectations
          .filter(affectation => Number(affectation.id_benevole) === Number(currentBenevole.id_benevole))
          .map(affectation => String(affectation.id_mission))
      )
      myActiveMissions.value = mappedMissions.filter(mission => myMissionIds.has(mission.id))
      badges.value = badgesData
        .filter(badge => Number(badge.id_benevole) === Number(currentBenevole.id_benevole))
        .map(badge => ({
          id: String(badge.id_badge),
          name: badge.titre_badge,
          icon: '🏅',
        }))
    } else {
      myActiveMissions.value = []
      badges.value = []
    }

    missionHistory.value = []
  } catch (error) {
    console.error('Erreur lors du chargement du dashboard:', error)
    loadError.value = 'Impossible de charger les donnees du dashboard depuis le backend.'
  } finally {
    isLoading.value = false
  }
}

const nextMission = computed(() => myActiveMissions.value[0] || null)
const totalMissions = computed(() => missionHistory.value.length + myActiveMissions.value.length)

const isManager = computed(() =>
  ['mission_manager', 'admin', 'superadmin'].includes(user.value.role)
)

const suggestedMissions = computed(() =>
  user.value.role === 'volunteer'
    ? availableMissions.value.filter(m => m.visibility === 'public' && m.inscription).slice(0, 3)
    : []
)

const managedMissions = computed(() =>
  isManager.value
    ? availableMissions.value
        .filter(m => m.missionManagers.some(mgr => mgr.id === user.value.id || mgr.id === String(user.value.id)))
        .slice(0, 3)
    : []
)

const formatDate = (date) =>
  new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })

const formatDateShort = (date) =>
  new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })

onMounted(loadDashboardData)
</script>