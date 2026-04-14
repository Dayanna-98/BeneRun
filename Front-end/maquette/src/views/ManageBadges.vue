<template>
	<div class="min-vh-100 bg-light pb-5">
		<header class="bg-white border-bottom sticky-top">
			<div class="p-3 d-flex align-items-center justify-content-between">
				<div class="d-flex align-items-center gap-3">
					<button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
						<ArrowLeft style="width:24px;height:24px" />
					</button>
					<h1 class="fs-5 fw-semibold mb-0">Gestion des badges</h1>
				</div>
				<button v-if="canEdit" class="btn btn-primary btn-sm d-flex align-items-center gap-2" @click="openCreateModal">
					<Plus style="width:16px;height:16px" /> Ajouter
				</button>
			</div>
		</header>

		<div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:768px">
			<div v-if="!canEdit" class="alert alert-info mb-0">
				Vous pouvez consulter les badges. Seul un super-administrateur peut les modifier.
			</div>

			<div class="card">
				<div class="card-body p-3 text-center">
					<div class="fs-3 fw-bold text-warning">{{ badges.length }}</div>
					<div class="small text-muted">Badge{{ badges.length !== 1 ? 's' : '' }}</div>
				</div>
			</div>

			<div class="input-group">
				<span class="input-group-text bg-white"><Search style="width:16px;height:16px;color:#6c757d" /></span>
				<input v-model="search" type="text" class="form-control border-start-0" placeholder="Rechercher un badge..." />
			</div>

			<div v-if="loading" class="text-center py-4 text-muted">
				<div class="spinner-border spinner-border-sm me-2" role="status"></div>
				Chargement...
			</div>
			<div v-else-if="loadError" class="alert alert-danger">{{ loadError }}</div>

			<div v-else class="card">
				<div class="card-header d-flex align-items-center justify-content-between">
					<h5 class="mb-0">Tous les badges</h5>
					<span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ filtered.length }}</span>
				</div>
				<div v-if="filtered.length === 0" class="card-body text-center text-muted py-5">
					<Award style="width:40px;height:40px;opacity:.3;margin-bottom:8px" />
					<p class="mb-0">Aucun badge trouvé</p>
				</div>
				<ul v-else class="list-group list-group-flush">
					<li v-for="b in filtered" :key="b.id_badge" class="list-group-item py-3">
						<div class="d-flex align-items-start justify-content-between gap-3">
							<div class="flex-fill">
								<div class="d-flex align-items-center gap-2 mb-1">
									<Award style="width:18px;height:18px;color:#f39c12" />
									<span class="fw-semibold">{{ b.titre_badge }}</span>
									<span class="badge bg-warning text-dark">{{ b.score_badge ?? 0 }} pts</span>
								</div>
								<div v-if="b.description_badge" class="small text-muted">{{ b.description_badge }}</div>
								<div v-if="b.regle_auto" class="small text-muted fst-italic mt-1">Regle auto: {{ b.regle_auto }}</div>
							</div>
							<div v-if="canEdit" class="d-flex gap-2 flex-shrink-0">
								<button class="btn btn-outline-primary btn-sm" @click="openEditModal(b)"><Pencil style="width:14px;height:14px" /></button>
								<button class="btn btn-outline-danger btn-sm" @click="askDelete(b)"><Trash2 style="width:14px;height:14px" /></button>
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
						{{ editingId ? 'Modifier le badge' : 'Nouveau badge' }}
					</h5>
					<button class="btn btn-link p-0 text-secondary" @click="closeFormModal"><X style="width:20px;height:20px" /></button>
				</div>
				<div class="card-body d-flex flex-column gap-3">
					<div>
						<label class="form-label fw-medium">Titre <span class="text-danger">*</span></label>
						<input v-model="form.titre_badge" type="text" class="form-control" :class="formError ? 'is-invalid' : ''" />
						<div v-if="formError" class="invalid-feedback d-block">{{ formError }}</div>
					</div>
					<div>
						<label class="form-label fw-medium">Description</label>
						<textarea v-model="form.description_badge" class="form-control" rows="2"></textarea>
					</div>
					<div>
						<label class="form-label fw-medium">Score (pts)</label>
						<input v-model.number="form.score_badge" type="number" min="0" class="form-control" />
					</div>
					<div>
						<label class="form-label fw-medium">Regle automatique</label>
						<input v-model="form.regle_auto" type="text" class="form-control" />
					</div>
				</div>
				<div class="card-footer d-flex gap-2 justify-content-end">
					<button class="btn btn-outline-secondary" @click="closeFormModal">Annuler</button>
					<button class="btn btn-primary" @click="submitForm" :disabled="saving">
						<span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status"></span>
						{{ editingId ? 'Enregistrer' : 'Ajouter' }}
					</button>
				</div>
			</div>
		</div>

		<div v-if="deleteTarget" class="modal-backdrop-custom" @click.self="deleteTarget = null">
			<div class="modal-dialog-custom card shadow-lg">
				<div class="card-header"><h5 class="mb-0 text-danger">Supprimer le badge</h5></div>
				<div class="card-body">
					<p class="mb-0">Confirmer la suppression de <strong>{{ deleteTarget.titre_badge }}</strong> ?</p>
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
import { useRouter } from 'vue-router'
import { ArrowLeft, Plus, Pencil, Trash2, Search, Award, X } from 'lucide-vue-next'
import { getCurrentUser, hasPermission } from '@/utils/auth'
import badgeService from '@/services/badgeService'

const router = useRouter()
const user = getCurrentUser()
if (!user) router.push('/login')

const canEdit = computed(() => hasPermission('createBadges'))

const badges = ref([])
const loading = ref(true)
const loadError = ref('')
const search = ref('')

const showFormModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')
const form = ref({ titre_badge: '', description_badge: '', score_badge: 0, regle_auto: '' })

const deleteTarget = ref(null)
const deleting = ref(false)
const toast = ref({ show: false, message: '', type: 'success' })

const filtered = computed(() => {
	const q = search.value.trim().toLowerCase()
	if (!q) return badges.value
	return badges.value.filter(b => (b.titre_badge || '').toLowerCase().includes(q) || (b.description_badge || '').toLowerCase().includes(q))
})

onMounted(fetchBadges)

async function fetchBadges() {
	loading.value = true
	loadError.value = ''
	try {
		badges.value = await badgeService.getAll()
	} catch {
		loadError.value = 'Impossible de charger les badges.'
	} finally {
		loading.value = false
	}
}

function openCreateModal() {
	editingId.value = null
	formError.value = ''
	form.value = { titre_badge: '', description_badge: '', score_badge: 0, regle_auto: '' }
	showFormModal.value = true
}

function openEditModal(badge) {
	editingId.value = badge.id_badge
	formError.value = ''
	form.value = {
		titre_badge: badge.titre_badge || '',
		description_badge: badge.description_badge || '',
		score_badge: badge.score_badge ?? 0,
		regle_auto: badge.regle_auto || '',
	}
	showFormModal.value = true
}

function closeFormModal() {
	showFormModal.value = false
}

async function submitForm() {
	if (!form.value.titre_badge.trim()) {
		formError.value = 'Le titre est obligatoire.'
		return
	}
	saving.value = true
	try {
		if (editingId.value) {
			const res = await badgeService.update(editingId.value, {
				titre_badge: form.value.titre_badge.trim(),
				description_badge: form.value.description_badge.trim() || null,
				score_badge: form.value.score_badge ?? 0,
				regle_auto: form.value.regle_auto.trim() || null,
			})
			const updated = res.badge ?? res
			const idx = badges.value.findIndex(b => b.id_badge === editingId.value)
			if (idx !== -1) badges.value[idx] = updated
			showToast('Badge mis a jour.', 'success')
		} else {
			const res = await badgeService.create({
				titre_badge: form.value.titre_badge.trim(),
				description_badge: form.value.description_badge.trim() || null,
				score_badge: form.value.score_badge ?? 0,
				regle_auto: form.value.regle_auto.trim() || null,
			})
			badges.value.push(res.badge ?? res)
			showToast('Badge ajoute.', 'success')
		}
		closeFormModal()
	} catch {
		showToast('Erreur lors de l enregistrement.', 'danger')
	} finally {
		saving.value = false
	}
}

function askDelete(badge) {
	deleteTarget.value = badge
}

async function confirmDelete() {
	deleting.value = true
	try {
		await badgeService.delete(deleteTarget.value.id_badge)
		badges.value = badges.value.filter(b => b.id_badge !== deleteTarget.value.id_badge)
		deleteTarget.value = null
		showToast('Badge supprime.', 'success')
	} catch {
		showToast('Erreur lors de la suppression.', 'danger')
	} finally {
		deleting.value = false
	}
}

function showToast(message, type = 'success') {
	toast.value = { show: true, message, type }
	setTimeout(() => { toast.value.show = false }, 3000)
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
	max-width: 480px;
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
</style>
