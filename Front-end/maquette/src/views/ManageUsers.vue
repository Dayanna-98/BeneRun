<template>
  <div class="min-vh-100 pb-5" style="background:#f0f4f8">
    <header class="text-white sticky-top overflow-hidden"
      style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-white" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <div>
            <div class="x-small" style="color:rgba(255,255,255,.55)">Administration</div>
            <h1 class="fs-5 fw-semibold mb-0">Gestion des Utilisateurs</h1>
          </div>
        </div>
      </div>
      <div class="px-3 pb-3 d-flex justify-content-end">
        <button class="btn btn-sm fw-semibold d-flex align-items-center gap-2 px-3 rounded-pill flex-shrink-0"
          style="background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230;border:none"
          @click="router.push('/manage-users/create')">
          <Plus style="width:16px;height:16px" /> Créer
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:1024px">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card"><div class="card-body p-3"><div class="fs-3 fw-bold text-primary">{{ usersList.length }}</div><div class="x-small text-muted">Total utilisateurs</div></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card"><div class="card-body p-3"><div class="fs-3 fw-bold" style="color:#2563eb">{{ volunteerCount }}</div><div class="x-small text-muted">Bénévoles</div></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card"><div class="card-body p-3"><div class="fs-3 fw-bold text-success">{{ organizerCount }}</div><div class="x-small text-muted">Organisateurs</div></div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card"><div class="card-body p-3"><div class="fs-3 fw-bold text-info">{{ adminCount }}</div><div class="x-small text-muted">Admins</div></div></div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-12 col-md-4">
              <input
                v-model="rawSearchQuery"
                type="text"
                class="form-control"
                placeholder="Rechercher nom, email, téléphone..."
              />
            </div>
            <div class="col-6 col-md-2">
              <select v-model="roleFilter" class="form-select">
                <option value="all">Tous rôles</option>
                <option value="volunteer">Bénévole</option>
                <option value="mission_manager">Responsable</option>
                <option value="admin">Admin</option>
                <option value="superadmin">Super-admin</option>
              </select>
            </div>
            <div class="col-6 col-md-2">
              <select v-model="statusFilter" class="form-select">
                <option value="all">Tous statuts</option>
                <option value="active">Actifs</option>
                <option value="suspended">Suspendus</option>
              </select>
            </div>
            <div class="col-6 col-md-2">
              <select v-model="anonymousFilter" class="form-select">
                <option value="all">Anonyme ?</option>
                <option value="yes">Anonymes</option>
                <option value="no">Non anonymes</option>
              </select>
            </div>
            <div class="col-6 col-md-2">
              <select v-model="sortBy" class="form-select">
                <option value="name_asc">Nom A-Z</option>
                <option value="name_desc">Nom Z-A</option>
                <option value="email_asc">Email A-Z</option>
                <option value="role_asc">Rôle</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
          <h5 class="mb-0">Tous les utilisateurs</h5>
          <span class="badge bg-secondary-subtle text-secondary border">{{ filteredUsers.length }} affichés</span>
        </div>
        <div class="card-body d-flex flex-column gap-3">
          <div v-if="searchLoading" class="small text-muted d-flex align-items-center gap-2">
            <span class="spinner-border spinner-border-sm" role="status"></span>
            Mise a jour des resultats...
          </div>

          <div v-if="selectedUserIds.length > 0" class="bulk-bar rounded border p-2 d-flex flex-wrap align-items-center gap-2">
            <span class="small fw-semibold me-2">{{ selectedUserIds.length }} sélectionné(s)</span>

            <select v-model="bulkRole" class="form-select form-select-sm" style="max-width:220px">
              <option value="">Changer le rôle...</option>
              <option value="volunteer">Bénévole</option>
              <option value="mission_manager">Responsable</option>
              <option value="admin">Admin</option>
              <option value="superadmin">Super-admin</option>
            </select>
            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" :disabled="bulkSaving || !bulkRole" @click="applyBulkRole">
              <span v-if="bulkSaving" class="spinner-border spinner-border-sm" role="status"></span>
              Appliquer rôle
            </button>

            <select v-model="bulkAnonymousMode" class="form-select form-select-sm" style="max-width:220px">
              <option value="">Anonymisation...</option>
              <option value="anonymize">Rendre anonymes</option>
              <option value="deanonymize">Retirer anonymisation</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" :disabled="bulkSaving || !bulkAnonymousMode" @click="applyBulkAnonymize">
              <span v-if="bulkSaving" class="spinner-border spinner-border-sm" role="status"></span>
              Appliquer anonymisation
            </button>

            <button class="btn btn-sm btn-outline-danger ms-auto" :disabled="bulkSaving" @click="clearSelection">
              Vider sélection
            </button>
          </div>

          <div v-if="bulkFeedback.show" :class="`alert alert-${bulkFeedback.type} small mb-0`">
            {{ bulkFeedback.message }}
          </div>

          <div v-if="filteredUsers.length > 0" class="d-flex align-items-center gap-2 px-1">
            <input
              id="select-all-users"
              class="form-check-input"
              type="checkbox"
              :checked="allVisibleSelected"
              @change="toggleSelectAllVisible"
            />
            <label for="select-all-users" class="small text-muted mb-0">Tout sélectionner (liste filtrée)</label>
          </div>

          <div v-for="u in visibleUsers" :key="u.id" :class="['border rounded p-3', u.suspended ? 'border-danger bg-danger-subtle' : 'border-light-subtle']">
            <div class="d-flex align-items-start justify-content-between mb-3">
              <div class="d-flex align-items-center gap-3 flex-fill">
                <input
                  class="form-check-input mt-0"
                  type="checkbox"
                  :checked="selectedUserIds.includes(u.id)"
                  @change="toggleUserSelection(u.id)"
                />
                <div class="position-relative flex-shrink-0">
                  <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width:48px;height:48px;background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230!important">
                    {{ initials(u) }}
                  </div>
                  <div v-if="u.suspended" class="position-absolute rounded-circle bg-danger d-flex align-items-center justify-content-center" style="width:20px;height:20px;top:-4px;right:-4px">
                    <UserX style="width:12px;height:12px;color:white" />
                  </div>
                  <div v-else-if="u.anonymous" class="position-absolute rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:20px;height:20px;top:-4px;right:-4px">
                    <EyeOff style="width:12px;height:12px;color:white" />
                  </div>
                </div>

                <div class="flex-fill">
                  <button class="btn btn-link p-0 fw-semibold text-primary mb-1 text-decoration-none"
                    @click="openUserDrawer(u)">
                    {{ u.anonymous ? 'Utilisateur Anonyme' : `${u.firstName} ${u.lastName}` }}
                  </button>
                  <div class="d-flex flex-wrap gap-1">
                    <span :class="`badge border ${roleBadgeClass(u.role)}`">{{ u.accountType }}</span>
                    <span v-if="u.suspended" class="badge bg-danger-subtle text-danger border border-danger">Suspendu</span>
                    <span v-if="u.anonymous" class="badge bg-secondary-subtle text-secondary border border-secondary d-inline-flex align-items-center gap-1">
                      <EyeOff style="width:12px;height:12px" /> Anonyme
                    </span>
                  </div>
                </div>
              </div>

              <div class="d-flex align-items-center gap-1 flex-shrink-0">
                <button class="btn btn-sm rounded-pill px-2" style="background:#eef2ff;color:#4338ca;border:none" title="Voir le détail"
                  @click="openUserDrawer(u)">
                  <Info style="width:16px;height:16px" />
                </button>
                <button class="btn btn-sm rounded-pill px-2" style="background:#fef3c7;color:#d97706;border:none" title="Gérer les badges" @click="openBadgeManager(u)">
                  <Award style="width:16px;height:16px" />
                </button>
                <button class="btn btn-sm rounded-pill px-2" style="background:#eff6ff;color:#3b82f6;border:none" title="Gérer les certificats" @click="openCertificateManager(u)">
                  <FileText style="width:16px;height:16px" />
                </button>
                <button class="btn btn-sm rounded-pill px-2" style="background:#f3f4f6;color:#4b5563;border:none" @click="router.push(`/manage-users/edit/${u.id}`)">
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
                <span v-for="group in groupedPermissions(u)" :key="`${u.id}-${group.label}`" class="badge bg-light text-dark border x-small">
                  {{ group.label }}: {{ group.items.join(', ') }}
                </span>
              </div>
            </div>
          </div>

          <div v-if="hasMoreUsers" class="text-center py-1">
            <button class="btn btn-outline-primary btn-sm" @click="loadMoreUsers">
              Voir plus d'utilisateurs
            </button>
          </div>

          <div v-if="filteredUsers.length === 0" class="text-center py-4 text-muted small">
            Aucun utilisateur ne correspond aux filtres.
          </div>
        </div>
      </div>
    </div>

    <div v-if="userDrawer.open" class="drawer-backdrop" @click.self="closeUserDrawer">
      <aside class="user-drawer card border-0 shadow-lg">
        <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom">
          <h6 class="mb-0 fw-semibold">Détail utilisateur</h6>
          <button class="btn btn-link p-0 text-secondary" @click="closeUserDrawer">
            <X style="width:20px;height:20px" />
          </button>
        </div>
        <div class="p-3 d-flex flex-column gap-3 drawer-content">
          <div v-if="userDrawer.user" class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
              style="width:52px;height:52px;background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230">
              {{ initials(userDrawer.user) }}
            </div>
            <div>
              <div class="fw-semibold">{{ userDrawer.user.firstName }} {{ userDrawer.user.lastName }}</div>
              <div class="small text-muted">{{ userDrawer.user.email || 'Email non renseigné' }}</div>
            </div>
          </div>

          <div v-if="userDrawer.user" class="small text-muted d-flex flex-column gap-1">
            <div>Téléphone: {{ userDrawer.user.phone || 'Non renseigné' }}</div>
            <div>Rôle: {{ userDrawer.user.accountType }}</div>
            <div>Anonyme: {{ userDrawer.user.anonymous ? 'Oui' : 'Non' }}</div>
            <div>Statut: {{ userDrawer.user.suspended ? 'Suspendu' : 'Actif' }}</div>
          </div>

          <div class="border-top pt-3">
            <div class="fw-medium small mb-2">Permissions actives</div>
            <div v-if="!userDrawer.user || activePermissions(userDrawer.user).length === 0" class="small text-muted">
              Aucune permission explicite.
            </div>
            <div v-else class="d-flex flex-column gap-2">
              <div v-for="group in groupedPermissions(userDrawer.user)" :key="`drawer-${group.label}`" class="small">
                <div class="fw-semibold">{{ group.label }}</div>
                <div class="text-muted">{{ group.items.join(', ') }}</div>
              </div>
            </div>
          </div>

          <div class="border-top pt-3">
            <div class="fw-medium small mb-2">Badges</div>
            <div v-if="userDrawer.loading" class="small text-muted">Chargement...</div>
            <div v-else-if="userDrawer.badges.length === 0" class="small text-muted">Aucun badge.</div>
            <ul v-else class="small mb-0 ps-3">
              <li v-for="b in userDrawer.badges.slice(0, 6)" :key="b.id_badge">{{ b.titre_badge }}</li>
            </ul>
          </div>

          <div class="border-top pt-3">
            <div class="fw-medium small mb-2">Certificats</div>
            <div v-if="userDrawer.loading" class="small text-muted">Chargement...</div>
            <div v-else-if="userDrawer.certificates.length === 0" class="small text-muted">Aucun certificat.</div>
            <ul v-else class="small mb-0 ps-3">
              <li v-for="c in userDrawer.certificates.slice(0, 6)" :key="c.id">{{ c.name }}</li>
            </ul>
          </div>

          <div class="mt-auto d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" @click="closeUserDrawer">Fermer</button>
            <button v-if="userDrawer.user" class="btn btn-primary btn-sm" @click="router.push(`/manage-users/edit/${userDrawer.user.id}`)">
              Modifier
            </button>
          </div>
        </div>
      </aside>
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

    <div v-if="certificateModal.open" class="modal-backdrop-custom" @click.self="closeCertificateManager">
      <div class="modal-dialog-custom card shadow-lg">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0 d-flex align-items-center gap-2">
            <FileText style="width:18px;height:18px;color:#0d6efd" />
            Certificats de {{ certificateModal.user?.firstName }} {{ certificateModal.user?.lastName }}
          </h5>
          <button class="btn btn-link p-0 text-secondary" @click="closeCertificateManager">
            <X style="width:20px;height:20px" />
          </button>
        </div>
        <div class="card-body d-flex flex-column gap-3">
          <div v-if="certificateModal.loading" class="text-muted small">
            <span class="spinner-border spinner-border-sm me-1" role="status"></span>Chargement...
          </div>
          <div v-else-if="certificateModal.certificates.length === 0" class="text-muted small fst-italic">
            Aucun certificat attribué.
          </div>
          <ul v-else class="list-group list-group-flush">
            <li v-for="cert in certificateModal.certificates" :key="cert.id" class="list-group-item py-3 px-0">
              <div v-if="certificateModal.editingId === cert.id" class="d-flex flex-column gap-2">
                <input v-model="certificateModal.editForm.name" type="text" class="form-control form-control-sm" placeholder="Titre" />
                <input v-model="certificateModal.editForm.issuer" type="text" class="form-control form-control-sm" placeholder="Emetteur" />
                <div class="row g-2">
                  <div class="col-6">
                    <input v-model="certificateModal.editForm.issueDate" type="date" class="form-control form-control-sm" />
                  </div>
                  <div class="col-6">
                    <input v-model="certificateModal.editForm.expiryDate" type="date" class="form-control form-control-sm" />
                  </div>
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <select v-model="certificateModal.editForm.type" class="form-select form-select-sm">
                      <option value="platform">Plateforme</option>
                      <option value="external">Externe</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <select v-model="certificateModal.editForm.status" class="form-select form-select-sm">
                      <option value="pending">En attente</option>
                      <option value="approved">Approuvé</option>
                      <option value="rejected">Rejeté</option>
                    </select>
                  </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-1">
                  <button class="btn btn-outline-secondary btn-sm" :disabled="certificateModal.saving" @click="cancelEditCertificate">Annuler</button>
                  <button class="btn btn-primary btn-sm" :disabled="certificateModal.saving" @click="saveEditedCertificate(cert.id)">
                    <span v-if="certificateModal.saving" class="spinner-border spinner-border-sm me-1" role="status"></span>
                    Enregistrer
                  </button>
                </div>
              </div>

              <div v-else class="d-flex align-items-start justify-content-between gap-2">
                <div class="flex-fill">
                  <div class="fw-medium small">{{ cert.name }}</div>
                  <div class="x-small text-muted">{{ cert.issuer || 'Emetteur non renseigné' }}</div>
                  <div class="d-flex flex-wrap gap-1 mt-1">
                    <span class="badge bg-secondary-subtle text-secondary border">{{ cert.type === 'platform' ? 'Plateforme' : 'Externe' }}</span>
                    <span class="badge" :class="statusBadgeClass(cert.status)">{{ formatStatus(cert.status) }}</span>
                  </div>
                  <div class="x-small text-muted mt-1">
                    Délivré le {{ formatDate(cert.issueDate) }}
                    <span v-if="cert.expiryDate"> • Expire le {{ formatDate(cert.expiryDate) }}</span>
                  </div>
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-outline-primary btn-sm" :disabled="certificateModal.saving" @click="startEditCertificate(cert)">
                    <Edit style="width:14px;height:14px" />
                  </button>
                  <button class="btn btn-outline-danger btn-sm" :disabled="certificateModal.saving" @click="deleteCertificateFromUser(cert)">
                    <Trash2 style="width:14px;height:14px" />
                  </button>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div v-if="toast.show" :class="`toast-custom alert alert-${toast.type} shadow`">
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Plus, Edit, UserX, EyeOff, Mail, Phone, Award, FileText, X, Trash2, Info } from 'lucide-vue-next'
import { getCurrentUser, isRole } from '@/utils/auth'
import userService from '@/services/userService'
import badgeService from '@/services/badgeService'
import certificatService from '@/services/certificatService'

const router = useRouter()
const user = getCurrentUser()
if (!user || !isRole('superadmin')) router.push('/')

const usersList = ref([])
const allBadges = ref([])
const toast = ref({ show: false, message: '', type: 'success' })
const rawSearchQuery = ref('')
const searchQuery = ref('')
const searchLoading = ref(false)
const roleFilter = ref('all')
const statusFilter = ref('all')
const anonymousFilter = ref('all')
const sortBy = ref('name_asc')
const selectedUserIds = ref([])
const bulkRole = ref('')
const bulkAnonymousMode = ref('')
const bulkSaving = ref(false)
const bulkFeedback = ref({ show: false, type: 'info', message: '' })

const PAGE_SIZE = 12
const visibleCount = ref(PAGE_SIZE)
const userDetailCache = ref({})
let searchTimer = null

const PERMISSION_GROUPS = {
  Administration: ['createAccount', 'assignPermissions'],
  Competences: ['createSkills'],
  Certifications: ['issueCertificate', 'createBadges'],
  Missions: ['createMissions', 'assignMissions', 'viewMissions'],
  Evenements: ['createEvents', 'manageEvents'],
}

const userDrawer = ref({
  open: false,
  user: null,
  badges: [],
  certificates: [],
  loading: false,
})

const badgeModal = ref({
  open: false,
  user: null,
  userBadges: [],
  selectedBadgeId: '',
  loading: false,
  saving: false,
})

const certificateModal = ref({
  open: false,
  user: null,
  certificates: [],
  loading: false,
  saving: false,
  editingId: null,
  editForm: {
    name: '',
    issuer: '',
    issueDate: '',
    expiryDate: '',
    type: 'platform',
    status: 'pending',
  },
})

onMounted(async () => {
  try {
    const dbUsers = await userService.getAll()
    usersList.value = dbUsers
  } catch (error) {
    console.error('Erreur chargement utilisateurs:', error)
    usersList.value = []
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

const filteredUsers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()

  const list = usersList.value.filter((u) => {
    const byRole = roleFilter.value === 'all' || u.role === roleFilter.value
    const byStatus = statusFilter.value === 'all'
      || (statusFilter.value === 'active' && !u.suspended)
      || (statusFilter.value === 'suspended' && !!u.suspended)
    const byAnon = anonymousFilter.value === 'all'
      || (anonymousFilter.value === 'yes' && !!u.anonymous)
      || (anonymousFilter.value === 'no' && !u.anonymous)

    const text = `${u.firstName || ''} ${u.lastName || ''} ${u.email || ''} ${u.phone || ''}`.toLowerCase()
    const byQuery = !q || text.includes(q)

    return byRole && byStatus && byAnon && byQuery
  })

  return [...list].sort((a, b) => {
    if (sortBy.value === 'name_desc') {
      return `${b.lastName} ${b.firstName}`.localeCompare(`${a.lastName} ${a.firstName}`, 'fr', { sensitivity: 'base' })
    }
    if (sortBy.value === 'email_asc') {
      return String(a.email || '').localeCompare(String(b.email || ''), 'fr', { sensitivity: 'base' })
    }
    if (sortBy.value === 'role_asc') {
      return String(a.accountType || '').localeCompare(String(b.accountType || ''), 'fr', { sensitivity: 'base' })
    }
    return `${a.lastName} ${a.firstName}`.localeCompare(`${b.lastName} ${b.firstName}`, 'fr', { sensitivity: 'base' })
  })
})

const visibleUsers = computed(() => filteredUsers.value.slice(0, visibleCount.value))
const hasMoreUsers = computed(() => visibleCount.value < filteredUsers.value.length)

const allVisibleSelected = computed(() =>
  filteredUsers.value.length > 0
  && filteredUsers.value.every((u) => selectedUserIds.value.includes(u.id))
)

const availableBadges = computed(() => {
  const owned = new Set(badgeModal.value.userBadges.map(b => b.id_badge))
  return allBadges.value.filter(b => !owned.has(b.id_badge))
})

function initials(u) {
  const first = (u?.firstName || '?').charAt(0)
  const last = (u?.lastName || '?').charAt(0)
  return `${first}${last}`.toUpperCase()
}

function activePermissions(targetUser) {
  if (!targetUser?.permissions) return []
  return Object.entries(targetUser.permissions)
    .filter(([, enabled]) => !!enabled)
    .map(([key]) => key)
}

function normalizePermissionLabel(permissionKey) {
  return permissionKey.replace(/([A-Z])/g, ' $1').trim()
}

function groupedPermissions(targetUser) {
  const active = activePermissions(targetUser)
  if (active.length === 0) return []

  const used = new Set()
  const groups = []

  Object.entries(PERMISSION_GROUPS).forEach(([label, keys]) => {
    const matched = keys.filter((key) => active.includes(key))
    if (matched.length > 0) {
      matched.forEach((key) => used.add(key))
      groups.push({ label, items: matched.map(normalizePermissionLabel) })
    }
  })

  const other = active.filter((key) => !used.has(key)).map(normalizePermissionLabel)
  if (other.length > 0) groups.push({ label: 'Autres', items: other })

  return groups
}

function toggleUserSelection(userId) {
  if (selectedUserIds.value.includes(userId)) {
    selectedUserIds.value = selectedUserIds.value.filter((id) => id !== userId)
  } else {
    selectedUserIds.value = [...selectedUserIds.value, userId]
  }
}

function toggleSelectAllVisible() {
  if (allVisibleSelected.value) {
    const visibleIds = new Set(filteredUsers.value.map((u) => u.id))
    selectedUserIds.value = selectedUserIds.value.filter((id) => !visibleIds.has(id))
  } else {
    const merged = new Set([...selectedUserIds.value, ...filteredUsers.value.map((u) => u.id)])
    selectedUserIds.value = Array.from(merged)
  }
}

function clearSelection() {
  selectedUserIds.value = []
  bulkRole.value = ''
  bulkAnonymousMode.value = ''
}

function loadMoreUsers() {
  if (hasMoreUsers.value) visibleCount.value += PAGE_SIZE
}

function setBulkFeedback(type, message) {
  bulkFeedback.value = { show: true, type, message }
}

function toUpdatePayload(targetUser, extra = {}) {
  return {
    firstName: extra.firstName ?? targetUser.firstName,
    lastName: extra.lastName ?? targetUser.lastName,
    email: extra.email ?? targetUser.email,
    phone: extra.phone ?? targetUser.phone,
    address: extra.address ?? targetUser.address,
    dateOfBirth: extra.dateOfBirth ?? targetUser.dateOfBirth,
    role: extra.role ?? targetUser.role,
    allergies: extra.allergies ?? targetUser.allergies ?? [],
    healthIssues: extra.healthIssues ?? targetUser.healthIssues ?? [],
    hasLicense: extra.hasLicense ?? !!targetUser.hasLicense,
    isMotorized: extra.isMotorized ?? !!targetUser.isMotorized,
    hasVehicle: extra.hasVehicle ?? !!targetUser.hasVehicle,
    bibSize: extra.bibSize ?? targetUser.bibSize,
    isAnonymous: extra.isAnonymous ?? !!targetUser.anonymous,
    isSuspended: extra.isSuspended ?? !!targetUser.suspended,
    suspensionReason: extra.suspensionReason ?? targetUser.suspensionReason ?? null,
    missionCount: extra.missionCount ?? targetUser.missionCount ?? 0,
    permissions: extra.permissions ?? targetUser.permissions ?? {},
  }
}

async function applyBulkRole() {
  if (!bulkRole.value || selectedUserIds.value.length === 0) return
  if (!confirm(`Appliquer le rôle à ${selectedUserIds.value.length} utilisateur(s) ?`)) return

  bulkSaving.value = true
  let successCount = 0
  const errors = []

  try {
    for (const userId of selectedUserIds.value) {
      const current = usersList.value.find((u) => u.id === userId)
      if (!current) continue

      try {
        const response = await userService.update(userId, toUpdatePayload(current, { role: bulkRole.value }))
        if (response?.user) {
          const idx = usersList.value.findIndex((u) => u.id === userId)
          if (idx !== -1) usersList.value[idx] = response.user
          successCount += 1
        }
      } catch {
        errors.push(`${current.firstName} ${current.lastName}`)
      }
    }

    if (errors.length === 0) {
      setBulkFeedback('success', `${successCount} utilisateur(s) mis a jour.`)
      showToast(`${successCount} utilisateur(s) mis a jour.`, 'success')
    } else {
      setBulkFeedback('warning', `${successCount} mis a jour, ${errors.length} en echec.`)
      showToast('Mise a jour partielle.', 'warning')
    }
    clearSelection()
  } catch (error) {
    showToast(error?.message || 'Erreur lors de la mise à jour en masse.', 'danger')
    setBulkFeedback('danger', 'La mise a jour en masse a echoue.')
  } finally {
    bulkSaving.value = false
  }
}

async function applyBulkAnonymize() {
  if (!bulkAnonymousMode.value || selectedUserIds.value.length === 0) return

  const targetValue = bulkAnonymousMode.value === 'anonymize'
  const confirmLabel = targetValue ? 'rendre anonymes' : 'retirer l\'anonymisation'
  if (!confirm(`Confirmer: ${confirmLabel} pour ${selectedUserIds.value.length} utilisateur(s) ?`)) return

  bulkSaving.value = true
  let successCount = 0
  const errors = []

  try {
    for (const userId of selectedUserIds.value) {
      const current = usersList.value.find((u) => u.id === userId)
      if (!current) continue

      try {
        const response = await userService.update(userId, toUpdatePayload(current, { isAnonymous: targetValue }))
        if (response?.user) {
          const idx = usersList.value.findIndex((u) => u.id === userId)
          if (idx !== -1) usersList.value[idx] = response.user
          successCount += 1
        }
      } catch {
        errors.push(`${current.firstName} ${current.lastName}`)
      }
    }

    if (errors.length === 0) {
      setBulkFeedback('success', `${successCount} utilisateur(s) mis a jour.`)
      showToast(`${successCount} utilisateur(s) mis a jour.`, 'success')
    } else {
      setBulkFeedback('warning', `${successCount} mis a jour, ${errors.length} en echec.`)
      showToast('Mise a jour partielle.', 'warning')
    }
    clearSelection()
  } catch (error) {
    showToast(error?.message || 'Erreur lors de la mise à jour en masse.', 'danger')
    setBulkFeedback('danger', 'La mise a jour en masse a echoue.')
  } finally {
    bulkSaving.value = false
  }
}

async function openUserDrawer(targetUser) {
  userDrawer.value = {
    open: true,
    user: targetUser,
    badges: [],
    certificates: [],
    loading: true,
  }

  try {
    const cache = userDetailCache.value[targetUser.id]
    if (cache) {
      userDrawer.value.badges = cache.badges
      userDrawer.value.certificates = cache.certificates
      userDrawer.value.loading = false
      return
    }

    const [badges, certificates] = await Promise.all([
      badgeService.getUserBadges(targetUser.id),
      certificatService.getByUserApi(targetUser.id),
    ])
    userDrawer.value.badges = badges || []
    userDrawer.value.certificates = certificates || []
    userDetailCache.value[targetUser.id] = {
      badges: userDrawer.value.badges,
      certificates: userDrawer.value.certificates,
    }
  } catch {
    userDrawer.value.badges = []
    userDrawer.value.certificates = []
    showToast('Impossible de charger le détail utilisateur.', 'danger')
  } finally {
    userDrawer.value.loading = false
  }
}

function closeUserDrawer() {
  userDrawer.value.open = false
}

watch(rawSearchQuery, (value) => {
  searchLoading.value = true
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    searchQuery.value = value
    searchLoading.value = false
  }, 250)
})

watch([searchQuery, roleFilter, statusFilter, anonymousFilter, sortBy], () => {
  visibleCount.value = PAGE_SIZE
})

onUnmounted(() => {
  if (searchTimer) clearTimeout(searchTimer)
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

function formatDate(value) {
  if (!value) return 'Date non renseignée'
  return new Date(value).toLocaleDateString('fr-FR')
}

function formatStatus(status) {
  if (status === 'approved') return 'Approuvé'
  if (status === 'rejected') return 'Rejeté'
  return 'En attente'
}

function statusBadgeClass(status) {
  if (status === 'approved') return 'bg-success-subtle text-success border border-success-subtle'
  if (status === 'rejected') return 'bg-danger-subtle text-danger border border-danger-subtle'
  return 'bg-warning-subtle text-warning border border-warning-subtle'
}

async function openCertificateManager(targetUser) {
  certificateModal.value = {
    open: true,
    user: targetUser,
    certificates: [],
    loading: true,
    saving: false,
    editingId: null,
    editForm: {
      name: '',
      issuer: '',
      issueDate: '',
      expiryDate: '',
      type: 'platform',
      status: 'pending',
    },
  }

  try {
    certificateModal.value.certificates = await certificatService.getByUserApi(targetUser.id)
  } catch {
    showToast('Impossible de charger les certificats utilisateur.', 'danger')
  } finally {
    certificateModal.value.loading = false
  }
}

function closeCertificateManager() {
  certificateModal.value.open = false
}

function startEditCertificate(cert) {
  certificateModal.value.editingId = cert.id
  certificateModal.value.editForm = {
    name: cert.name || '',
    issuer: cert.issuer || '',
    issueDate: cert.issueDate || '',
    expiryDate: cert.expiryDate || '',
    type: cert.type || 'platform',
    status: cert.status || 'pending',
  }
}

function cancelEditCertificate() {
  certificateModal.value.editingId = null
}

async function saveEditedCertificate(certId) {
  if (!certificateModal.value.user?.id) return

  const payload = {
    userId: certificateModal.value.user.id,
    name: certificateModal.value.editForm.name,
    issuer: certificateModal.value.editForm.issuer,
    issueDate: certificateModal.value.editForm.issueDate || null,
    expiryDate: certificateModal.value.editForm.expiryDate || null,
    type: certificateModal.value.editForm.type,
    status: certificateModal.value.editForm.status,
  }

  certificateModal.value.saving = true
  try {
    const updated = await certificatService.update(certId, payload)
    const idx = certificateModal.value.certificates.findIndex(c => c.id === certId)
    if (idx !== -1) {
      certificateModal.value.certificates[idx] = updated
    }
    certificateModal.value.editingId = null
    showToast('Certificat mis à jour.', 'success')
  } catch {
    showToast('Erreur lors de la modification du certificat.', 'danger')
  } finally {
    certificateModal.value.saving = false
  }
}

async function deleteCertificateFromUser(cert) {
  if (!confirm(`Supprimer le certificat "${cert.name}" ?`)) return

  certificateModal.value.saving = true
  try {
    await certificatService.delete(cert.id)
    certificateModal.value.certificates = certificateModal.value.certificates.filter(c => c.id !== cert.id)
    showToast('Certificat supprimé de l utilisateur.', 'success')
  } catch {
    showToast('Erreur lors de la suppression du certificat.', 'danger')
  } finally {
    certificateModal.value.saving = false
  }
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
  z-index: 5000;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  overflow-y: auto;
  padding: max(0.75rem, env(safe-area-inset-top, 0px)) 1rem calc(0.75rem + env(safe-area-inset-bottom, 0px));
}

.modal-dialog-custom {
  width: 100%;
  max-width: 560px;
  max-height: calc(100dvh - 1.5rem - env(safe-area-inset-bottom, 0px));
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-radius: 0.75rem;
}

.modal-dialog-custom .card-body {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.stat-card {
  border: 0;
  box-shadow: 0 6px 18px rgba(26, 34, 48, 0.08);
}

.bulk-bar {
  background: #f8fafc;
  border-color: #dbe4f0 !important;
}

.drawer-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(17, 24, 39, 0.45);
  z-index: 5100;
  display: flex;
  justify-content: flex-end;
}

.user-drawer {
  width: min(94vw, 420px);
  height: 100dvh;
  border-radius: 0;
}

.drawer-content {
  overflow-y: auto;
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