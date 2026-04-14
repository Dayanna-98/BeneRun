<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Not found -->
    <div v-if="!targetUser" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-muted mb-3">Utilisateur non trouvé</p>
        <button class="btn btn-primary" @click="router.push('/manage-users')">Retour à la gestion</button>
      </div>
    </div>

    <template v-else>
      <!-- Header -->
      <header class="bg-white border-bottom sticky-top">
        <div class="p-3 d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Modifier les Droits</h1>
        </div>
      </header>

      <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:768px">

        <!-- Alertes statut -->
        <div v-if="targetUser.suspended" class="alert alert-danger d-flex align-items-center gap-2 mb-0">
          <UserX style="width:18px;height:18px;flex-shrink:0" />
          <span><strong>Compte suspendu.</strong> Cet utilisateur ne peut plus accéder à la plateforme.</span>
        </div>

        <div v-if="targetUser.anonymous" class="alert alert-secondary d-flex align-items-center gap-2 mb-0">
          <EyeOff style="width:18px;height:18px;flex-shrink:0" />
          <span><strong>Compte anonyme.</strong> Les informations personnelles sont masquées.</span>
        </div>

        <!-- Infos utilisateur -->
        <div class="card">
          <div class="card-header"><h5 class="mb-0">Informations de l'utilisateur</h5></div>
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 text-white flex-shrink-0"
                style="width:64px;height:64px;background:linear-gradient(135deg,#d4e645,#a3c200)">
                {{ targetUser.firstName[0] }}{{ targetUser.lastName[0] }}
              </div>
              <div>
                <h5 class="fw-semibold mb-1">
                  {{ targetUser.anonymous ? 'Utilisateur Anonyme' : `${targetUser.firstName} ${targetUser.lastName}` }}
                </h5>
                <p class="small text-muted mb-0">{{ targetUser.anonymous ? '***@***.***' : targetUser.email }}</p>
              </div>
            </div>

            <div class="row g-3 small">
              <div class="col-6">
                <div class="text-muted">Téléphone</div>
                <div class="fw-medium">{{ targetUser.anonymous ? '***' : targetUser.phone }}</div>
              </div>
              <div class="col-6">
                <div class="text-muted">Rôle actuel</div>
                <span :class="`badge border ${roleBadgeClass(targetUser.role)}`">{{ targetUser.accountType }}</span>
              </div>
              <div class="col-6">
                <div class="text-muted">Statut</div>
                <div class="fw-medium">
                  <span v-if="targetUser.suspended" class="text-danger">Suspendu</span>
                  <span v-else-if="targetUser.anonymous" class="text-secondary">Anonyme</span>
                  <span v-else class="text-success">Actif</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modifier infos personnelles -->
        <div v-if="!targetUser.suspended" class="card">
          <div class="card-header"><h5 class="mb-0">Modifier les informations</h5></div>
          <div class="card-body d-flex flex-column gap-3">
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label small fw-medium">Prénom</label>
                <input v-model="editForm.firstName" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-medium">Nom</label>
                <input v-model="editForm.lastName" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-medium">Email</label>
                <input v-model="editForm.email" type="email" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-medium">Téléphone</label>
                <input v-model="editForm.phone" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label small fw-medium">Adresse</label>
                <input v-model="editForm.address" class="form-control" />
              </div>
            </div>

            <button class="btn btn-primary d-flex align-items-center justify-content-center gap-2" @click="handleSaveInfo">
              <Save style="width:16px;height:16px" />
              Enregistrer les informations
            </button>
          </div>
        </div>

        <!-- Modifier le rôle -->
        <div v-if="!targetUser.suspended" class="card">
          <div class="card-header"><h5 class="mb-0">Modifier le rôle et les permissions</h5></div>
          <div class="card-body">
            <form @submit.prevent="handleSubmit" class="d-flex flex-column gap-3">
              <div>
                <label class="form-label small fw-medium">Sélectionner un nouveau rôle</label>
                <div class="d-flex flex-column gap-2">
                  <button
                    v-for="role in roleOptions" :key="role.value"
                    type="button"
                    :class="['btn text-start p-3 border-2 rounded', selectedRole === role.value ? `${roleBadgeClass(role.value)} border-current` : 'btn-outline-secondary']"
                    @click="selectedRole = role.value"
                  >
                    <div class="fw-medium">{{ role.label }}</div>
                    <div class="small opacity-75">{{ role.desc }}</div>
                  </button>
                </div>
              </div>

              <div class="alert alert-info small mb-0">
                <div class="fw-medium mb-1">Hiérarchie des rôles</div>
                <ul class="mb-0 ps-3">
                  <li>Bénévole : Utilisateur de base</li>
                  <li>Organisateur : Bénévole + gestion opérationnelle des missions</li>
                  <li>Admin : Organisateur + gestion stratégique des événements</li>
                  <li>Super-admin : Détient l'intégralité des droits de la plateforme</li>
                </ul>
              </div>

              <div class="d-flex gap-3 pt-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" @click="router.go(-1)">Annuler</button>
                <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2">
                  <Save style="width:16px;height:16px" />
                  Enregistrer
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Compétences utilisateur -->
        <div v-if="!targetUser.suspended" class="card">
          <div class="card-header"><h5 class="mb-0">Compétences de l'utilisateur</h5></div>
          <div class="card-body d-flex flex-column gap-3">
            <div v-if="skillError" class="alert alert-danger py-2 mb-0 small">{{ skillError }}</div>
            <div v-if="skillLoading" class="small text-muted">Chargement des compétences...</div>

            <div v-for="c in userCompetences" :key="c.id_competence" class="d-flex align-items-center gap-2">
              <span class="small flex-fill">{{ c.nom_competence }}</span>
              <button class="btn btn-link p-1" :disabled="skillLoading" @click="removeSkill(c.id_competence)">
                <Trash2 style="width:16px;height:16px;color:#ef4444" />
              </button>
            </div>

            <div v-if="userCompetences.length === 0 && !isAddingSkill && !skillLoading" class="small text-muted">
              Aucune compétence pour le moment.
            </div>

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
                <button class="btn btn-primary btn-sm flex-fill" :disabled="!selectedSkillId || skillLoading" @click="addSkill">
                  Ajouter
                </button>
                <button class="btn btn-outline-secondary btn-sm" @click="cancelAddSkill">Annuler</button>
              </div>
            </div>

            <button
              v-else
              class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center gap-2"
              @click="isAddingSkill = true"
              :disabled="skillLoading"
            >
              <Plus style="width:16px;height:16px" /> Ajouter une compétence
            </button>
          </div>
        </div>

        <!-- Gestion du compte -->
        <div class="card">
          <div class="card-header"><h5 class="mb-0">Gestion du compte</h5></div>
          <div class="card-body d-flex flex-column gap-2">
            <template v-if="targetUser.suspended">
              <button class="btn btn-success d-flex align-items-center justify-content-center gap-2"
                @click="showReactivateDialog = true">
                <Eye style="width:16px;height:16px" />
                Réactiver le compte
              </button>
            </template>
            <template v-else>
              <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2"
                @click="showAnonymizeDialog = true">
                <EyeOff style="width:16px;height:16px" />
                {{ targetUser.anonymous ? "Retirer l'anonymat" : 'Rendre anonyme' }}
              </button>
              <button class="btn btn-danger d-flex align-items-center justify-content-center gap-2"
                @click="showSuspendDialog = true">
                <UserX style="width:16px;height:16px" />
                Suspendre le compte
              </button>
            </template>
          </div>
        </div>

      </div>
    </template>

    <!-- Modal Suspension -->
    <Teleport to="body">
      <div v-if="showSuspendDialog" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Suspendre le compte ?</h5>
            </div>
            <div class="modal-body">
              Êtes-vous sûr de vouloir suspendre le compte de
              <strong>{{ targetUser?.firstName }} {{ targetUser?.lastName }}</strong> ?
              L'utilisateur ne pourra plus accéder à la plateforme tant que le compte n'est pas réactivé.
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="showSuspendDialog = false">Annuler</button>
              <button class="btn btn-danger" @click="handleSuspend">Suspendre</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Anonymisation -->
    <Teleport to="body">
      <div v-if="showAnonymizeDialog" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ targetUser?.anonymous ? "Retirer l'anonymat ?" : 'Rendre le compte anonyme ?' }}</h5>
            </div>
            <div class="modal-body">
              <template v-if="targetUser?.anonymous">
                Les informations personnelles de <strong>{{ targetUser?.firstName }} {{ targetUser?.lastName }}</strong> redeviendront visibles.
              </template>
              <template v-else>
                Les informations personnelles de <strong>{{ targetUser?.firstName }} {{ targetUser?.lastName }}</strong> seront masquées et remplacées par des données anonymes.
              </template>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="showAnonymizeDialog = false">Annuler</button>
              <button class="btn btn-primary" @click="handleAnonymize">Confirmer</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Réactivation -->
    <Teleport to="body">
      <div v-if="showReactivateDialog" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Réactiver le compte ?</h5>
            </div>
            <div class="modal-body">
              Êtes-vous sûr de vouloir réactiver le compte de
              <strong>{{ targetUser?.firstName }} {{ targetUser?.lastName }}</strong> ?
              L'utilisateur pourra à nouveau accéder à la plateforme.
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="showReactivateDialog = false">Annuler</button>
              <button class="btn btn-success" @click="handleReactivate">Réactiver</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, Save, UserX, Eye, EyeOff, Plus, Trash2 } from 'lucide-vue-next'
import { getCurrentUser, isRole } from '@/utils/auth'
import { users } from '@/data/mockData'
import userService from '@/services/userService'
import competenceService from '@/services/competenceService'

const demoUsers = users.filter(user => user.email === 'admin@benerun.ch')

const router = useRouter()
const route = useRoute()
const currentUser = getCurrentUser()
if (!currentUser || !isRole('superadmin')) router.push('/')

const targetUser = ref(null)

const editForm = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  address: '',
})

const syncEditForm = () => {
  if (!targetUser.value) return
  editForm.value = {
    firstName: targetUser.value.firstName || '',
    lastName: targetUser.value.lastName || '',
    email: targetUser.value.email || '',
    phone: targetUser.value.phone || '',
    address: targetUser.value.address || '',
  }
}

onMounted(async () => {
  try {
    targetUser.value = await userService.getById(route.params.id)
  } catch {
    targetUser.value = demoUsers.find(u => String(u.id) === String(route.params.id)) || null
  }

  if (targetUser.value?.id) {
    await loadSkills(targetUser.value.id)
  }

  syncEditForm()
  if (targetUser.value) {
    selectedRole.value = targetUser.value.role || 'volunteer'
  }
})

const selectedRole = ref('volunteer')
const showSuspendDialog = ref(false)
const showAnonymizeDialog = ref(false)
const showReactivateDialog = ref(false)
const allCompetences = ref([])
const userCompetences = ref([])
const skillError = ref('')
const skillLoading = ref(false)
const isAddingSkill = ref(false)
const selectedSkillId = ref('')

const availableToAdd = computed(() =>
  allCompetences.value.filter(
    c => !userCompetences.value.some(u => u.id_competence === c.id_competence)
  )
)

const roleOptions = [
  { value: 'volunteer',   label: 'Bénévole',    desc: 'Utilisateur de base' },
  { value: 'mission_manager',   label: 'Organisateur', desc: 'Bénévole + gestion des missions' },
  { value: 'admin',       label: 'Admin',        desc: 'Organisateur + gestion des événements' },
  { value: 'superadmin',  label: 'Super-admin',  desc: 'Tous les droits de la plateforme' },
]

const roleBadgeClass = (role) => ({
  volunteer:  'bg-primary-subtle text-primary border-primary',
  organizer:  'bg-success-subtle text-success border-success',
  mission_manager: 'bg-success-subtle text-success border-success',
  admin:      'bg-info-subtle text-info border-info',
  superadmin: 'bg-danger-subtle text-danger border-danger',
}[role] || 'bg-secondary-subtle text-secondary border-secondary')

const loadSkills = async (userId) => {
  if (!userId) return
  skillError.value = ''
  skillLoading.value = true
  try {
    const [all, mine] = await Promise.all([
      competenceService.getAll(),
      competenceService.getUserCompetences(userId),
    ])
    allCompetences.value = all
    userCompetences.value = mine
  } catch {
    skillError.value = 'Impossible de charger les compétences utilisateur.'
  } finally {
    skillLoading.value = false
  }
}

const addSkill = async () => {
  if (!targetUser.value?.id || !selectedSkillId.value) return
  skillError.value = ''
  skillLoading.value = true
  try {
    await competenceService.addToUser(targetUser.value.id, selectedSkillId.value)
    await loadSkills(targetUser.value.id)
    selectedSkillId.value = ''
    isAddingSkill.value = false
  } catch (error) {
    skillError.value = error?.response?.data?.message || 'Erreur lors de l\'ajout de la compétence.'
  } finally {
    skillLoading.value = false
  }
}

const removeSkill = async (competenceId) => {
  if (!targetUser.value?.id) return
  skillError.value = ''
  skillLoading.value = true
  try {
    await competenceService.removeFromUser(targetUser.value.id, competenceId)
    await loadSkills(targetUser.value.id)
  } catch (error) {
    skillError.value = error?.response?.data?.message || 'Erreur lors de la suppression de la compétence.'
  } finally {
    skillLoading.value = false
  }
}

const cancelAddSkill = () => {
  isAddingSkill.value = false
  selectedSkillId.value = ''
}

const toUpdatePayload = (extra = {}) => ({
  firstName: extra.firstName ?? editForm.value.firstName ?? targetUser.value.firstName,
  lastName: extra.lastName ?? editForm.value.lastName ?? targetUser.value.lastName,
  email: extra.email ?? editForm.value.email ?? targetUser.value.email,
  phone: extra.phone ?? editForm.value.phone ?? targetUser.value.phone,
  address: extra.address ?? editForm.value.address ?? targetUser.value.address,
  dateOfBirth: extra.dateOfBirth ?? targetUser.value.dateOfBirth ?? '',
  role: extra.role ?? selectedRole.value ?? targetUser.value.role,
  allergies: extra.allergies ?? targetUser.value.allergies ?? [],
  healthIssues: extra.healthIssues ?? targetUser.value.healthIssues ?? [],
  hasLicense: extra.hasLicense ?? !!targetUser.value.hasLicense,
  isMotorized: extra.isMotorized ?? !!targetUser.value.isMotorized,
  hasVehicle: extra.hasVehicle ?? !!targetUser.value.hasVehicle,
  bibSize: extra.bibSize ?? targetUser.value.bibSize ?? '',
  isAnonymous: extra.isAnonymous ?? !!targetUser.value.anonymous,
  isSuspended: extra.isSuspended ?? !!targetUser.value.suspended,
  suspensionReason: extra.suspensionReason ?? targetUser.value.suspensionReason ?? null,
  missionCount: extra.missionCount ?? targetUser.value.missionCount ?? 0,
})

const handleSaveInfo = async () => {
  if (!targetUser.value?.id) {
    alert('Impossible de modifier cet utilisateur (ID manquant).')
    return
  }

  try {
    const response = await userService.update(targetUser.value.id, toUpdatePayload())
    if (response.user) {
      targetUser.value = response.user
      selectedRole.value = response.user.role
      syncEditForm()
    }
    alert('Informations utilisateur mises à jour')
  } catch (error) {
    alert(error.message || 'Erreur lors de la mise à jour')
  }
}

const handleSubmit = async () => {
  if (!targetUser.value?.id) {
    alert('Impossible de modifier cet utilisateur (ID manquant).')
    return
  }

  try {
    const response = await userService.update(targetUser.value.id, toUpdatePayload({ role: selectedRole.value }))
    if (response.user) {
      targetUser.value = response.user
    }
    alert(`Rôle de ${targetUser.value.firstName} ${targetUser.value.lastName} mis à jour avec succès !`)
    router.push('/manage-users')
  } catch (error) {
    alert(error.message || 'Erreur lors de la modification du rôle')
  }
}
const handleSuspend = async () => {
  if (!targetUser.value?.id) {
    alert('Impossible de suspendre cet utilisateur (ID manquant).')
    return
  }

  try {
    const response = await userService.update(targetUser.value.id, toUpdatePayload({
      isSuspended: true,
      suspensionReason: 'Suspendu par super-admin',
    }))
    if (response.user) {
      targetUser.value = response.user
    }
    alert(`Utilisateur ${targetUser.value.firstName} ${targetUser.value.lastName} suspendu.`)
    showSuspendDialog.value = false
    router.push('/manage-users')
  } catch (error) {
    alert(error.message || 'Erreur lors de la suspension')
  }
}
const handleAnonymize = async () => {
  if (!targetUser.value?.id) {
    alert('Impossible de modifier cet utilisateur (ID manquant).')
    return
  }

  try {
    const response = await userService.update(targetUser.value.id, toUpdatePayload({
      isAnonymous: !targetUser.value.anonymous,
    }))
    if (response.user) {
      targetUser.value = response.user
    }
    alert(`Utilisateur ${targetUser.value.firstName} ${targetUser.value.lastName} ${targetUser.value.anonymous ? 'rendu anonyme' : 'désanonymisé'}.`)
    showAnonymizeDialog.value = false
  } catch (error) {
    alert(error.message || 'Erreur lors de l\'anonymisation')
  }
}
const handleReactivate = async () => {
  if (!targetUser.value?.id) {
    alert('Impossible de réactiver cet utilisateur (ID manquant).')
    return
  }

  try {
    const response = await userService.update(targetUser.value.id, toUpdatePayload({
      isSuspended: false,
      suspensionReason: null,
    }))
    if (response.user) {
      targetUser.value = response.user
    }
    alert(`Utilisateur ${targetUser.value.firstName} ${targetUser.value.lastName} réactivé.`)
    showReactivateDialog.value = false
  } catch (error) {
    alert(error.message || 'Erreur lors de la réactivation')
  }
}

</script>