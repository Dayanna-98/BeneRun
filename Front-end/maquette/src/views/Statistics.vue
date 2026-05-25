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
              <BarChart3 style="width:22px;height:22px;color:#d4e645" />
            </div>
            <div>
              <div class="x-small" style="color:rgba(255,255,255,.5)">Administration</div>
              <div class="fs-5 fw-bold">Statistiques</div>
            </div>
          </div>
        </div>
        <button class="btn btn-sm fw-semibold d-flex align-items-center gap-2 px-3 rounded-pill"
          style="background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230;border:none"
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
      <div class="card border-0 shadow-sm overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-3 py-3"
          style="background:linear-gradient(135deg,#1a2230,#2d3a4a)">
          <BarChart3 style="width:18px;height:18px;color:#d4e645" />
          <span class="fw-semibold text-white">Répartition des utilisateurs par rôle</span>
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
      <div class="card border-0 shadow-sm overflow-hidden">
        <div class="px-3 py-3" style="background:linear-gradient(135deg,#1a2230,#2d3a4a)">
          <span class="fw-semibold text-white">Missions par événement</span>
        </div>
        <div class="card-body d-flex flex-column gap-2">
          <div v-if="statsLoading" class="text-muted small py-2">Chargement...</div>
          <div v-for="event in missionsByEvent" :key="event.id"
            class="d-flex align-items-center justify-content-between p-3 rounded-3"
            style="background:#f8fafc;border:1px solid #e2e8f0">
            <div class="flex-fill">
              <div class="small fw-medium">{{ event.name }}</div>
              <div class="x-small text-muted">{{ event.currentVolunteers }}/{{ event.totalNeeded }} bénévoles</div>
            </div>
            <div class="text-end">
              <div class="fs-5 fw-bold" style="color:#1a2230">{{ event.missionsCount }}</div>
              <div class="x-small text-muted">missions</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions rapides -->
      <div class="card border-0 shadow-sm overflow-hidden">
        <div class="d-flex align-items-center justify-content-between px-3 py-3"
          style="background:linear-gradient(135deg,#1a2230,#2d3a4a)">
          <span class="fw-semibold text-white">Actions rapides</span>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-6">
              <button class="btn w-100 py-3 d-flex flex-column align-items-center gap-2 border-0 rounded-3"
                style="background:linear-gradient(135deg,#eff6ff,#dbeafe);color:#1d4ed8"
                @click="showExportDialog = true">
                <Download style="width:24px;height:24px" />
                <span class="small fw-medium">Exporter</span>
              </button>
            </div>
            <div class="col-6">
              <button class="btn w-100 py-3 d-flex flex-column align-items-center gap-2 border-0 rounded-3"
                style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);color:#166534"
                @click="router.push('/manage-users')">
                <Users style="width:24px;height:24px" />
                <span class="small fw-medium">Utilisateurs</span>
              </button>
            </div>
            <div class="col-6">
              <button class="btn w-100 py-3 d-flex flex-column align-items-center gap-2 border-0 rounded-3"
                style="background:linear-gradient(135deg,#faf5ff,#f3e8ff);color:#7c3aed"
                @click="router.push('/manage-events')">
                <Calendar style="width:24px;height:24px" />
                <span class="small fw-medium">Evénements</span>
              </button>
            </div>
            <div class="col-6">
              <button class="btn w-100 py-3 d-flex flex-column align-items-center gap-2 border-0 rounded-3"
                style="background:linear-gradient(135deg,#fff7ed,#ffedd5);color:#c2410c"
                @click="router.push('/manage-missions')">
                <Briefcase style="width:24px;height:24px" />
                <span class="small fw-medium">Missions</span>
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
              <button class="btn btn-primary d-flex align-items-center gap-2" :disabled="exportLoading" @click="exportStatistics">
                <span v-if="exportLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <Download v-else style="width:16px;height:16px" />
                {{ exportLoading ? 'Génération...' : `Exporter en ${exportFormat.toUpperCase()}` }}
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
import { useToast } from '@/composables/useToast'
import { jsPDF } from 'jspdf'
import * as XLSX from 'xlsx'

const router = useRouter()
const toast = useToast()

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
  { keys: ['bénévole', 'benevole', 'volunteer'], label: 'Bénévoles', color: '#3b82f6' },
  { keys: ['responsable', 'mission_manager', 'organisateur'], label: 'Resp. de mission', color: '#22c55e' },
  { keys: ['admin'], label: 'Administrateurs', color: '#a855f7' },
  { keys: ['superadmin', 'super_admin'], label: 'Super-admins', color: '#ef4444' },
]

const roleStats = computed(() => {
  const rs = rawRoleStats.value
  const normalizedStats = Object.entries(rs || {}).reduce((acc, [rawKey, rawValue]) => {
    const key = String(rawKey || '').toLowerCase().trim()
    acc[key] = Number(rawValue || 0)
    return acc
  }, {})

  const total = Object.values(normalizedStats).reduce((s, v) => s + Number(v), 0) || 1
  return ROLE_META.map((r) => {
    const count = r.keys.reduce((sum, roleKey) => sum + Number(normalizedStats[roleKey] || 0), 0)
    return { ...r, count, pct: Math.round((count / total) * 100) }
  })
})

// ── Export dialog ─────────────────────────────────────────────────────────────
const showExportDialog  = ref(false)
const exportFormat      = ref('pdf')
const exportLoading     = ref(false)

const selectedDataTypes = ref({ users: true, events: true, missions: true, badges: true })
const selectedEvents    = ref([])
const selectedMissions  = ref([])

const dataTypeOptions = [
  { key: 'users',    label: 'Données utilisateurs', icon: Users     },
  { key: 'events',   label: 'Données événements',   icon: Calendar  },
  { key: 'missions', label: 'Données missions',      icon: Briefcase },
  { key: 'badges',   label: 'Données badges',        icon: Award     },
]

const selectedEventRows = computed(() => {
  const selectedIds = new Set(selectedEvents.value.map(String))
  return exportEvents.value.filter((event) => selectedIds.has(String(event.id)))
})

const selectedMissionRows = computed(() => {
  const selectedIds = new Set(selectedMissions.value.map(String))
  return exportMissions.value.filter((mission) => selectedIds.has(String(mission.id)))
})

const buildExportSections = () => {
  const sections = []
  const roleRows = roleStats.value

  if (selectedDataTypes.value.users) {
    sections.push({
      title: 'Utilisateurs',
      columns: ['Indicateur', 'Valeur'],
      rows: [
        ['Total utilisateurs', String(rawKpis.value?.totalUsers ?? 0)],
        ['Bénévoles', String(rawKpis.value?.volunteerCount ?? 0)],
        ['Taux de complétion (%)', String(rawKpis.value?.completionRate ?? 0)],
        ...roleRows.map((row) => [`Rôle: ${row.label}`, `${row.count} (${row.pct}%)`]),
      ],
    })
  }

  if (selectedDataTypes.value.events) {
    sections.push({
      title: 'Événements',
      columns: ['ID', 'Nom'],
      rows: selectedEventRows.value.map((event) => [String(event.id), event.name || 'Événement']),
    })
  }

  if (selectedDataTypes.value.missions) {
    sections.push({
      title: 'Missions',
      columns: ['ID', 'Nom', 'Événement', 'Date', 'Heure début', 'Heure fin', 'Lieu'],
      rows: selectedMissionRows.value.map((mission) => [
        String(mission.id),
        mission.name || 'Mission',
        mission.eventName || 'N/A',
        mission.date || '',
        mission.startTime || '',
        mission.endTime || '',
        mission.location || '',
      ]),
    })
  }

  if (selectedDataTypes.value.badges) {
    sections.push({
      title: 'Badges',
      columns: ['Indicateur', 'Valeur'],
      rows: [
        ['Total badges disponibles', String(rawKpis.value?.totalBadges ?? 0)],
      ],
    })
  }

  return sections
}

const sanitizeFileName = (name) =>
  String(name || 'export-statistiques')
    .replace(/[\\/:*?"<>|]+/g, '-')
    .replace(/\s+/g, '-')
    .trim()

const exportAsPdf = (sections) => {
  const doc = new jsPDF({ unit: 'pt', format: 'a4' })
  const left = 40
  const topStart = 54
  const bottom = 780
  const lineHeight = 16

  let y = topStart

  const addLine = (text, options = {}) => {
    const size = options.size || 11
    const bold = !!options.bold
    doc.setFont('helvetica', bold ? 'bold' : 'normal')
    doc.setFontSize(size)

    const maxWidth = 515
    const lines = doc.splitTextToSize(String(text || ''), maxWidth)
    for (const line of lines) {
      if (y > bottom) {
        doc.addPage()
        y = topStart
      }
      doc.text(line, left, y)
      y += lineHeight
    }
  }

  addLine('Export statistiques Béné\'Run', { size: 16, bold: true })
  addLine(`Généré le ${new Date().toLocaleString('fr-FR')}`, { size: 10 })
  y += 10

  for (const section of sections) {
    addLine(section.title, { size: 13, bold: true })
    addLine(section.columns.join(' | '), { size: 10, bold: true })
    for (const row of section.rows) {
      addLine(row.join(' | '), { size: 10 })
    }
    y += 8
  }

  const fileName = `${sanitizeFileName(`statistiques-${new Date().toISOString().slice(0, 10)}`)}.pdf`
  doc.save(fileName)
}

const exportAsExcel = (sections) => {
  const workbook = XLSX.utils.book_new()

  for (const section of sections) {
    const rows = [section.columns, ...section.rows]
    const worksheet = XLSX.utils.aoa_to_sheet(rows)
    XLSX.utils.book_append_sheet(workbook, worksheet, section.title.slice(0, 31))
  }

  const fileName = `${sanitizeFileName(`statistiques-${new Date().toISOString().slice(0, 10)}`)}.xlsx`
  XLSX.writeFile(workbook, fileName)
}

const toggleEvent   = (id) => {
  const i = selectedEvents.value.indexOf(id)
  if (i === -1) {
    selectedEvents.value.push(id)
  } else {
    selectedEvents.value.splice(i, 1)
  }
}
const toggleMission = (id) => {
  const i = selectedMissions.value.indexOf(id)
  if (i === -1) {
    selectedMissions.value.push(id)
  } else {
    selectedMissions.value.splice(i, 1)
  }
}
const selectAllEvents     = () => { selectedEvents.value   = exportEvents.value.map(e => e.id) }
const deselectAllEvents   = () => { selectedEvents.value   = [] }
const selectAllMissions   = () => { selectedMissions.value = exportMissions.value.map(m => m.id) }
const deselectAllMissions = () => { selectedMissions.value = [] }

const exportStatistics = async () => {
  if (exportLoading.value) return

  const selectedTypes = Object.entries(selectedDataTypes.value)
    .filter(([, enabled]) => enabled)
    .map(([key]) => key)

  if (selectedTypes.length === 0) {
    toast.error('Sélectionnez au moins un type de données à exporter.')
    return
  }

  if (selectedDataTypes.value.events && selectedEvents.value.length === 0) {
    toast.error('Sélectionnez au moins un événement.')
    return
  }

  if (selectedDataTypes.value.missions && selectedMissions.value.length === 0) {
    toast.error('Sélectionnez au moins une mission.')
    return
  }

  const sections = buildExportSections().filter((section) => section.rows.length > 0)
  if (sections.length === 0) {
    toast.error('Aucune donnée disponible pour cet export.')
    return
  }

  exportLoading.value = true
  try {
    if (exportFormat.value === 'pdf') {
      exportAsPdf(sections)
    } else {
      exportAsExcel(sections)
    }

    toast.success(`Export ${exportFormat.value.toUpperCase()} lancé avec succès.`)
    showExportDialog.value = false
  } catch (error) {
    console.error('Erreur lors de l\'export des statistiques:', error)
    toast.error('Impossible de générer le fichier d\'export pour le moment.')
  } finally {
    exportLoading.value = false
  }
}
</script>