<template>
	<div class="rule-builder">

		<!-- Empty state -->
		<div v-if="conditions.length === 0" class="empty-rule text-center py-3 text-muted small border rounded-3 bg-light">
			<div style="font-size:1.8rem">🏆</div>
			<div class="fw-medium">Aucune condition automatique</div>
			<div style="font-size:0.75rem">Optionnel — le badge peut être attribué manuellement</div>
		</div>

		<!-- Conditions -->
		<div v-for="(cond, i) in conditions" :key="i">

			<!-- AND / OR connector (between conditions) -->
			<div v-if="i > 0" class="d-flex align-items-center gap-2 justify-content-center my-2">
				<div class="flex-grow-1 border-top"></div>
				<button
					v-for="op in ['ET', 'OU']" :key="op"
					:class="['btn btn-sm px-3 fw-semibold', globalOp === (op === 'ET' ? 'AND' : 'OR')
						? (op === 'ET' ? 'btn-primary' : 'btn-warning text-dark')
						: (op === 'ET' ? 'btn-outline-primary' : 'btn-outline-warning')]"
					@click="globalOp = op === 'ET' ? 'AND' : 'OR'">
					{{ op }}
				</button>
				<div class="flex-grow-1 border-top"></div>
			</div>

			<!-- Condition block -->
			<div class="card border-2" :class="isConditionComplete(cond) ? 'border-success-subtle' : 'border-primary-subtle'">
				<div class="card-body p-2 d-flex flex-column gap-2">

					<!-- Step 1: Field -->
					<div>
						<div class="rule-step-label">① Champ</div>
						<div class="d-flex flex-wrap gap-1 mt-1">
							<button
								v-for="f in FIELDS" :key="f.key"
								:class="['btn btn-sm', cond.field === f.key ? 'btn-primary' : 'btn-outline-secondary']"
								@click="selectField(cond, f.key)">
								{{ f.icon }} {{ f.label }}
							</button>
						</div>
					</div>

					<!-- Step 2: Operator (after field chosen) -->
					<div v-if="cond.field">
						<div class="rule-step-label">② Opérateur</div>
						<div class="d-flex flex-wrap gap-1 mt-1">
							<button
								v-for="op in getOperators(cond.field)" :key="op.key"
								:class="['btn btn-sm fw-bold', cond.op === op.key ? 'btn-dark' : 'btn-outline-dark']"
								style="min-width: 42px"
								@click="cond.op = op.key">
								{{ op.label }}
							</button>
						</div>
					</div>

					<!-- Step 3: Value (after op chosen) -->
					<div v-if="cond.field && cond.op">
						<div class="rule-step-label">③ Valeur</div>
						<div class="mt-1">
							<input
								v-if="getFieldType(cond.field) === 'number'"
								v-model.number="cond.value"
								type="number"
								min="0"
								class="form-control form-control-sm"
								style="max-width: 120px"
								placeholder="ex: 5" />
							<div v-else-if="getFieldType(cond.field) === 'select'" class="d-flex flex-wrap gap-1">
								<button
									v-for="opt in getFieldOptions(cond.field)" :key="opt.key"
									:class="['btn btn-sm', cond.value === opt.key ? 'btn-success' : 'btn-outline-success']"
									@click="cond.value = opt.key">
									{{ opt.label }}
								</button>
							</div>
						</div>
					</div>

					<!-- Condition preview chip -->
					<div v-if="isConditionComplete(cond)" class="condition-chip">
						{{ conditionText(cond) }}
					</div>

					<!-- Remove -->
					<div class="d-flex justify-content-end">
						<button class="btn btn-link btn-sm text-danger p-0" style="font-size:0.75rem" @click="removeCondition(i)">
							<X style="width:12px;height:12px;margin-right:2px" />Supprimer cette condition
						</button>
					</div>

				</div>
			</div>
		</div>

		<!-- Add condition button -->
		<button class="btn btn-outline-primary btn-sm w-100 mt-2" @click="addCondition">
			<Plus style="width:14px;height:14px;margin-right:4px" />Ajouter une condition
		</button>

		<!-- Full rule preview -->
		<div v-if="conditions.length > 0 && isAllComplete" class="rule-preview mt-2">
			<span class="fw-semibold">Règle : </span>{{ previewText }}
		</div>
		<div v-else-if="conditions.length > 0 && !isAllComplete" class="rule-incomplete mt-2">
			⚠ Complétez toutes les conditions pour valider la règle.
		</div>

	</div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Plus, X } from 'lucide-vue-next'

const props = defineProps({
	modelValue: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue'])

// ─── Field definitions ─────────────────────────────────────────────────────
const FIELDS = [
	{ key: 'missions_completees',  label: 'Missions complétées', icon: '🎯', type: 'number' },
	{ key: 'evenements_participes', label: 'Événements participés', icon: '📅', type: 'number' },
	{ key: 'score_total',          label: 'Score total (pts)',    icon: '⭐', type: 'number' },
	{
		key: 'role_mission', label: 'Rôle en mission', icon: '👤', type: 'select',
		options: [
			{ key: 'benevole',    label: 'Bénévole' },
			{ key: 'responsable', label: 'Responsable' },
		],
	},
]

const OPERATORS_NUMBER = [
	{ key: '=',  label: '=' },
	{ key: '!=', label: '≠' },
	{ key: '<',  label: '<' },
	{ key: '<=', label: '≤' },
	{ key: '>',  label: '>' },
	{ key: '>=', label: '≥' },
]
const OPERATORS_SELECT = [
	{ key: '=',  label: '=' },
	{ key: '!=', label: '≠' },
]

function getFieldType(key)    { return FIELDS.find(f => f.key === key)?.type ?? 'number' }
function getOperators(key)    { return getFieldType(key) === 'select' ? OPERATORS_SELECT : OPERATORS_NUMBER }
function getFieldOptions(key) { return FIELDS.find(f => f.key === key)?.options ?? [] }

// ─── Label maps (for preview) ───────────────────────────────────────────────
const FIELD_LABELS = Object.fromEntries(FIELDS.map(f => [f.key, f.label]))
const OP_LABELS    = { '=': '=', '!=': '≠', '<': '<', '<=': '≤', '>': '>', '>=': '≥' }
const VALUE_LABELS = { benevole: 'Bénévole', responsable: 'Responsable' }

function conditionText(cond) {
	const f = FIELD_LABELS[cond.field] ?? cond.field
	const o = OP_LABELS[cond.op]      ?? cond.op
	const v = VALUE_LABELS[cond.value] ?? cond.value
	return `${f} ${o} ${v}`
}

function isConditionComplete(cond) {
	return cond.field && cond.op && (cond.value !== '' && cond.value !== null && cond.value !== undefined)
}

// ─── State ──────────────────────────────────────────────────────────────────
const globalOp   = ref('AND')
const conditions = ref([])

// Parse incoming value
function parse(v) {
	conditions.value = []
	globalOp.value = 'AND'
	if (!v) return
	try {
		const parsed = JSON.parse(v)
		if (Array.isArray(parsed.conditions)) {
			conditions.value = parsed.conditions.map(c => ({ ...c }))
			globalOp.value = parsed.op ?? 'AND'
		}
	} catch { /* invalid JSON — ignore */ }
}

watch(() => props.modelValue, parse, { immediate: true })

// Emit serialized rule whenever state changes
function emitUpdate() {
	if (conditions.value.length === 0) {
		emit('update:modelValue', '')
		return
	}
	emit('update:modelValue', JSON.stringify({ op: globalOp.value, conditions: conditions.value }))
}
watch([conditions, globalOp], emitUpdate, { deep: true })

// ─── Actions ─────────────────────────────────────────────────────────────────
function addCondition()    { conditions.value.push({ field: '', op: '', value: '' }) }
function removeCondition(i){ conditions.value.splice(i, 1) }
function selectField(cond, key) {
	cond.field = key
	cond.op    = ''
	cond.value = ''
}

// ─── Computed ────────────────────────────────────────────────────────────────
const isAllComplete = computed(() => conditions.value.every(isConditionComplete))
const previewText   = computed(() => {
	const sep = globalOp.value === 'AND' ? ' ET ' : ' OU '
	return conditions.value.map(conditionText).join(sep)
})
</script>

<style scoped>
.rule-step-label {
	font-size: 0.7rem;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.04em;
	color: #6c757d;
}
.condition-chip {
	display: inline-block;
	font-size: 0.78rem;
	font-weight: 600;
	background: #d1e7dd;
	color: #0f5132;
	border-radius: 20px;
	padding: 2px 10px;
	align-self: flex-start;
}
.rule-preview {
	font-size: 0.8rem;
	padding: 8px 12px;
	background: #cfe2ff;
	color: #084298;
	border-radius: 8px;
}
.rule-incomplete {
	font-size: 0.78rem;
	padding: 6px 10px;
	background: #fff3cd;
	color: #664d03;
	border-radius: 8px;
}
</style>
