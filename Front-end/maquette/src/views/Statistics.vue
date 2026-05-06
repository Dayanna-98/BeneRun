<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Statistiques</h1>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
          @click="showExportDialog = true">
          <Download style="width:16px;height:16px" /> Exporter
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:1024px">

      <!-- KPI Cards -->
      <div v-if="statsLoading" class="d-flex justify-content-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
      </div>
      <div v-else class="row g-3">
        <div v-for="kpi in kpis" :key="kpi.label" class="col-6 col-md-4">
          <div class="card border-0 text-white h-100" :style="`background:${kpi.gradient}`">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <component :is="kpi.icon" style="width:32px;height:32px;opacity:.8" />
              <div>
                <div class="fs-4 fw-bold">{{ kpi.value }}</div>
                <div class="x-small opacity-90">{{ kpi.label }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Répartition par rôle -->
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <BarChart3 style="width:20px;height:20px" />
          <h5 class="mb-0">Répartition des utilisateurs par rôle</h5>
        </div>
        <div class="card-body d-flex flex-column gap-3">
          <div v-for="r in roleStats" :key="r.role">
            <div class="d-flex justify-content-between small fw-medium mb-1">
              <span>{{ r.label }}</span>
              <span class="text-muted">{{ r.count }} ({{ r.pct }}%)</span>
            </div>
            <div class="progress" style="height:12px">
              <div class="progress-bar" :style="`width:${r.pct}%;background:${r.color}`"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Missions par événement -->
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Missions par événement</h5></div>
        <div class="card-body d-flex flex-column gap-2">
          <div v-if="statsLoading" class="text-muted small py-2">Chargement...</div>
          <div v-for="event in missionsByEvent" :key="event.id"
            class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
            <div class="flex-fill">
              <div class="small fw-medium">{{ event.name }}</div>
              <div class="x-small text-muted">{{ event.currentVolunteers }}/{{ event.totalNeeded }} bénévoles</div>
            </div>
            <div class="text-end">
              <div class="fs-5 fw-bold text-primary">{{ event.missionsCount }}</div>
              <div class="x-small text-muted">missions</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions rapides -->
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Actions rapides</h5></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-6">
              <button class="btn btn-outline-secondary w-100 py-3 d-flex flex-column align-items-center gap-2"
                @click="showExportDialog = true">
                <Download style="width:24px;height:24px" />
                <span class="small">Exporter personnalisé</span>
              </button>
            </div>
            <div class="col-6">
              <button class="btn btn-outline-secondary w-100 py-3 d-flex flex-column align-items-center gap-2"
                @click="router.push('/manage-users')">
                <Users style="width:24px;height:24px" />
                <span class="small">Gérer les utilisateurs</span>
              </button>
            </div>
            <div class="col-6">
              <button class="btn btn-outline-secondary w-100 py-3 d-flex flex-column align-items-center gap-2"
                @click="router.push('/manage-events')">
                <Calendar style="width:24px;height:24px" />
                <span class="small">Gérer les événements</span>
              </button>
            </div>
            <div class="col-6">
              <button class="btn btn-outline-secondary w-100 py-3 d-flex flex-column align-items-center gap-2"
                @click="router.push('/manage-missions')">
                <Briefcase style="width:24px;height:24px" />
                <span class="small">Gérer les missions</span>
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Modal Export -->
    <Teleport to="body">
      <div v-if="showExportDialog" class="modal d-block" tabindex="-1"
        style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
          <div class="modal-content">

            <div class="modal-header">
              <div>
                <h5 class="modal-title">Exporter les statistiques</h5>
                <p class="small text-muted mb-0">Sélectionnez les données à exporter et le format souhaité</p>
              </div>
              <button class="btn-close" @click="showExportDialog = false"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-4">

              <!-- Format -->
              <div>
                <h6 class="fw-medium mb-3">Format d'export</h6>
                <div class="row g-3">
                  <div class="col-6">
                    <button
                      :class="['btn w-100 py-3 border-2 d-flex flex-column align-items-center gap-2',
                        exportFormat === 'pdf' ? 'btn-primary' : 'btn-outline-secondary']"
                      @click="exportFormat = 'pdf'">
                      <FileText style="width:32px;height:32px;color:#dc2626" />
                      <div class="fw-medium small">PDF</div>
                      <div class="x-small text-muted">Document imprimable</div>
                    </button>
                  </div>
                  <div class="col-6">
                    <button
                      :class="['btn w-100 py-3 border-2 d-flex flex-column align-items-center gap-2',
                        exportFormat === 'excel' ? 'btn-primary' : 'btn-outline-secondary']"
                      @click="exportFormat = 'excel'">
                      <FileSpreadsheet style="width:32px;height:32px;color:#16a34a" />
                      <div class="fw-medium small">Excel</div>
                      <div class="x-small text-muted">Tableur éditable</div>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Types de données -->
              <div>
                <h6 class="fw-medium mb-3">Types de données</h6>
                <div class="bg-light rounded p-3 d-flex flex-column gap-2">
                  <div v-for="dt in dataTypeOptions" :key="dt.key"
                    class="form-check d-flex align-items-center gap-2 mb-0">
                    <input class="form-check-input mt-0" type="checkbox"
                      :id="`dt-${dt.key}`" v-model="selectedDataTypes[dt.key]" />
                    <label :for="`dt-${dt.key}`" class="form-check-label d-flex align-items-center gap-2 small">
                      <component :is="dt.icon" style="width:16px;height:16px" />
                      {{ dt.label }}
                    </label>
                  </div>
                </div>
              </div>

              <!-- Événements -->
              <div v-if="selectedDataTypes.events">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <h6 class="fw-medium mb-0">Événements à inclure</h6>
                  <div class="d-flex gap-2">
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" @click="selectAllEvents">Tous</button>
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" @click="deselectAllEvents">Aucun</button>
                  </div>
                </div>
                <div class="bg-light rounded p-3 d-flex flex-column gap-2" style="max-height:160px;overflow-y:auto">
                  <div v-for="event in exportEvents" :key="event.id" class="form-check mb-0">
                    <input class="form-check-input" type="checkbox"
                      :id="`ev-${event.id}`"
                      :checked="selectedEvents.includes(event.id)"
                      @change="toggleEvent(event.id)" />
                    <label :for="`ev-${event.id}`" class="form-check-label small">{{ event.name }}</label>
                  </div>
                </div>
                <p class="x-small text-muted mt-1 mb-0">{{ selectedEvents.length }} événement(s) sélectionné(s)</p>
              </div>

              <!-- Missions -->
              <div v-if="selectedDataTypes.missions">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <h6 class="fw-medium mb-0">Missions à inclure</h6>
                  <div class="d-flex gap-2">
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" @click="selectAllMissions">Toutes</button>
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" @click="deselectAllMissions">Aucune</button>
                  </div>
                </div>
                <div class="bg-light rounded p-3 d-flex flex-column gap-2" style="max-height:160px;overflow-y:auto">
                  <div v-for="mission in exportMissions" :key="mission.id" class="form-check mb-0">
                    <input class="form-check-input" type="checkbox"
                      :id="`ms-${mission.id}`"
                      :checked="selectedMissions.includes(mission.id)"
                      @change="toggleMission(mission.id)" />
                    <label :for="`ms-${mission.id}`" class="form-check-label small">
                      {{ mission.name }} - {{ mission.eventName }}
                    </label>
                  </div>
                </div>
                <p class="x-small text-muted mt-1 mb-0">{{ selectedMissions.length }} mission(s) sélectionnée(s)</p>
              </div>

            </div>

            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="showExportDialog = false">Annuler</button>
              <button class="btn btn-primary d-flex align-items-center gap-2" @click="exportStatistics">
                <Download style="width:16px;height:16px" />
                Exporter en {{ exportFormat.toUpperCase() }}
              </button>
            </div>

          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  ArrowLeft, Download, TrendingUp, Users, Calendar, Award,
  Briefcase, BarChart3, FileText, FileSpreadsheet
} from 'lucide-vue-next'
import { getCurrentUser, isRole } from '@/utils/auth'
import api from '@/services/api'
import { eventService } from '@/services/eventService'
import { missionService } from '@/services/missionService'

const router = useRouter()

const user = getCurrentUser()
if (!user || !isRole(['superadmin', 'admin'])) {
  router.push('/')
}

// ── State ─────────────────────────────────────────────────────────────────────
const statsLoading  = ref(false)
const rawKpis       = ref(null)
const rawRoleStats  = ref({})
const missionsByEvent = ref([])

// Pour l'export : listes complètes
const exportEvents   = ref([])
const exportMissions = ref([])

onMounted(async () => {
  statsLoading.value = true
  try {
    const [statsRes, eventsRes, missionsRes] = await Promise.all([
      api.get('/stats'),
      eventService.getAll(),
      missionService.getAll(),
    ])

    rawKpis.value        = statsRes.data.kpis
    rawRoleStats.value   = statsRes.data.roleStats || {}
    missionsByEvent.value = statsRes.data.missionsByEvent || []

    exportEvents.value   = eventsRes
    exportMissions.value = missionsRes

    selectedEvents.value   = exportEvents.value.map(e => e.id)
    selectedMissions.value = exportMissions.value.map(m => m.id)
  } catch (e) {
    console.error('Erreur chargement statistiques:', e)
  } finally {
    statsLoading.value = false
  }
})

// ── KPIs ──────────────────────────────────────────────────────────────────────
const kpis = computed(() => {
  const k = rawKpis.value
  if (!k) return []
  return [
    { label: 'Utilisateurs',       value: k.totalUsers,             icon: Users,      gradient: 'linear-gradient(135deg,#3b82f6,#2563eb)' },
    { label: 'Événements',         value: k.totalEvents,            icon: Calendar,   gradient: 'linear-gradient(135deg,#22c55e,#16a34a)' },
    { label: 'Missions',           value: k.totalMissions,          icon: Briefcase,  gradient: 'linear-gradient(135deg,#a855f7,#7c3aed)' },
    { label: 'Bénévoles',          value: k.volunteerCount,         icon: Users,      gradient: 'linear-gradient(135deg,#f97316,#ea580c)' },
    { label: 'Badges disponibles', value: k.totalBadges,            icon: Award,      gradient: 'linear-gradient(135deg,#eab308,#ca8a04)' },
    { label: 'Taux de complétion', value: `${k.completionRate}%`,   icon: TrendingUp, gradient: 'linear-gradient(135deg,#ec4899,#db2777)' },
  ]
})

// ── Rôles ─────────────────────────────────────────────────────────────────────
const ROLE_META = [
  { key: 'bénévole',    label: 'Bénévoles',        color: '#3b82f6' },
  { key: 'responsable', label: 'Resp. de mission',  color: '#22c55e' },
  { key: 'admin',       label: 'Administrateurs',   color: '#a855f7' },
  { key: 'superadmin',  label: 'Super-admins',      color: '#ef4444' },
]

const roleStats = computed(() => {
  const rs = rawRoleStats.value
  const total = Object.values(rs).reduce((s, v) => s + Number(v), 0) || 1
  return ROLE_META.map(r => {
    const count = Number(rs[r.key] || 0)
    return { ...r, count, pct: Math.round((count / total) * 100) }
  })
})

// ── Export dialog ─────────────────────────────────────────────────────────────
const showExportDialog  = ref(false)
const exportFormat      = ref('pdf')

const selectedDataTypes = ref({ users: true, events: true, missions: true, badges: true })
const selectedEvents    = ref([])
const selectedMissions  = ref([])

const dataTypeOptions = [
  { key: 'users',    label: 'Données utilisateurs', icon: Users     },
  { key: 'events',   label: 'Données événements',   icon: Calendar  },
  { key: 'missions', label: 'Données missions',      icon: Briefcase },
  { key: 'badges',   label: 'Données badges',        icon: Award     },
]

const toggleEvent   = (id) => {
  const i = selectedEvents.value.indexOf(id)
  i === -1 ? selectedEvents.value.push(id) : selectedEvents.value.splice(i, 1)
}
const toggleMission = (id) => {
  const i = selectedMissions.value.indexOf(id)
  i === -1 ? selectedMissions.value.push(id) : selectedMissions.value.splice(i, 1)
}
const selectAllEvents     = () => { selectedEvents.value   = exportEvents.value.map(e => e.id) }
const deselectAllEvents   = () => { selectedEvents.value   = [] }
const selectAllMissions   = () => { selectedMissions.value = exportMissions.value.map(m => m.id) }
const deselectAllMissions = () => { selectedMissions.value = [] }

const exportStatistics = () => {
  alert(
    `Export ${exportFormat.value.toUpperCase()}\n\n` +
    `Événements : ${selectedEvents.value.length}\n` +
    `Missions : ${selectedMissions.value.length}\n\n` +
    `Le fichier ${exportFormat.value === 'pdf' ? 'PDF' : 'Excel'} sera téléchargé.`
  )
  showExportDialog.value = false
}
</script>