<template>
  <div class="min-vh-100 bg-light pb-5">
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Gestion des certificats</h1>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" @click="openCreateModal">
          <Plus style="width:16px;height:16px" /> Créer
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:1024px">
      <div v-if="loadError" class="alert alert-danger mb-0">{{ loadError }}</div>

      <div class="row g-3">
        <div class="col-12 col-md-4">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary">{{ certificates.length }}</div>
              <div class="x-small text-muted">Certificats</div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-success">{{ approvedCount }}</div>
              <div class="x-small text-muted">Approuvés</div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-warning">{{ pendingCount }}</div>
              <div class="x-small text-muted">En attente</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body d-flex flex-column flex-md-row gap-2">
          <input
            v-model="search"
            type="text"
            class="form-control"
            placeholder="Rechercher par titre ou émetteur..."
          />
          <select v-model="selectedUserId" class="form-select" style="max-width:320px">
            <option value="">Tous les utilisateurs</option>
            <option v-for="u in users" :key="u.id" :value="String(u.id)">
              {{ u.firstName }} {{ u.lastName }} ({{ u.email }})
            </option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="text-center py-4 text-muted">
        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
        Chargement des certificats...
      </div>

      <div v-else class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Liste des certificats</h5>
          <span class="badge bg-primary-subtle text-primary border">{{ filteredCertificates.length }}</span>
        </div>
        <div v-if="filteredCertificates.length === 0" class="card-body text-center text-muted py-5">
          <FileText style="width:40px;height:40px;opacity:.3;margin-bottom:8px" />
          <p class="mb-0">Aucun certificat trouvé</p>
        </div>
        <ul v-else class="list-group list-group-flush">
          <li v-for="cert in filteredCertificates" :key="cert.id" class="list-group-item py-3">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <div class="flex-fill">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <span class="fw-semibold">{{ cert.name }}</span>
                  <span :class="['badge', cert.type === 'platform' ? 'bg-primary' : 'bg-secondary-subtle text-secondary border']">
                    {{ cert.type === 'platform' ? 'Plateforme' : 'Externe' }}
                  </span>
                  <span :class="statusBadgeClass(cert.status)">{{ formatStatus(cert.status) }}</span>
                </div>
                <div class="small text-muted mb-1">{{ cert.issuer || 'Emetteur non renseigné' }}</div>
                <div class="x-small text-muted mb-1">
                  Utilisateur: {{ getUserLabel(cert.userId) }}
                </div>
                <div class="x-small text-muted">
                  Délivré le {{ formatDate(cert.issueDate) }}
                  <span v-if="cert.expiryDate"> • Expire le {{ formatDate(cert.expiryDate) }}</span>
                </div>
                <div class="mt-2 pt-2 border-top">
                  <div class="x-small text-muted mb-2">Validation du certificat</div>
                  <div class="d-flex flex-wrap gap-2">
                    <button
                      class="btn btn-outline-success btn-sm"
                      :disabled="statusSavingId === cert.id || cert.status === 'approved'"
                      @click="updateStatus(cert, 'approved')"
                    >
                      Approuver
                    </button>
                    <button
                      class="btn btn-outline-danger btn-sm"
                      :disabled="statusSavingId === cert.id || cert.status === 'rejected'"
                      @click="updateStatus(cert, 'rejected')"
                    >
                      Rejeter
                    </button>
                  </div>
                </div>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" @click="openEditModal(cert)">
                  <Pencil style="width:14px;height:14px" />
                </button>
                <button class="btn btn-outline-danger btn-sm" @click="askDelete(cert)">
                  <Trash2 style="width:14px;height:14px" />
                </button>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <div v-if="showFormModal" class="modal-backdrop-custom" @click.self="closeFormModal">
      <div class="modal-dialog-custom card shadow-lg">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0 d-flex align-items-center gap-2">
            <Pencil v-if="editingId" style="width:18px;height:18px" />
            <Plus v-else style="width:18px;height:18px" />
            {{ editingId ? 'Modifier le certificat' : 'Nouveau certificat' }}
          </h5>
          <button class="btn btn-link p-0 text-secondary" @click="closeFormModal">
            <X style="width:20px;height:20px" />
          </button>
        </div>

        <div class="card-body d-flex flex-column gap-3">
          <div>
            <label class="form-label fw-medium">Utilisateur <span class="text-danger">*</span></label>
            <select v-model="form.userId" class="form-select" :class="formError.userId ? 'is-invalid' : ''">
              <option value="">Sélectionner un utilisateur</option>
              <option v-for="u in users" :key="u.id" :value="String(u.id)">
                {{ u.firstName }} {{ u.lastName }} ({{ u.email }})
              </option>
            </select>
            <div v-if="formError.userId" class="invalid-feedback d-block">{{ formError.userId }}</div>
          </div>

          <div>
            <label class="form-label fw-medium">Titre <span class="text-danger">*</span></label>
            <input v-model="form.name" type="text" class="form-control" :class="formError.name ? 'is-invalid' : ''" />
            <div v-if="formError.name" class="invalid-feedback d-block">{{ formError.name }}</div>
          </div>

          <div>
            <label class="form-label fw-medium">Emetteur</label>
            <input v-model="form.issuer" type="text" class="form-control" />
          </div>

          <div class="row g-2">
            <div class="col-6">
              <label class="form-label fw-medium">Date d'émission</label>
              <input v-model="form.issueDate" type="date" class="form-control" />
            </div>
            <div class="col-6">
              <label class="form-label fw-medium">Date d'expiration</label>
              <input v-model="form.expiryDate" type="date" class="form-control" />
            </div>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <label class="form-label fw-medium">Type</label>
              <select v-model="form.type" class="form-select">
                <option value="platform">Plateforme</option>
                <option value="external">Externe</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label fw-medium">Statut</label>
              <select v-model="form.status" class="form-select">
                <option value="pending">En attente</option>
                <option value="approved">Approuvé</option>
                <option value="rejected">Rejeté</option>
              </select>
            </div>
          </div>

          <div>
            <label class="form-label fw-medium">Fichier certificat</label>
            <input
              ref="fileInput"
              type="file"
              class="d-none"
              accept=".pdf,.png,.jpg,.jpeg"
              @change="onFileInputChange"
            />

            <div
              class="upload-dropzone rounded p-3 text-center"
              :class="isDraggingFile ? 'dragging' : ''"
              @dragenter.prevent="isDraggingFile = true"
              @dragover.prevent="isDraggingFile = true"
              @dragleave.prevent="isDraggingFile = false"
              @drop.prevent="onFileDrop"
            >
              <Upload style="width:20px;height:20px" class="mx-auto mb-2 text-secondary" />
              <div class="small fw-medium">Glisser-déposer ici, ou choisir un fichier</div>
              <div class="x-small text-muted">Formats acceptés: PDF, PNG, JPG, JPEG (max 10 Mo)</div>
              <div v-if="form.fileName" class="small mt-2 text-success">
                Fichier sélectionné: {{ form.fileName }}
              </div>
            </div>

            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-outline-secondary btn-sm" @click="openFilePicker">
                Parcourir le PC
              </button>
              <button
                v-if="form.file"
                type="button"
                class="btn btn-outline-danger btn-sm"
                @click="clearSelectedFile"
              >
                Retirer le fichier
              </button>
            </div>

            <div v-if="form.filePath && !form.file" class="x-small text-muted mt-2">
              Fichier actuel: {{ form.filePath }}
            </div>
          </div>
        </div>

        <div class="card-footer d-flex gap-2 justify-content-end">
          <button class="btn btn-outline-secondary" @click="closeFormModal">Annuler</button>
          <button class="btn btn-primary" @click="submitForm" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status"></span>
            {{ editingId ? 'Enregistrer' : 'Créer' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="deleteTarget" class="modal-backdrop-custom" @click.self="deleteTarget = null">
      <div class="modal-dialog-custom card shadow-lg">
        <div class="card-header"><h5 class="mb-0 text-danger">Supprimer le certificat</h5></div>
        <div class="card-body">
          <p class="mb-0">Confirmer la suppression de <strong>{{ deleteTarget.name }}</strong> ?</p>
        </div>
        <div class="card-footer d-flex gap-2 justify-content-end">
          <button class="btn btn-outline-secondary" @click="deleteTarget = null">Annuler</button>
          <button class="btn btn-danger" @click="confirmDelete" :disabled="deleting">
            <span v-if="deleting" class="spinner-border spinner-border-sm me-1" role="status"></span>
            Supprimer
          </button>
        </div>
      </div>
    </div>

    <div v-if="toast.show" :class="`toast-custom alert alert-${toast.type} shadow`">{{ toast.message }}</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeft, Plus, Pencil, Trash2, X, FileText, Upload } from 'lucide-vue-next'
import { getCurrentUser, isRole } from '@/utils/auth'
import userService from '@/services/userService'
import certificatService from '@/services/certificatService'

const route = useRoute()
const router = useRouter()
const user = getCurrentUser()
if (!user || !isRole('superadmin')) router.push('/')

const users = ref([])
const certificates = ref([])
const loading = ref(true)
const loadError = ref('')

const search = ref('')
const selectedUserId = ref('')

const showFormModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref({ userId: '', name: '' })
const form = ref(getDefaultForm())
const fileInput = ref(null)
const isDraggingFile = ref(false)
const statusSavingId = ref('')

const deleteTarget = ref(null)
const deleting = ref(false)
const toast = ref({ show: false, message: '', type: 'success' })

const approvedCount = computed(() => certificates.value.filter(c => c.status === 'approved').length)
const pendingCount = computed(() => certificates.value.filter(c => c.status === 'pending').length)

const filteredCertificates = computed(() => {
  const query = search.value.trim().toLowerCase()
  return certificates.value.filter((cert) => {
    const matchesUser = !selectedUserId.value || String(cert.userId) === String(selectedUserId.value)
    const matchesQuery = !query
      || (cert.name || '').toLowerCase().includes(query)
      || (cert.issuer || '').toLowerCase().includes(query)

    return matchesUser && matchesQuery
  })
})

onMounted(async () => {
  await loadUsers()
  await loadCertificates()

  const prefUser = route.query.userId
  if (prefUser) selectedUserId.value = String(prefUser)
})

async function loadUsers() {
  try {
    users.value = await userService.getAll()
  } catch {
    users.value = []
    loadError.value = 'Impossible de charger les utilisateurs.'
  }
}

async function loadCertificates() {
  loading.value = true
  loadError.value = ''
  try {
    certificates.value = await certificatService.getAll()
  } catch {
    certificates.value = []
    loadError.value = 'Impossible de charger les certificats.'
  } finally {
    loading.value = false
  }
}

function getDefaultForm() {
  return {
    userId: selectedUserId.value || '',
    name: '',
    issuer: '',
    issueDate: '',
    expiryDate: '',
    type: 'platform',
    status: 'pending',
    filePath: '',
    file: null,
    fileName: '',
  }
}

function getUserLabel(userId) {
  const target = users.value.find(u => String(u.id) === String(userId))
  if (!target) return `Utilisateur #${userId}`
  return `${target.firstName} ${target.lastName}`
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
  if (status === 'approved') return 'badge bg-success-subtle text-success border border-success-subtle'
  if (status === 'rejected') return 'badge bg-danger-subtle text-danger border border-danger-subtle'
  return 'badge bg-warning-subtle text-warning border border-warning-subtle'
}

function openCreateModal() {
  editingId.value = null
  form.value = getDefaultForm()
  formError.value = { userId: '', name: '' }
  showFormModal.value = true
}

function openEditModal(cert) {
  editingId.value = cert.id
  form.value = {
    userId: String(cert.userId || ''),
    name: cert.name || '',
    issuer: cert.issuer || '',
    issueDate: cert.issueDate || '',
    expiryDate: cert.expiryDate || '',
    type: cert.type || 'platform',
    status: cert.status || 'pending',
    filePath: cert.filePath || '',
    file: null,
    fileName: '',
  }
  formError.value = { userId: '', name: '' }
  showFormModal.value = true
}

function closeFormModal() {
  showFormModal.value = false
}

function openFilePicker() {
  fileInput.value?.click()
}

function applySelectedFile(file) {
  if (!file) return

  const allowedTypes = ['application/pdf', 'image/png', 'image/jpeg']
  if (!allowedTypes.includes(file.type)) {
    showToast('Format invalide. Utilisez PDF, PNG, JPG ou JPEG.', 'danger')
    return
  }

  if (file.size > 10 * 1024 * 1024) {
    showToast('Fichier trop volumineux (max 10 Mo).', 'danger')
    return
  }

  form.value.file = file
  form.value.fileName = file.name
}

function onFileInputChange(event) {
  const file = event.target.files?.[0]
  applySelectedFile(file)
  event.target.value = ''
}

function onFileDrop(event) {
  isDraggingFile.value = false
  const file = event.dataTransfer?.files?.[0]
  applySelectedFile(file)
}

function clearSelectedFile() {
  form.value.file = null
  form.value.fileName = ''
}

function validateForm() {
  formError.value = { userId: '', name: '' }
  if (!form.value.userId) formError.value.userId = 'Veuillez sélectionner un utilisateur.'
  if (!form.value.name.trim()) formError.value.name = 'Le titre est obligatoire.'
  return !formError.value.userId && !formError.value.name
}

async function submitForm() {
  if (!validateForm()) return

  saving.value = true
  const payload = {
    userId: form.value.userId,
    name: form.value.name.trim(),
    issuer: form.value.issuer.trim() || null,
    issueDate: form.value.issueDate || null,
    expiryDate: form.value.expiryDate || null,
    type: form.value.type,
    status: form.value.status,
    filePath: form.value.filePath.trim() || null,
    file: form.value.file,
  }

  try {
    if (editingId.value) {
      const updated = await certificatService.update(editingId.value, payload)
      const idx = certificates.value.findIndex(c => c.id === String(editingId.value))
      if (idx !== -1) certificates.value[idx] = updated
      showToast('Certificat mis à jour.', 'success')
    } else {
      const created = await certificatService.create(payload)
      certificates.value.unshift(created)
      showToast('Certificat créé.', 'success')
    }
    closeFormModal()
  } catch (error) {
    showToast(error?.response?.data?.message || 'Erreur lors de l enregistrement du certificat.', 'danger')
  } finally {
    saving.value = false
  }
}

async function updateStatus(cert, status) {
  statusSavingId.value = cert.id
  try {
    const updated = await certificatService.update(cert.id, {
      userId: cert.userId,
      name: cert.name,
      issuer: cert.issuer,
      issueDate: cert.issueDate,
      expiryDate: cert.expiryDate,
      type: cert.type,
      status,
      filePath: cert.filePath,
    })

    const idx = certificates.value.findIndex(c => c.id === cert.id)
    if (idx !== -1) {
      certificates.value[idx] = updated
    }
    showToast(`Certificat ${status === 'approved' ? 'approuvé' : 'rejeté'}.`, 'success')
  } catch (error) {
    showToast(error?.response?.data?.message || 'Erreur lors de la mise à jour du statut.', 'danger')
  } finally {
    statusSavingId.value = ''
  }
}

function askDelete(cert) {
  deleteTarget.value = cert
}

async function confirmDelete() {
  if (!deleteTarget.value) return

  deleting.value = true
  try {
    await certificatService.delete(deleteTarget.value.id)
    certificates.value = certificates.value.filter(c => c.id !== deleteTarget.value.id)
    deleteTarget.value = null
    showToast('Certificat supprimé.', 'success')
  } catch (error) {
    showToast(error?.response?.data?.message || 'Erreur lors de la suppression.', 'danger')
  } finally {
    deleting.value = false
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

.upload-dropzone {
  border: 2px dashed #ced4da;
  background: #f8f9fa;
  transition: all 0.2s ease;
}

.upload-dropzone.dragging {
  border-color: #0d6efd;
  background: #e7f1ff;
}
</style>
