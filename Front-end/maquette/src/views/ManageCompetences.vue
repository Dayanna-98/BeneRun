<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Hero Header -->
    <header class="text-white sticky-top overflow-hidden"
      style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-white" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-2 d-flex align-items-center justify-content-center"
              style="background:rgba(255,255,255,.15);width:40px;height:40px">
              <Award style="width:22px;height:22px;color:#c084fc" />
            </div>
            <div>
              <div class="x-small" style="color:rgba(255,255,255,.5)">Administration</div>
              <div class="fs-5 fw-bold">Compétences</div>
            </div>
          </div>
        </div>

      </div>
      <div class="px-3 pb-3 d-flex align-items-center justify-content-between gap-2">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill"
          style="background:rgba(192,132,252,.2);border:1px solid rgba(192,132,252,.3)">
          <Award style="width:14px;height:14px;color:#c084fc" />
          <span class="small fw-semibold" style="color:#e9d5ff">{{ competences.length }} compétence{{ competences.length !== 1 ? 's' : '' }}</span>
        </div>
        <button v-if="canEdit"
          class="btn btn-sm fw-semibold d-flex align-items-center gap-2 px-3 rounded-pill flex-shrink-0"
          style="background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230;border:none"
          @click="openCreateModal">
          <Plus style="width:14px;height:14px" /> Ajouter
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:768px">

      <!-- Alerte permission -->
      <div v-if="!canEdit" class="rounded-3 p-3 d-flex align-items-center gap-3 small"
        style="background:#faf5ff;border-left:4px solid #a855f7">
        <Info style="width:18px;height:18px;flex-shrink:0;color:#a855f7" />
        <span class="text-muted">Vous pouvez consulter les compétences. Seul un super-administrateur peut les créer, modifier ou supprimer.</span>
      </div>

      <!-- Stats -->
      <div class="card border-0 text-white shadow-sm" style="background:linear-gradient(135deg,#7c3aed,#a855f7)">
        <div class="card-body p-3 d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center"
            style="width:48px;height:48px;background:rgba(255,255,255,.2)">
            <Award style="width:24px;height:24px" />
          </div>
          <div>
            <div class="fs-3 fw-bold">{{ competences.length }}</div>
            <div class="x-small opacity-90">Compétence{{ competences.length !== 1 ? 's' : '' }} disponible{{ competences.length !== 1 ? 's' : '' }}</div>
          </div>
        </div>
      </div>

      <!-- Barre de recherche -->
      <div class="input-group shadow-sm rounded-3 overflow-hidden">
        <span class="input-group-text bg-white border-0 ps-3">
          <Search style="width:16px;height:16px;color:#9ca3af" />
        </span>
        <input v-model="search" type="text" class="form-control border-0 bg-white" placeholder="Rechercher une compétence..." />
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
      <div v-else class="card border-0 shadow-sm" style="border-radius:0.5rem;overflow:hidden">
        <div class="d-flex align-items-center justify-content-between px-3 py-3"
          style="background:linear-gradient(135deg,#1a2230,#2d3a4a)">
          <span class="fw-semibold text-white d-flex align-items-center gap-2"
            style="margin-top:0">
            <Award style="width:16px;height:16px;color:#c084fc" /> Toutes les compétences
          </span>
          <span class="badge rounded-pill"
            style="background:rgba(192,132,252,.2);color:#c084fc;border:1px solid rgba(192,132,252,.3)">
            {{ filtered.length }}
          </span>
        </div>

        <div v-if="filtered.length === 0" class="text-center py-5 text-muted">
          <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
            style="width:64px;height:64px;background:#f3f4f6">
            <Award style="width:32px;height:32px;color:#d1d5db" />
          </div>
          <p class="small mb-0">Aucune compétence trouvée</p>
        </div>

        <ul v-else class="list-group list-group-flush">
          <li v-for="c in filtered" :key="c.id_competence"
            class="list-group-item d-flex align-items-center justify-content-between gap-3 py-3 px-3">

            <!-- Nom ou champ d'édition inline -->
            <div class="d-flex align-items-center gap-3 flex-fill">
              <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                style="width:36px;height:36px;background:linear-gradient(135deg,#f3e8ff,#e9d5ff)">
                <Award style="width:16px;height:16px;color:#9333ea" />
              </div>
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
                    <button class="btn btn-success btn-sm rounded-pill px-3" @click="confirmEdit(c.id_competence)" :disabled="editLoading">
                      <Check style="width:14px;height:14px" />
                    </button>
                    <button class="btn btn-outline-secondary btn-sm rounded-pill" @click="cancelEdit">
                      <X style="width:14px;height:14px" />
                    </button>
                  </div>
                  <div v-if="editError" class="text-danger x-small mt-1">{{ editError }}</div>
                  <div class="mt-2">
                    <div class="x-small text-muted mb-1">Types de mission suggeres</div>
                    <div class="d-flex flex-wrap gap-2">
                      <button
                        v-for="type in missionTypeOptions"
                        :key="`edit-type-${c.id_competence}-${type.value}`"
                        type="button"
                        class="btn btn-sm rounded-pill"
                        :class="editMissionTypes.includes(type.value) ? 'btn-primary' : 'btn-outline-secondary'"
                        @click="toggleTypeSelection(editMissionTypes, type.value)">
                        {{ type.label }}
                      </button>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <span class="fw-medium">{{ c.nom_competence }}</span>
                  <div v-if="Array.isArray(c.types_mission_suggeres) && c.types_mission_suggeres.length" class="d-flex flex-wrap gap-1 mt-1">
                    <span
                      v-for="missionType in c.types_mission_suggeres"
                      :key="`tag-${c.id_competence}-${missionType}`"
                      class="badge rounded-pill text-bg-light border">
                      {{ missionType }}
                    </span>
                  </div>
                </template>
              </div>
            </div>

            <!-- Actions (superadmin seulement) - masquées pendant l'édition inline -->
            <div v-if="canEdit && editingId !== c.id_competence" class="d-flex gap-2 flex-shrink-0">
              <button class="btn btn-sm rounded-pill px-3"
                style="background:#eff6ff;color:#3b82f6;border:none"
                @click="startEdit(c)" title="Modifier">
                <Pencil style="width:13px;height:13px" />
              </button>
              <button class="btn btn-sm rounded-pill px-3"
                style="background:#fff1f2;color:#ef4444;border:none"
                @click="askDelete(c)" title="Supprimer">
                <Trash2 style="width:13px;height:13px" />
              </button>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Modal : Créer une compétence -->
    <div v-if="showCreateModal" class="modal-backdrop-custom" @click.self="closeCreateModal">
      <div class="modal-dialog-custom card border-0 shadow-lg">
        <div class="d-flex align-items-center justify-content-between px-4 py-3"
          style="background:linear-gradient(135deg,#1a2230,#2d3a4a)">
          <h5 class="mb-0 text-white d-flex align-items-center gap-2">
            <div class="rounded-2 p-1" style="background:rgba(255,255,255,.15)">
              <Plus style="width:15px;height:15px;color:#d4e645" />
            </div>
            Nouvelle compétence
          </h5>
          <button class="btn btn-link p-0" style="color:rgba(255,255,255,.6)" @click="closeCreateModal">
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
          <div>
            <label class="form-label fw-medium">Types de mission suggérés</label>
            <div class="d-flex flex-wrap gap-2">
              <button
                v-for="type in missionTypeOptions"
                :key="`create-type-${type.value}`"
                type="button"
                class="btn btn-sm rounded-pill"
                :class="newMissionTypes.includes(type.value) ? 'btn-primary' : 'btn-outline-secondary'"
                @click="toggleTypeSelection(newMissionTypes, type.value)">
                {{ type.label }}
              </button>
            </div>
            <div class="form-text">Ces types serviront de suggestions automatiques dans le formulaire mission.</div>
          </div>
        </div>
        <div class="d-flex gap-2 justify-content-end px-4 py-3 border-top">
          <button class="btn btn-outline-secondary btn-sm rounded-pill px-4" @click="closeCreateModal">Annuler</button>
          <button class="btn btn-sm rounded-pill px-4 fw-semibold d-flex align-items-center gap-2"
            style="background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230;border:none"
            @click="submitCreate" :disabled="createLoading">
            <span v-if="createLoading" class="spinner-border spinner-border-sm" role="status"></span>
            Ajouter
          </button>
        </div>
      </div>
    </div>

    <!-- Modal : Confirmer suppression -->
    <div v-if="deleteTarget" class="modal-backdrop-custom" @click.self="deleteTarget = null">
      <div class="modal-dialog-custom card border-0 shadow-lg">
        <div class="px-4 py-3 d-flex align-items-center gap-2"
          style="background:linear-gradient(135deg,#450a0a,#7f1d1d)">
          <Trash2 style="width:18px;height:18px;color:#fca5a5" />
          <h5 class="mb-0 text-white">Supprimer la compétence</h5>
        </div>
        <div class="card-body">
          <p class="mb-0">
            Êtes-vous sûr de vouloir supprimer la compétence
            <strong>« {{ deleteTarget.nom_competence }} »</strong> ?
            Cette action est irréversible.
          </p>
        </div>
        <div class="d-flex gap-2 justify-content-end px-4 py-3 border-top">
          <button class="btn btn-outline-secondary btn-sm rounded-pill px-4" @click="deleteTarget = null">Annuler</button>
          <button class="btn btn-danger btn-sm rounded-pill px-4 d-flex align-items-center gap-2" @click="confirmDelete" :disabled="deleteLoading">
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
const newMissionTypes = ref([])

// Édition inline
const editingId = ref(null)
const editName = ref('')
const editError = ref('')
const editLoading = ref(false)
const editInput = ref(null)
const editMissionTypes = ref([])

// Suppression
const deleteTarget = ref(null)
const deleteLoading = ref(false)

// Toast
const toast = ref({ show: false, message: '', type: 'success' })

const missionTypeOptions = [
  { value: 'secours', label: 'Secours' },
  { value: 'logistique', label: 'Logistique' },
  { value: 'accueil', label: 'Accueil' },
  { value: 'technique', label: 'Technique' },
  { value: 'animation', label: 'Animation' },
  { value: 'autre', label: 'Autre' },
]

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
  newMissionTypes.value = []
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
    const created = await competenceService.create({
      nom_competence: name,
      types_mission_suggeres: newMissionTypes.value,
    })
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
  editMissionTypes.value = Array.isArray(c.types_mission_suggeres) ? [...c.types_mission_suggeres] : []
  editError.value = ''
  nextTick(() => {
    const input = Array.isArray(editInput.value) ? editInput.value[0] : editInput.value
    if (input && typeof input.focus === 'function') {
      input.focus()
    }
  })
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
    const updated = await competenceService.update(id, {
      nom_competence: name,
      types_mission_suggeres: editMissionTypes.value,
    })
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

function toggleTypeSelection(list, missionType) {
  if (!Array.isArray(list)) return

  const idx = list.indexOf(missionType)
  if (idx === -1) {
    list.push(missionType)
  } else {
    list.splice(idx, 1)
  }
}
</script>

<style scoped>
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  z-index: 1050;
  overflow-y: auto;
  padding: max(0.75rem, env(safe-area-inset-top, 0px)) 1rem calc(0.75rem + env(safe-area-inset-bottom, 0px));
}

.modal-dialog-custom {
  width: 100%;
  max-width: 460px;
  max-height: calc(100dvh - 1.5rem - env(safe-area-inset-bottom, 0px));
  display: flex;
  flex-direction: column;
  border-radius: 0.75rem;
  overflow: hidden;
}

.modal-dialog-custom .card-body {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
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
