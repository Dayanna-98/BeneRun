<template>
  <div class="min-vh-100 bg-light pb-5">
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Gestion des Utilisateurs</h1>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" @click="router.push('/manage-users/create')">
          <Plus style="width:16px;height:16px" /> Créer
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:1024px">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card text-center"><div class="card-body p-3"><div class="fs-3 fw-bold text-primary">{{ usersList.length }}</div><div class="x-small text-muted">Total utilisateurs</div></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center"><div class="card-body p-3"><div class="fs-3 fw-bold" style="color:#2563eb">{{ volunteerCount }}</div><div class="x-small text-muted">Bénévoles</div></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center"><div class="card-body p-3"><div class="fs-3 fw-bold text-success">{{ organizerCount }}</div><div class="x-small text-muted">Organisateurs</div></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center"><div class="card-body p-3"><div class="fs-3 fw-bold text-info">{{ adminCount }}</div><div class="x-small text-muted">Admins</div></div></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h5 class="mb-0">Tous les utilisateurs</h5></div>
        <div class="card-body d-flex flex-column gap-3">
          <div v-for="u in usersList" :key="u.id" :class="['border rounded p-3', u.suspended ? 'border-danger bg-danger-subtle' : 'border-light-subtle']">
            <div class="d-flex align-items-start justify-content-between mb-3">
              <div class="d-flex align-items-center gap-3 flex-fill">
                <div class="position-relative flex-shrink-0">
                  <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width:48px;height:48px;background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230!important">
                    {{ u.firstName[0] }}{{ u.lastName[0] }}
                  </div>
                  <div v-if="u.suspended" class="position-absolute rounded-circle bg-danger d-flex align-items-center justify-content-center" style="width:20px;height:20px;top:-4px;right:-4px">
                    <UserX style="width:12px;height:12px;color:white" />
                  </div>
                  <div v-else-if="u.anonymous" class="position-absolute rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:20px;height:20px;top:-4px;right:-4px">
                    <EyeOff style="width:12px;height:12px;color:white" />
                  </div>
                </div>

                <div class="flex-fill">
                  <h6 class="fw-semibold text-primary mb-1">
                    {{ u.anonymous ? 'Utilisateur Anonyme' : `${u.firstName} ${u.lastName}` }}
                  </h6>
                  <div class="d-flex flex-wrap gap-1">
                    <span :class="`badge border ${roleBadgeClass(u.role)}`">{{ u.accountType }}</span>
                    <span v-if="u.suspended" class="badge bg-danger-subtle text-danger border border-danger">Suspendu</span>
                    <span v-if="u.anonymous" class="badge bg-secondary-subtle text-secondary border border-secondary d-inline-flex align-items-center gap-1">
                      <EyeOff style="width:12px;height:12px" /> Anonyme
                    </span>
                  </div>
                </div>
              </div>

              <div class="d-flex align-items-center gap-1">
                <button class="btn btn-link p-1 text-warning" title="Gérer les badges" @click="openBadgeManager(u)">
                  <Award style="width:16px;height:16px" />
                </button>
                <button class="btn btn-link p-1 text-secondary" @click="router.push(`/manage-users/${u.id}`)">
                  <Edit style="width:16px;height:16px" />
                </button>
              </div>
            </div>

            <div class="row g-2 small text-muted">
              <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                <Mail style="width:16px;height:16px" />
                <span class="x-small text-truncate">{{ u.anonymous ? '***@***.***' : u.email }}</span>
              </div>
              <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                <Phone style="width:16px;height:16px" />
                <span class="x-small">{{ u.anonymous ? '***' : u.phone }}</span>
              </div>
            </div>

            <div v-if="!u.suspended && u.permissions" class="mt-3 pt-3 border-top">
              <div class="x-small text-muted mb-2">Permissions :</div>
              <div class="d-flex flex-wrap gap-1">
                <span v-for="(val, key) in u.permissions" :key="key" v-show="val === true" class="badge bg-light text-dark border x-small">
                  {{ key.replace(/([A-Z])/g, ' $1').trim() }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="badgeModal.open" class="modal-backdrop-custom" @click.self="closeBadgeManager">
      <div class="modal-dialog-custom card shadow-lg">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0 d-flex align-items-center gap-2">
            <Award style="width:18px;height:18px;color:#f39c12" />
            Badges de {{ badgeModal.user?.firstName }} {{ badgeModal.user?.lastName }}
          </h5>
          <button class="btn btn-link p-0 text-secondary" @click="closeBadgeManager">
            <X style="width:20px;height:20px" />
          </button>
        </div>
        <div class="card-body d-flex flex-column gap-3">
          <div>
            <div class="fw-medium small mb-2">Badges attribués</div>
            <div v-if="badgeModal.loading" class="text-muted small">
              <span class="spinner-border spinner-border-sm me-1" role="status"></span>Chargement...
            </div>
            <div v-else-if="badgeModal.userBadges.length === 0" class="text-muted small fst-italic">
              Aucun badge attribué.
            </div>
            <ul v-else class="list-group list-group-flush">
              <li v-for="b in badgeModal.userBadges" :key="b.id_badge" class="list-group-item d-flex align-items-center justify-content-between py-2 px-0">
                <div>
                  <span class="fw-medium small">{{ b.titre_badge }}</span>
                  <span v-if="b.score_badge" class="text-muted x-small ms-2">{{ b.score_badge }} pts</span>
                </div>
                <button class="btn btn-outline-danger btn-sm" @click="removeBadgeFromUser(b.id_badge)" :disabled="badgeModal.saving">
                  Retirer
                </button>
              </li>
            </ul>
          </div>

          <div class="border-top pt-3">
            <div class="fw-medium small mb-2">Attribuer un badge</div>
            <div class="d-flex gap-2">
              <select v-model="badgeModal.selectedBadgeId" class="form-select form-select-sm" :disabled="badgeModal.saving || availableBadges.length === 0">
                <option value="">Choisir un badge...</option>
                <option v-for="b in availableBadges" :key="b.id_badge" :value="b.id_badge">
                  {{ b.titre_badge }}{{ b.score_badge ? ` (${b.score_badge} pts)` : '' }}
                </option>
              </select>
              <button class="btn btn-primary btn-sm" @click="addBadgeToUser" :disabled="badgeModal.saving || !badgeModal.selectedBadgeId">
                Ajouter
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="toast.show" :class="`toast-custom alert alert-${toast.type} shadow`">
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Plus, Edit, UserX, EyeOff, Mail, Phone, Award, X } from 'lucide-vue-next'
import { users } from '@/data/mockData'
import { getCurrentUser, isRole } from '@/utils/auth'
import userService from '@/services/userService'
import badgeService from '@/services/badgeService'

const demoUsers = users.filter(userData => userData.email === 'admin@benerun.ch')

const router = useRouter()
const user = getCurrentUser()
if (!user || !isRole('superadmin')) router.push('/')

const usersList = ref([])
const allBadges = ref([])
const toast = ref({ show: false, message: '', type: 'success' })

const badgeModal = ref({
  open: false,
  user: null,
  userBadges: [],
  selectedBadgeId: '',
  loading: false,
  saving: false,
})

onMounted(async () => {
  try {
    const dbUsers = await userService.getAll()
    usersList.value = userService.mergeUsers(demoUsers, dbUsers)
  } catch (error) {
    console.error('Erreur chargement utilisateurs:', error)
    usersList.value = [...demoUsers]
  }

  try {
    allBadges.value = await badgeService.getAll()
  } catch {
    allBadges.value = []
  }
})

const volunteerCount = computed(() => usersList.value.filter(u => u.role === 'volunteer').length)
const organizerCount = computed(() => usersList.value.filter(u => u.role === 'organizer' || u.role === 'mission_manager').length)
const adminCount = computed(() => usersList.value.filter(u => u.role === 'admin' || u.role === 'superadmin').length)

const availableBadges = computed(() => {
  const owned = new Set(badgeModal.value.userBadges.map(b => b.id_badge))
  return allBadges.value.filter(b => !owned.has(b.id_badge))
})

const roleBadgeClass = (role) => ({
  volunteer: 'bg-primary-subtle text-primary border-primary',
  organizer: 'bg-success-subtle text-success border-success',
  mission_manager: 'bg-success-subtle text-success border-success',
  admin: 'bg-info-subtle text-info border-info',
  superadmin: 'bg-danger-subtle text-danger border-danger',
}[role] || 'bg-secondary-subtle text-secondary border-secondary')

async function openBadgeManager(targetUser) {
  badgeModal.value = {
    open: true,
    user: targetUser,
    userBadges: [],
    selectedBadgeId: '',
    loading: true,
    saving: false,
  }

  try {
    badgeModal.value.userBadges = await badgeService.getUserBadges(targetUser.id)
  } catch {
    showToast('Impossible de charger les badges utilisateur.', 'danger')
  } finally {
    badgeModal.value.loading = false
  }
}

function closeBadgeManager() {
  badgeModal.value.open = false
}

async function addBadgeToUser() {
  if (!badgeModal.value.selectedBadgeId) return
  badgeModal.value.saving = true
  try {
    await badgeService.addToUser(badgeModal.value.user.id, badgeModal.value.selectedBadgeId)
    badgeModal.value.userBadges = await badgeService.getUserBadges(badgeModal.value.user.id)
    badgeModal.value.selectedBadgeId = ''
    showToast('Badge attribué à l utilisateur.', 'success')
  } catch {
    showToast('Erreur lors de l attribution du badge.', 'danger')
  } finally {
    badgeModal.value.saving = false
  }
}

async function removeBadgeFromUser(badgeId) {
  badgeModal.value.saving = true
  try {
    await badgeService.removeFromUser(badgeModal.value.user.id, badgeId)
    badgeModal.value.userBadges = await badgeService.getUserBadges(badgeModal.value.user.id)
    showToast('Badge retiré de l utilisateur.', 'success')
  } catch {
    showToast('Erreur lors du retrait du badge.', 'danger')
  } finally {
    badgeModal.value.saving = false
  }
}

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => {
    toast.value.show = false
  }, 2500)
}
</script>

<style scoped>
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-dialog-custom {
  width: 100%;
  max-width: 560px;
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 0.75rem;
}

.toast-custom {
  position: fixed;
  bottom: 1.5rem;
  left: 50%;
  transform: translateX(-50%);
  z-index: 2000;
  min-width: 260px;
  text-align: center;
  padding: 0.65rem 1.25rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  pointer-events: none;
}

.x-small {
  font-size: 0.75rem;
}
</style>