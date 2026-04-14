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
            {{ user.firstName[0] }}{{ user.lastName[0] }}
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
              <div class="fs-5 fw-bold" style="color:#d4e645">{{ userCompetences.length }}</div>
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
            <button class="btn btn-link p-0" @click="handleEditProfile"><Edit style="width:16px;height:16px" /></button>
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

            <div v-if="skillError" class="alert alert-danger py-2 mb-0 small">{{ skillError }}</div>

            <div v-for="c in userCompetences" :key="c.id_competence" class="d-flex align-items-center gap-2">
              <span class="small flex-fill">{{ c.nom_competence }}</span>
              <button class="btn btn-link p-1" :disabled="skillLoading" @click="removeSkill(c.id_competence)">
                <Trash2 style="width:16px;height:16px;color:#ef4444" />
              </button>
            </div>

            <div v-if="userCompetences.length === 0 && !isAddingSkill" class="small text-muted">
              Aucune compétence pour le moment.
            </div>

            <!-- Formulaire ajout -->
            <div v-if="isAddingSkill" class="bg-light rounded p-3 d-flex flex-column gap-2">
              <div>
                <label class="x-small text-muted">Choisir une compétence</label>
                <select v-model="selectedSkillId" class="form-select form-select-sm mt-1">
                  <option value="">-- Sélectionner --</option>
                  <option v-for="c in availableToAdd" :key="c.id_competence" :value="c.id_competence">
                    {{ c.nom_competence }}
                  </option>
                </select>
                <div v-if="availableToAdd.length === 0" class="x-small text-muted mt-1">
                  Toutes les compétences disponibles ont déjà été ajoutées.
                </div>
              </div>
              <div class="d-flex gap-2 pt-1">
                <button class="btn btn-primary btn-sm flex-fill"
                  :disabled="!selectedSkillId || skillLoading" @click="addSkill">
                  <span v-if="skillLoading" class="spinner-border spinner-border-sm me-1" />
                  Ajouter
                </button>
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
              @click="handleSuspendOwnAccount">
              <UserX style="width:16px;height:16px" /> Suspendre mon compte
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  User, Award, FileText, Briefcase, MapPin, MessageSquare,
  Edit, Download, Star, Calendar, Plus, Trash2, Lock,
  UserX, LogOut
} from 'lucide-vue-next'
import { getCurrentUser, logout as authLogout } from '@/utils/auth'
import competenceService from '@/services/competenceService'
import userService from '@/services/userService'

const router = useRouter()
const user = getCurrentUser()
if (!user) router.push('/login')

const activeTab     = ref('info')
const permissions   = ref({ ...user.permissions })
const badges        = computed(() => user?.badges || [])
const certificates  = computed(() => user?.certificates || [])
const missionHistory = computed(() => user?.missionHistory || [])

// --- Compétences ---
const allCompetences    = ref([])   // toutes les compétences dispo en bdd
const userCompetences   = ref([])   // compétences de l'utilisateur
const isAddingSkill     = ref(false)
const selectedSkillId   = ref('')
const skillError        = ref('')
const skillLoading      = ref(false)

const availableToAdd = computed(() =>
  allCompetences.value.filter(
    c => !userCompetences.value.some(u => u.id_competence === c.id_competence)
  )
)

const loadSkills = async () => {
  if (!user?.id) return
  try {
    const [all, mine] = await Promise.all([
      competenceService.getAll(),
      competenceService.getUserCompetences(user.id),
    ])
    allCompetences.value  = all
    userCompetences.value = mine
  } catch {
    skillError.value = 'Impossible de charger les compétences.'
  }
}

const addSkill = async () => {
  if (!selectedSkillId.value) return
  skillError.value  = ''
  skillLoading.value = true
  try {
    await competenceService.addToUser(user.id, selectedSkillId.value)
    await loadSkills()
    selectedSkillId.value = ''
    isAddingSkill.value   = false
  } catch (error) {
    skillError.value = error?.response?.data?.message || 'Erreur lors de l\'ajout de la compétence.'
  } finally {
    skillLoading.value = false
  }
}

const removeSkill = async (competenceId) => {
  skillError.value  = ''
  skillLoading.value = true
  try {
    await competenceService.removeFromUser(user.id, competenceId)
    await loadSkills()
  } catch (error) {
    skillError.value = error?.response?.data?.message || 'Erreur lors de la suppression de la compétence.'
  } finally {
    skillLoading.value = false
  }
}

const cancelAddSkill = () => {
  isAddingSkill.value   = false
  selectedSkillId.value = ''
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

const handleEditProfile = () => {
  router.push('/profile/edit')
}

const handleSuspendOwnAccount = async () => {
  if (!confirm('Voulez-vous vraiment suspendre votre compte ?')) return

  const reason = prompt('Raison de suspension (optionnel)', 'Suspension demandée par l\'utilisateur') || 'Suspension demandée par l\'utilisateur'
  const userId = user.id
  if (!userId) {
    alert('Impossible de suspendre ce compte (ID utilisateur manquant).')
    return
  }

  try {
    await userService.update(userId, {
      firstName: user.firstName,
      lastName: user.lastName,
      email: user.email,
      phone: user.phone || '',
      address: user.address || '',
      dateOfBirth: user.dateOfBirth || '',
      role: user.role,
      allergies: user.allergies || [],
      healthIssues: user.healthIssues || [],
      hasLicense: !!user.hasLicense,
      isMotorized: !!user.isMotorized,
      hasVehicle: !!user.hasVehicle,
      bibSize: user.bibSize || '',
      isAnonymous: !!user.anonymous,
      isSuspended: true,
      suspensionReason: reason,
      missionCount: user.missionCount || 0,
    })

    authLogout()
    alert('Votre compte est maintenant suspendu.')
    router.push('/login')
  } catch (error) {
    alert(error.message || 'Erreur lors de la suspension du compte')
  }
}

onMounted(loadSkills)
</script>