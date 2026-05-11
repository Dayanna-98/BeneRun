<template>
	<div class="min-vh-100 bg-light pb-5">
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
							<Award style="width:22px;height:22px;color:#f5d020" />
						</div>
						<div>
							<div class="x-small" style="color:rgba(255,255,255,.5)">Administration</div>
							<div class="fs-5 fw-bold">Badges</div>
						</div>
					</div>
				</div>

			</div>
			<div class="px-3 pb-3 d-flex align-items-center justify-content-between gap-2">
				<div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill"
					style="background:rgba(255,255,255,.12)">
					<Award style="width:14px;height:14px;color:#f5d020" />
					<span class="small fw-semibold">{{ badges.length }} badge{{ badges.length !== 1 ? 's' : '' }}</span>
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
			<div v-if="!canEdit" class="rounded-3 p-3 d-flex align-items-center gap-3 small"
				style="background:#eff6ff;border-left:4px solid #3b82f6">
				<Award style="width:18px;height:18px;flex-shrink:0;color:#3b82f6" />
				<span class="text-muted">Vous pouvez consulter les badges. Seul un super-administrateur peut les modifier.</span>
			</div>

			<div class="input-group shadow-sm rounded-3 overflow-hidden">
				<span class="input-group-text bg-white border-0 ps-3"><Search style="width:16px;height:16px;color:#9ca3af" /></span>
				<input v-model="search" type="text" class="form-control border-0 bg-white" placeholder="Rechercher un badge..." />
			</div>

			<div v-if="loading" class="text-center py-4 text-muted">
				<div class="spinner-border spinner-border-sm me-2" role="status"></div>
				Chargement...
			</div>
			<div v-else-if="loadError" class="alert alert-danger">{{ loadError }}</div>

			<div v-else class="card border-0 shadow-sm" style="border-radius:0.5rem;overflow:hidden">
				<div class="d-flex align-items-center justify-content-between px-3 py-3"
					style="background:linear-gradient(135deg,#1a2230,#2d3a4a);border-radius:0.5rem 0.5rem 0 0">
					<span class="fw-semibold text-white d-flex align-items-center gap-2">
						<Award style="width:16px;height:16px;color:#f5d020" /> Tous les badges
					</span>
					<span class="badge rounded-pill"
						style="background:rgba(245,208,32,.2);color:#f5d020;border:1px solid rgba(245,208,32,.3)">
						{{ filtered.length }}
					</span>
				</div>
				<div v-if="filtered.length === 0" class="text-center py-5 text-muted">
					<div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
						style="width:64px;height:64px;background:#f3f4f6">
						<Award style="width:32px;height:32px;color:#d1d5db" />
					</div>
					<p class="small mb-0">Aucun badge trouvé</p>
				</div>
				<ul v-else class="list-group list-group-flush">
					<li v-for="b in filtered" :key="b.id_badge" class="list-group-item py-3 px-3">
						<div class="d-flex align-items-start justify-content-between gap-3">
							<div class="d-flex align-items-start gap-3 flex-fill">
								<div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
									style="width:42px;height:42px;background:linear-gradient(135deg,#fef3c7,#fde68a)">
									<Award style="width:20px;height:20px;color:#d97706" />
								</div>
								<div class="flex-fill">
									<div class="d-flex flex-wrap align-items-center gap-2 mb-1">
										<span class="fw-semibold">{{ b.titre_badge }}</span>
										<span class="badge rounded-pill px-2"
											style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;font-size:.7rem">
											{{ b.score_badge ?? 0 }} pts
										</span>
									</div>
									<div v-if="b.description_badge" class="small text-muted">{{ b.description_badge }}</div>
									<div v-if="b.regle_auto" class="x-small text-muted fst-italic mt-1">📋 {{ formatRegle(b.regle_auto) }}</div>
								</div>
							</div>
							<div v-if="canEdit" class="d-flex gap-2 flex-shrink-0">
								<button class="btn btn-sm rounded-pill px-3"
									style="background:#eff6ff;color:#3b82f6;border:none"
									@click="openEditModal(b)">
									<Pencil style="width:13px;height:13px" />
								</button>
								<button class="btn btn-sm rounded-pill px-3"
									style="background:#fff1f2;color:#ef4444;border:none"
									@click="askDelete(b)">
									<Trash2 style="width:13px;height:13px" />
								</button>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>

		<div v-if="showFormModal" class="modal-backdrop-custom" @click.self="closeFormModal">
			<div class="modal-dialog-custom card border-0 shadow-lg">
				<div class="d-flex align-items-center justify-content-between px-4 py-3"
					style="background:linear-gradient(135deg,#1a2230,#2d3a4a)">
					<h5 class="mb-0 text-white d-flex align-items-center gap-2">
						<div class="rounded-2 p-1" style="background:rgba(255,255,255,.15)">
							<Pencil v-if="editingId" style="width:15px;height:15px;color:#d4e645" />
							<Plus v-else style="width:15px;height:15px;color:#d4e645" />
						</div>
						{{ editingId ? 'Modifier le badge' : 'Nouveau badge' }}
					</h5>
					<button class="btn btn-link p-0" style="color:rgba(255,255,255,.6)" @click="closeFormModal">
						<X style="width:20px;height:20px" />
					</button>
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
						<label class="form-label fw-medium">Règle d'attribution automatique</label>
						<BadgeRuleBuilder v-model="form.regle_auto" />
					</div>
				</div>
				<div class="d-flex gap-2 justify-content-end px-4 py-3 border-top">
					<button class="btn btn-outline-secondary btn-sm rounded-pill px-4" @click="closeFormModal">Annuler</button>
					<button class="btn btn-sm rounded-pill px-4 fw-semibold"
						style="background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230;border:none"
						@click="submitForm" :disabled="saving">
						<span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status"></span>
						{{ editingId ? 'Enregistrer' : 'Ajouter' }}
					</button>
				</div>
			</div>
		</div>

		<div v-if="deleteTarget" class="modal-backdrop-custom" @click.self="deleteTarget = null">
			<div class="modal-dialog-custom card border-0 shadow-lg">
				<div class="px-4 py-3 d-flex align-items-center gap-2"
					style="background:linear-gradient(135deg,#450a0a,#7f1d1d)">
					<Trash2 style="width:18px;height:18px;color:#fca5a5" />
					<h5 class="mb-0 text-white">Supprimer le badge</h5>
				</div>
				<div class="card-body p-4">
					<p class="mb-0">Confirmer la suppression de <strong>{{ deleteTarget.titre_badge }}</strong> ? Cette action est irréversible.</p>
				</div>
				<div class="d-flex gap-2 justify-content-end px-4 py-3 border-top">
					<button class="btn btn-outline-secondary btn-sm rounded-pill px-4" @click="deleteTarget = null">Annuler</button>
					<button class="btn btn-danger btn-sm rounded-pill px-4" @click="confirmDelete" :disabled="deleting">
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
import BadgeRuleBuilder from '@/components/badges/BadgeRuleBuilder.vue'

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

// ─── Rule preview helper ────────────────────────────────────────────────────
const RULE_FIELD_LABELS  = { missions_completees: 'Missions complétées', evenements_participes: 'Événements', score_total: 'Score total', role_mission: 'Rôle' }
const RULE_OP_LABELS     = { '=': '=', '!=': '≠', '<': '<', '<=': '≤', '>': '>', '>=': '≥' }
const RULE_VALUE_LABELS  = { benevole: 'Bénévole', responsable: 'Responsable' }

function formatRegle(regleStr) {
	if (!regleStr) return ''
	try {
		const parsed = JSON.parse(regleStr)
		if (!Array.isArray(parsed.conditions)) return regleStr
		const sep = parsed.op === 'AND' ? ' ET ' : ' OU '
		return parsed.conditions.map(c => {
			const f = RULE_FIELD_LABELS[c.field] ?? c.field
			const o = RULE_OP_LABELS[c.op]      ?? c.op
			const v = RULE_VALUE_LABELS[c.value] ?? c.value
			return `${f} ${o} ${v}`
		}).join(sep)
	} catch { return regleStr }
}
</script>

<style scoped>
.modal-backdrop-custom {
	position: fixed;
	inset: 0;
	background: rgba(0, 0, 0, 0.5);
	z-index: 1050;
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
