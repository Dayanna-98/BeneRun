<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Gestion des Compétences</h1>
        </div>
        <button v-if="canEdit" class="btn btn-primary btn-sm d-flex align-items-center gap-2" @click="openCreateModal">
          <Plus style="width:16px;height:16px" /> Ajouter
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:768px">

      <!-- Alerte permission -->
      <div v-if="!canEdit" class="alert alert-info d-flex align-items-center gap-2 mb-0">
        <Info style="width:18px;height:18px;flex-shrink:0" />
        <span class="small">Vous pouvez consulter les compétences. Seul un super-administrateur peut les créer, modifier ou supprimer.</span>
      </div>

      <!-- Stats -->
      <div class="card">
        <div class="card-body p-3 text-center">
          <div class="fs-3 fw-bold text-primary">{{ competences.length }}</div>
          <div class="small text-muted">Compétence{{ competences.length !== 1 ? 's' : '' }} disponible{{ competences.length !== 1 ? 's' : '' }}</div>
        </div>
      </div>

      <!-- Barre de recherche -->
      <div class="input-group">
        <span class="input-group-text bg-white">
          <Search style="width:16px;height:16px;color:#6c757d" />
        </span>
        <input v-model="search" type="text" class="form-control border-start-0" placeholder="Rechercher une compétence..." />
      </div>

      <!-- États de chargement / erreur -->
      <div v-if="loading" class="text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
        Chargement...
      </div>
      <div v-else-if="loadError" class="alert alert-danger">
        {{ loadError }}
      </div>

      <!-- Liste des compétences -->
      <div v-else class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Toutes les compétences</h5>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ filtered.length }}</span>
        </div>

        <div v-if="filtered.length === 0" class="card-body text-center text-muted py-5">
          <Award style="width:40px;height:40px;opacity:.3;margin-bottom:8px" />
          <p class="mb-0">Aucune compétence trouvée</p>
        </div>

        <ul v-else class="list-group list-group-flush">
          <li v-for="c in filtered" :key="c.id_competence"
            class="list-group-item d-flex align-items-center justify-content-between gap-3 py-3">

            <!-- Nom ou champ d'édition inline -->
            <div class="flex-fill">
              <template v-if="editingId === c.id_competence">
                <div class="d-flex align-items-center gap-2">
                  <input
                    v-model="editName"
                    class="form-control form-control-sm"
                    :class="editError ? 'is-invalid' : ''"
                    @keyup.enter="confirmEdit(c.id_competence)"
                    @keyup.escape="cancelEdit"
                    ref="editInput"
                  />
                  <button class="btn btn-success btn-sm" @click="confirmEdit(c.id_competence)" :disabled="editLoading">
                    <Check style="width:14px;height:14px" />
                  </button>
                  <button class="btn btn-outline-secondary btn-sm" @click="cancelEdit">
                    <X style="width:14px;height:14px" />
                  </button>
                </div>
                <div v-if="editError" class="text-danger x-small mt-1">{{ editError }}</div>
              </template>
              <template v-else>
                <span class="fw-medium">{{ c.nom_competence }}</span>
              </template>
            </div>

            <!-- Actions (superadmin seulement) -->
            <div v-if="canEdit" class="d-flex gap-2 flex-shrink-0">
              <button class="btn btn-outline-primary btn-sm" @click="startEdit(c)" title="Modifier">
                <Pencil style="width:14px;height:14px" />
              </button>
              <button class="btn btn-outline-danger btn-sm" @click="askDelete(c)" title="Supprimer">
                <Trash2 style="width:14px;height:14px" />
              </button>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Modal : Créer une compétence -->
    <div v-if="showCreateModal" class="modal-backdrop-custom" @click.self="closeCreateModal">
      <div class="modal-dialog-custom card shadow-lg">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0 d-flex align-items-center gap-2">
            <Plus style="width:18px;height:18px" /> Nouvelle compétence
          </h5>
          <button class="btn btn-link p-0 text-secondary" @click="closeCreateModal">
            <X style="width:20px;height:20px" />
          </button>
        </div>
        <div class="card-body d-flex flex-column gap-3">
          <div>
            <label class="form-label fw-medium">Nom de la compétence <span class="text-danger">*</span></label>
            <input
              v-model="newName"
              type="text"
              class="form-control"
              :class="createError ? 'is-invalid' : ''"
              placeholder="Ex: Premiers secours, Logistique..."
              @keyup.enter="submitCreate"
              ref="createInput"
            />
            <div v-if="createError" class="invalid-feedback d-block">{{ createError }}</div>
          </div>
        </div>
        <div class="card-footer d-flex gap-2 justify-content-end">
          <button class="btn btn-outline-secondary" @click="closeCreateModal">Annuler</button>
          <button class="btn btn-primary d-flex align-items-center gap-2" @click="submitCreate" :disabled="createLoading">
            <span v-if="createLoading" class="spinner-border spinner-border-sm" role="status"></span>
            Ajouter
          </button>
        </div>
      </div>
    </div>

    <!-- Modal : Confirmer suppression -->
    <div v-if="deleteTarget" class="modal-backdrop-custom" @click.self="deleteTarget = null">
      <div class="modal-dialog-custom card shadow-lg">
        <div class="card-header d-flex align-items-center gap-2">
          <Trash2 style="width:18px;height:18px;color:#dc3545" />
          <h5 class="mb-0 text-danger">Supprimer la compétence</h5>
        </div>
        <div class="card-body">
          <p class="mb-0">
            Êtes-vous sûr de vouloir supprimer la compétence
            <strong>« {{ deleteTarget.nom_competence }} »</strong> ?
            Cette action est irréversible.
          </p>
        </div>
        <div class="card-footer d-flex gap-2 justify-content-end">
          <button class="btn btn-outline-secondary" @click="deleteTarget = null">Annuler</button>
          <button class="btn btn-danger d-flex align-items-center gap-2" @click="confirmDelete" :disabled="deleteLoading">
            <span v-if="deleteLoading" class="spinner-border spinner-border-sm" role="status"></span>
            Supprimer
          </button>
        </div>
      </div>
    </div>

    <!-- Toast notification -->
    <div v-if="toast.show" :class="`toast-custom alert alert-${toast.type} shadow`">
      {{ toast.message }}
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import {
  ArrowLeft, Plus, Pencil, Trash2, Check, X, Search, Award, Info,
} from 'lucide-vue-next'
import { getCurrentUser, hasPermission } from '@/utils/auth'
import competenceService from '@/services/competenceService'

const router = useRouter()
const user = getCurrentUser()
if (!user) router.push('/login')

// Seul le superadmin (permission createSkills) peut modifier
const canEdit = computed(() => hasPermission('createSkills'))

// ─── État ───────────────────────────────────────────────
const competences = ref([])
const loading = ref(true)
const loadError = ref(null)
const search = ref('')

// Création
const showCreateModal = ref(false)
const newName = ref('')
const createError = ref('')
const createLoading = ref(false)
const createInput = ref(null)

// Édition inline
const editingId = ref(null)
const editName = ref('')
const editError = ref('')
const editLoading = ref(false)
const editInput = ref(null)

// Suppression
const deleteTarget = ref(null)
const deleteLoading = ref(false)

// Toast
const toast = ref({ show: false, message: '', type: 'success' })

// ─── Chargement ─────────────────────────────────────────
onMounted(async () => {
  await fetchCompetences()
})

async function fetchCompetences() {
  loading.value = true
  loadError.value = null
  try {
    competences.value = await competenceService.getAll()
  } catch {
    loadError.value = 'Impossible de charger les compétences. Vérifiez votre connexion au serveur.'
  } finally {
    loading.value = false
  }
}

// ─── Filtrage ────────────────────────────────────────────
const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return competences.value
  return competences.value.filter(c =>
    c.nom_competence.toLowerCase().includes(q)
  )
})

// ─── Création ────────────────────────────────────────────
function openCreateModal() {
  newName.value = ''
  createError.value = ''
  showCreateModal.value = true
  nextTick(() => createInput.value?.focus())
}

function closeCreateModal() {
  showCreateModal.value = false
}

async function submitCreate() {
  const name = newName.value.trim()
  if (!name) {
    createError.value = 'Le nom de la compétence est requis.'
    return
  }
  createLoading.value = true
  createError.value = ''
  try {
    const created = await competenceService.create(name)
    competences.value.push(created.competence)
    closeCreateModal()
    showToast('Compétence ajoutée avec succès.', 'success')
  } catch (err) {
    if (err.response?.status === 422) {
      const errors = err.response.data?.errors?.nom_competence
      createError.value = errors?.[0] ?? 'Cette compétence existe déjà.'
    } else {
      createError.value = 'Une erreur est survenue. Réessayez.'
    }
  } finally {
    createLoading.value = false
  }
}

// ─── Édition ─────────────────────────────────────────────
function startEdit(c) {
  editingId.value = c.id_competence
  editName.value = c.nom_competence
  editError.value = ''
  nextTick(() => editInput.value?.focus())
}

function cancelEdit() {
  editingId.value = null
  editError.value = ''
}

async function confirmEdit(id) {
  const name = editName.value.trim()
  if (!name) {
    editError.value = 'Le nom ne peut pas être vide.'
    return
  }
  editLoading.value = true
  editError.value = ''
  try {
    const updated = await competenceService.update(id, name)
    const idx = competences.value.findIndex(c => c.id_competence === id)
    if (idx !== -1) competences.value[idx] = updated.competence
    editingId.value = null
    showToast('Compétence mise à jour.', 'success')
  } catch (err) {
    if (err.response?.status === 422) {
      const errors = err.response.data?.errors?.nom_competence
      editError.value = errors?.[0] ?? 'Cette compétence existe déjà.'
    } else {
      editError.value = 'Erreur lors de la mise à jour. Réessayez.'
    }
  } finally {
    editLoading.value = false
  }
}

// ─── Suppression ─────────────────────────────────────────
function askDelete(c) {
  deleteTarget.value = c
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await competenceService.delete(deleteTarget.value.id_competence)
    competences.value = competences.value.filter(
      c => c.id_competence !== deleteTarget.value.id_competence
    )
    showToast(`Compétence « ${deleteTarget.value.nom_competence} » supprimée.`, 'danger')
    deleteTarget.value = null
  } catch {
    showToast('Impossible de supprimer la compétence.', 'danger')
    deleteTarget.value = null
  } finally {
    deleteLoading.value = false
  }
}

// ─── Toast ───────────────────────────────────────────────
function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}
</script>

<style scoped>
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}

.modal-dialog-custom {
  width: 100%;
  max-width: 460px;
  border-radius: 0.75rem;
  overflow: hidden;
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
