<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="text-white p-4 pb-5 position-relative overflow-hidden"
      style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">
      <div class="position-relative" style="z-index:1">

        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="d-flex align-items-center gap-3">
            <div class="bg-white rounded-3 p-2 shadow">
              <img src="@/assets/logo.png" alt="Running Geneva" style="height:32px;width:auto" />
            </div>
            <div>
              <div class="x-small text-white-50">Running Geneva</div>
              <div class="small fw-bold">Mon Profil</div>
            </div>
          </div>
          <button class="btn btn-sm text-white d-flex align-items-center gap-2"
            style="background:rgba(255,255,255,.1)"
            @click="handleLogout">
            <LogOut style="width:16px;height:16px" /> Déconnexion
          </button>
        </div>

        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3 border border-4 shadow"
            style="width:80px;height:80px;background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230;border-color:rgba(255,255,255,.2)!important;flex-shrink:0">
            {{ user.firstName?.[0] || '?' }}{{ user.lastName?.[0] || '' }}
          </div>
          <div>
            <h5 class="fw-bold text-white mb-1">{{ user.firstName }} {{ user.lastName }}</h5>
            <span class="badge fw-bold text-dark px-3 py-1"
              style="background:linear-gradient(to right,#d4e645,#a3c200)">
              {{ user.accountType }}
            </span>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-4">
            <div class="rounded-3 text-center p-3 border border-white border-opacity-25"
              style="background:rgba(255,255,255,.1)">
              <div class="fs-5 fw-bold" style="color:#d4e645">{{ badges.length }}</div>
              <div class="x-small text-white-50">Badges</div>
            </div>
          </div>
          <div class="col-4">
            <div class="rounded-3 text-center p-3 border border-white border-opacity-25"
              style="background:rgba(255,255,255,.1)">
              <div class="fs-5 fw-bold" style="color:#d4e645">{{ missionHistory.length }}</div>
              <div class="x-small text-white-50">Missions</div>
            </div>
          </div>
          <div class="col-4">
            <div class="rounded-3 text-center p-3 border border-white border-opacity-25"
              style="background:rgba(255,255,255,.1)">
              <div class="fs-5 fw-bold" style="color:#d4e645">{{ skills.length }}</div>
              <div class="x-small text-white-50">Compétences</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="px-3 pt-3 mx-auto" style="max-width:576px">

      <!-- Tabs nav -->
      <ul class="nav nav-tabs nav-fill mb-3">
        <li class="nav-item">
          <button :class="['nav-link', activeTab === 'info' ? 'active' : '']" @click="activeTab = 'info'">
            <User style="width:18px;height:18px" />
          </button>
        </li>
        <li class="nav-item">
          <button :class="['nav-link', activeTab === 'badges' ? 'active' : '']" @click="activeTab = 'badges'">
            <Award style="width:18px;height:18px" />
          </button>
        </li>
        <li class="nav-item">
          <button :class="['nav-link', activeTab === 'certs' ? 'active' : '']" @click="activeTab = 'certs'">
            <FileText style="width:18px;height:18px" />
          </button>
        </li>
        <li class="nav-item">
          <button :class="['nav-link', activeTab === 'history' ? 'active' : '']" @click="activeTab = 'history'">
            <Briefcase style="width:18px;height:18px" />
          </button>
        </li>
      </ul>

      <!-- TAB: Infos -->
      <div v-if="activeTab === 'info'" class="d-flex flex-column gap-3">

        <!-- Infos personnelles -->
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Informations personnelles</h5>
            <button class="btn btn-link p-0"><Edit style="width:16px;height:16px" /></button>
          </div>
          <div class="card-body d-flex flex-column gap-2">
            <div><div class="x-small text-muted">Email</div><div class="small">{{ user.email }}</div></div>
            <div><div class="x-small text-muted">Téléphone</div><div class="small">{{ user.phone }}</div></div>
            <div>
              <div class="x-small text-muted">Date d'inscription</div>
              <div class="small">{{ user.registrationDate ? new Date(user.registrationDate).toLocaleDateString('fr-FR') : 'Non disponible' }}</div>
            </div>
          </div>
        </div>

        <!-- Santé -->
        <div class="card">
          <div class="card-header"><h5 class="mb-0">Santé et Allergies</h5></div>
          <div class="card-body d-flex flex-column gap-3">
            <div>
              <div class="x-small text-muted mb-2">Allergies</div>
              <div class="d-flex flex-wrap gap-2">
                <span v-for="a in user.allergies" :key="a"
                  class="badge bg-secondary-subtle text-secondary border">{{ a }}</span>
              </div>
            </div>
            <div>
              <div class="x-small text-muted mb-2">Problèmes de santé</div>
              <div class="d-flex flex-wrap gap-2">
                <span v-for="h in user.healthIssues" :key="h"
                  class="badge bg-secondary-subtle text-secondary border">{{ h }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Avertissements (admin+) -->
        <div v-if="hasMinRole('admin')" class="card">
          <div class="card-header d-flex align-items-center gap-2">
            <AlertTriangle style="width:20px;height:20px;color:#ca8a04" />
            <h5 class="mb-0">Avertissements</h5>
          </div>
          <div class="card-body d-flex flex-column gap-3">
            <div v-if="user.warnings >= 3" class="alert alert-danger mb-0">
              <div class="fw-bold mb-1">⚠️ Compte à risque</div>
              <p class="small mb-0">
                Cet utilisateur a atteint <strong>{{ user.warnings }}/3</strong> avertissements.
                {{ user.suspended ? 'Le compte est actuellement suspendu.' : 'Le compte devrait être suspendu.' }}
              </p>
            </div>
            <div v-else-if="user.warnings > 0" class="alert alert-warning mb-0">
              <div class="fw-semibold mb-1">Avertissements : {{ user.warnings }}/3</div>
              <p class="small mb-0">{{ 3 - user.warnings }} avertissement(s) restant(s) avant suspension.</p>
            </div>
            <div v-else class="alert alert-success mb-0 d-flex align-items-center gap-2">
              <span class="fs-4">✓</span>
              <div>
                <div class="fw-semibold">Aucun avertissement</div>
                <p class="small mb-0">Comportement exemplaire</p>
              </div>
            </div>
            <div class="bg-light rounded p-3 x-small text-muted">
              <p class="fw-medium mb-2">ℹ️ Informations sur les avertissements</p>
              <ul class="mb-0 ps-3">
                <li>1 avertissement = désinscription d'une mission</li>
                <li>3 avertissements = suspension automatique du compte</li>
                <li>Badge "Non fiable" visible à 3 avertissements</li>
              </ul>
            </div>
            <div v-if="user.warnings >= 3">
              <span class="badge bg-danger-subtle text-danger border border-danger">
                🚫 Badge: Utilisateur Non Fiable
              </span>
            </div>
          </div>
        </div>

        <!-- Autorisations -->
        <div class="card">
          <div class="card-header"><h5 class="mb-0">Autorisations</h5></div>
          <div class="card-body d-flex flex-column gap-3">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <MapPin style="width:20px;height:20px;color:#6b7280" />
                <div>
                  <div class="small fw-medium">Localisation</div>
                  <div class="x-small text-muted">Pour la carte en temps réel</div>
                </div>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" v-model="permissions.location" />
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <MessageSquare style="width:20px;height:20px;color:#6b7280" />
                <div>
                  <div class="small fw-medium">Messagerie</div>
                  <div class="x-small text-muted">Notifications des messages</div>
                </div>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" v-model="permissions.messaging" />
              </div>
            </div>
          </div>
        </div>

        <!-- Compétences -->
        <div class="card">
          <div class="card-header"><h5 class="mb-0">Compétences</h5></div>
          <div class="card-body d-flex flex-column gap-3">
            <div v-for="skill in skills" :key="skill.id" class="d-flex align-items-center gap-2">
              <span class="small flex-fill">{{ skill.name }}</span>
              <span :class="['badge',
                skill.level === 'Expert' ? 'bg-primary' :
                skill.level === 'Avancé' ? 'bg-secondary' :
                'bg-light text-dark border']">
                {{ skill.level }}
              </span>
              <button class="btn btn-link p-1" @click="removeSkill(skill.id)">
                <Trash2 style="width:16px;height:16px;color:#ef4444" />
              </button>
            </div>

            <!-- Formulaire ajout -->
            <div v-if="isAddingSkill" class="bg-light rounded p-3 d-flex flex-column gap-2">
              <div>
                <label class="x-small text-muted">Nom de la compétence</label>
                <input v-model="newSkillName" type="text" class="form-control form-control-sm mt-1"
                  placeholder="Ex: Premiers secours, Logistique..." />
              </div>
              <div>
                <label class="x-small text-muted">Niveau</label>
                <select v-model="newSkillLevel" class="form-select form-select-sm mt-1">
                  <option v-for="l in skillLevels" :key="l" :value="l">{{ l }}</option>
                </select>
              </div>
              <div class="d-flex gap-2 pt-1">
                <button class="btn btn-primary btn-sm flex-fill"
                  :disabled="!newSkillName.trim()" @click="addSkill">Ajouter</button>
                <button class="btn btn-outline-secondary btn-sm" @click="cancelAddSkill">Annuler</button>
              </div>
            </div>
            <button v-else
              class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center gap-2"
              @click="isAddingSkill = true">
              <Plus style="width:16px;height:16px" /> Ajouter une compétence
            </button>
          </div>
        </div>

        <!-- Sécurité -->
        <div class="card">
          <div class="card-header"><h5 class="mb-0">Sécurité du compte</h5></div>
          <div class="card-body d-flex flex-column gap-2">
            <button class="btn btn-outline-secondary d-flex align-items-center gap-2"
              @click="handleChangePassword">
              <Lock style="width:16px;height:16px" /> Changer le mot de passe
            </button>
            <button class="btn btn-danger d-flex align-items-center gap-2"
              @click="handleDeleteAccount">
              <UserX style="width:16px;height:16px" /> Supprimer le compte
            </button>
          </div>
        </div>
      </div>

      <!-- TAB: Badges -->
      <div v-if="activeTab === 'badges'" class="card">
        <div class="card-header"><h5 class="mb-0">Mes Badges ({{ badges.length }})</h5></div>
        <div class="card-body">
          <div class="row g-3">
            <div v-for="badge in badges" :key="badge.id" class="col-6">
              <div class="rounded-3 text-center p-3 border"
                style="background:linear-gradient(135deg,#fefce8,#fef9c3);border-color:#fde68a!important">
                <div class="fs-1 mb-2">{{ badge.icon }}</div>
                <div class="fw-semibold small mb-1">{{ badge.name }}</div>
                <div class="x-small text-muted mb-1">{{ badge.description }}</div>
                <div class="x-small text-muted">
                  {{ new Date(badge.earnedDate).toLocaleDateString('fr-FR') }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB: Certificats -->
      <div v-if="activeTab === 'certs'" class="card">
        <div class="card-header"><h5 class="mb-0">Mes Certificats ({{ certificates.length }})</h5></div>
        <div class="card-body d-flex flex-column gap-3">
          <div v-for="cert in certificates" :key="cert.id" class="border rounded p-3">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="flex-fill">
                <div class="fw-semibold">{{ cert.name }}</div>
                <div class="small text-muted">{{ cert.issuer }}</div>
              </div>
              <span :class="['badge', cert.type === 'platform' ? 'bg-primary' : 'bg-secondary-subtle text-secondary border']">
                {{ cert.type === 'platform' ? 'Plateforme' : 'Externe' }}
              </span>
            </div>
            <div class="x-small text-muted mb-3">
              Délivré le {{ new Date(cert.issueDate).toLocaleDateString('fr-FR') }}
              <span v-if="cert.expiryDate">
                • Expire le {{ new Date(cert.expiryDate).toLocaleDateString('fr-FR') }}
              </span>
            </div>
            <button
              class="btn btn-outline-secondary btn-sm w-100 d-flex align-items-center justify-content-center gap-2"
              @click="exportCert(cert.name)">
              <Download style="width:16px;height:16px" /> Exporter en PDF
            </button>
          </div>
        </div>
      </div>

      <!-- TAB: Historique -->
      <div v-if="activeTab === 'history'" class="card">
        <div class="card-header"><h5 class="mb-0">Historique des Missions ({{ missionHistory.length }})</h5></div>
        <div class="card-body d-flex flex-column gap-3">
          <div v-for="m in missionHistory" :key="m.id" class="border rounded p-3">
            <div class="fw-semibold mb-1">{{ m.missionName }}</div>
            <div class="small text-muted mb-2">{{ m.eventName }}</div>
            <div class="d-flex align-items-center gap-2 x-small text-muted mb-2">
              <Calendar style="width:12px;height:12px" />
              <span>{{ new Date(m.date).toLocaleDateString('fr-FR') }}</span>
              <span>•</span>
              <span>{{ m.duration }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="badge bg-secondary-subtle text-secondary border">{{ m.role }}</span>
              <div class="d-flex gap-1">
                <Star v-for="i in 5" :key="i"
                  style="width:16px;height:16px"
                  :style="i <= m.rating ? 'fill:#facc15;color:#facc15' : 'color:#d1d5db'" />
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  User, Award, FileText, Briefcase, MapPin, MessageSquare,
  Edit, Download, Star, Calendar, Plus, Trash2, Lock,
  UserX, LogOut, AlertTriangle
} from 'lucide-vue-next'
import { getCurrentUser, logout as authLogout, hasMinRole } from '@/utils/auth'
import api from '@/services/api'

const router = useRouter()
const currentSessionUser = getCurrentUser()
if (!currentSessionUser) router.push('/login')

const user = ref({
  id: currentSessionUser?.id || '',
  firstName: currentSessionUser?.firstName || 'Utilisateur',
  lastName: currentSessionUser?.lastName || '',
  email: currentSessionUser?.email || '',
  phone: '',
  registrationDate: null,
  accountType: currentSessionUser?.accountType || 'Benevole',
  permissions: currentSessionUser?.permissions || { location: true, messaging: true },
  allergies: [],
  healthIssues: [],
  warnings: 0,
  suspended: false,
})

const activeTab     = ref('info')
const permissions   = ref({ ...(user.value.permissions || { location: true, messaging: true }) })
const skills        = ref([])
const badges        = ref([])
const certificates  = ref([])
const missionHistory = ref([])
const isAddingSkill = ref(false)
const newSkillName  = ref('')
const newSkillLevel = ref('Débutant')
const skillLevels   = ['Débutant', 'Intermédiaire', 'Avancé', 'Expert']

const toTime = (value) => {
  if (!value || typeof value !== 'string') return ''
  return value.slice(0, 5)
}

const computeDuration = (start, end) => {
  if (!start || !end) return ''
  const startDate = new Date(`1970-01-01T${start}`)
  const endDate = new Date(`1970-01-01T${end}`)
  const diffMs = endDate.getTime() - startDate.getTime()
  if (Number.isNaN(diffMs) || diffMs <= 0) return `${toTime(start)} - ${toTime(end)}`
  const hours = Math.round((diffMs / (1000 * 60 * 60)) * 10) / 10
  return `${hours} h`
}

const accountTypeFromRole = (role) => {
  if (role === 'superadmin') return 'Super-admin'
  if (role === 'admin') return 'Admin'
  if (role === 'responsable') return 'Responsable de mission'
  return 'Benevole'
}

const loadProfileData = async () => {
  if (!user.value.email) return

  try {
    const [
      usersResponse,
      badgesResponse,
      certificatsResponse,
      affectationsResponse,
      missionsResponse,
      evenementsResponse,
    ] = await Promise.all([
      api.get('/users'),
      api.get('/badges'),
      api.get('/certificats'),
      api.get('/affectations'),
      api.get('/missions'),
      api.get('/evenements'),
    ])

    const users = usersResponse.data ?? []
    const badgesData = badgesResponse.data ?? []
    const certificatsData = certificatsResponse.data ?? []
    const affectations = affectationsResponse.data ?? []
    const missions = missionsResponse.data ?? []
    const evenements = evenementsResponse.data ?? []

    const rawUser = users.find(u => (u.email || '').toLowerCase() === user.value.email.toLowerCase())
    if (!rawUser) return

    user.value = {
      ...user.value,
      id: String(rawUser.id_utilisateur),
      firstName: rawUser.prenom_utilisateur || user.value.firstName,
      lastName: rawUser.nom_utilisateur || user.value.lastName,
      email: rawUser.email || user.value.email,
      phone: rawUser.telephone_utilisateur || '',
      registrationDate: rawUser.created_at || null,
      allergies: [],
      healthIssues: [],
      warnings: 0,
      suspended: false,
      accountType: accountTypeFromRole(rawUser.role_utilisateur),
      permissions: {
        ...(user.value.permissions || {}),
      },
    }

    permissions.value = {
      location: user.value.permissions?.location !== false,
      messaging: user.value.permissions?.messaging !== false,
    }

    badges.value = badgesData
      .map((b, index) => ({
        id: String(b.id_badge),
        name: b.titre_badge,
        description: b.regle_auto || b.description_badge || '',
        icon: '🏅',
        earnedDate: b.created_at || new Date().toISOString(),
        order: index,
      }))

    certificates.value = certificatsData
      .filter(c => Number(c.id_utilisateur) === Number(rawUser.id_utilisateur))
      .map(c => ({
        id: String(c.id_certificat),
        name: c.titre_certificat,
        issuer: c.emetteur_certificat || 'BeneRun',
        issueDate: c.date_emission_certificat || c.created_at || new Date().toISOString(),
        expiryDate: c.date_expiration_certificat,
        type: c.type_certificat || 'platform',
      }))

    const missionMap = new Map(missions.map(m => [Number(m.id_mission), m]))
    const eventMap = new Map(evenements.map(e => [Number(e.id_evenement), e]))

    missionHistory.value = affectations
      .filter(a => Number(a.id_utilisateur) === Number(rawUser.id_utilisateur))
      .map((a, index) => {
        const mission = missionMap.get(Number(a.id_mission))
        const event = mission ? eventMap.get(Number(mission.id_evenement)) : null

        return {
          id: String(a.id_affectation || `${a.id_mission}-${index}`),
          missionName: mission?.titre_mission || 'Mission',
          eventName: event?.nom_evenement || 'Evenement',
          date: mission?.date_mission || a.created_at || new Date().toISOString(),
          duration: computeDuration(mission?.heure_debut_mission, mission?.heure_fin_mission),
          role: a.est_responsable ? 'Responsable de mission' : 'Benevole',
          rating: 0,
        }
      })
      .sort((a, b) => new Date(b.date) - new Date(a.date))

    // Endpoint utilisateur_competences n'est pas expose; laisse vide cote backend.
    skills.value = []
  } catch (error) {
    console.error('Erreur chargement profil:', error)
  }
}

const addSkill = () => {
  if (newSkillName.value.trim()) {
    skills.value.push({
      id: Date.now().toString(),
      name: newSkillName.value.trim(),
      level: newSkillLevel.value
    })
    cancelAddSkill()
  }
}

const cancelAddSkill = () => {
  isAddingSkill.value = false
  newSkillName.value  = ''
  newSkillLevel.value = 'Débutant'
}

const removeSkill = (id) => {
  skills.value = skills.value.filter(s => s.id !== id)
}

const exportCert = (name) => {
  alert(`Export du certificat "${name}" en PDF`)
}

const handleLogout = () => {
  if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
    authLogout()
    router.push('/login')
  }
}

const handleChangePassword = () => {
  if (confirm('Voulez-vous changer votre mot de passe ?')) {
    router.push('/reset-password')
  }
}

const handleDeleteAccount = () => {
  if (confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action désactivera votre compte.')) {
    authLogout()
    alert('Votre compte a été désactivé')
    router.push('/login')
  }
}

onMounted(loadProfileData)
</script>