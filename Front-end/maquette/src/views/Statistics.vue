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
                <p class="small text-muted mb-0">Export en format Excel avec tous les donnees</p>
              </div>
              <button class="btn-close" @click="showExportDialog = false"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-4">

              <!-- Types de données -->
              <div>
                <h6 class="fw-medium mb-3">Types de donnees a inclure</h6>
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
                  <h6 class="fw-medium mb-0">Evenements a inclure ({{ exportEvents.length }})</h6>
                  <div class="d-flex gap-2">
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" @click="selectAllEvents">Tous</button>
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" @click="deselectAllEvents">Aucun</button>
                  </div>
                </div>
                <div class="bg-light rounded p-3 d-flex flex-column gap-2" style="max-height:160px;overflow-y:auto">
                  <div v-if="exportEvents.length === 0" class="text-muted small py-2">Aucun evenement disponible</div>
                  <div v-for="event in exportEvents" :key="event.id" class="form-check mb-0">
                    <input class="form-check-input" type="checkbox"
                      :id="`ev-${event.id}`"
                      :checked="selectedEvents.includes(event.id)"
                      @change="toggleEvent(event.id)" />
                    <label :for="`ev-${event.id}`" class="form-check-label small">{{ event.name }}</label>
                  </div>
                </div>
                <p class="x-small text-muted mt-1 mb-0">{{ selectedEvents.length }} evenement(s) selectionne(s)</p>
              </div>

              <!-- Missions -->
              <div v-if="selectedDataTypes.missions">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <h6 class="fw-medium mb-0">Missions a inclure ({{ filteredExportMissions.length }})</h6>
                  <div class="d-flex gap-2">
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" :disabled="filteredExportMissions.length === 0" @click="selectAllMissions">Toutes</button>
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" @click="deselectAllMissions">Aucune</button>
                  </div>
                </div>
                <div class="bg-light rounded p-3 d-flex flex-column gap-2" style="max-height:160px;overflow-y:auto">
                  <div v-if="filteredExportMissions.length === 0" class="text-muted small py-2">Aucune mission disponible</div>
                  <div v-for="mission in filteredExportMissions" :key="mission.id" class="form-check mb-0">
                    <input class="form-check-input" type="checkbox"
                      :id="`ms-${mission.id}`"
                      :checked="selectedMissions.includes(mission.id)"
                      @change="toggleMission(mission.id)" />
                    <label :for="`ms-${mission.id}`" class="form-check-label small">
                      {{ mission.name }} - {{ mission.eventName }}
                    </label>
                  </div>
                </div>
                <p class="x-small text-muted mt-1 mb-0">
                  {{ selectedMissions.length }} mission(s) selectionnee(s) sur {{ filteredExportMissions.length }} disponibles
                </p>
              </div>

            </div>

            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="showExportDialog = false">Annuler</button>
              <button class="btn btn-primary d-flex align-items-center gap-2" :disabled="exportLoading" @click="exportStatistics">
                <span v-if="exportLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <FileSpreadsheet v-else style="width:16px;height:16px" />
                {{ exportLoading ? 'Generation...' : 'Exporter en Excel' }}
              </button>
            </div>

          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import {
  ArrowLeft, Download, TrendingUp, Users, Calendar, Award,
  Briefcase, BarChart3, FileSpreadsheet
} from 'lucide-vue-next'
import { getCurrentUser, isRole } from '@/utils/auth'
import api from '@/services/api'
import { eventService } from '@/services/eventService'
import { missionService } from '@/services/missionService'
import { useToast } from '@/composables/useToast'
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
    console.log('Loading stats...')
    const statsRes = await api.get('/stats')
    console.log('Stats response:', statsRes.data)
    
    rawKpis.value        = statsRes.data.kpis || {}
    rawRoleStats.value   = statsRes.data.roleStats || {}
    missionsByEvent.value = statsRes.data.missionsByEvent || []

    // Load events - transform to flat array
    console.log('Loading events...')
    let eventsData = []
    try {
      eventsData = await eventService.getAll()
      console.log('Events raw data:', eventsData)
      if (Array.isArray(eventsData)) {
        exportEvents.value = eventsData.map(e => ({
          id: e.id || e.id_evenement,
          name: e.name || e.nom_evenement || 'Evenement'
        }))
      } else {
        exportEvents.value = []
      }
    } catch (eErr) {
      console.error('Erreur chargement evenements:', eErr)
      exportEvents.value = []
    }
    console.log('Events transformed:', exportEvents.value)

    // Load missions - transform to flat array
    console.log('Loading missions...')
    let missionsData = []
    try {
      missionsData = await missionService.getAll()
      console.log('Missions raw data:', missionsData)
      if (Array.isArray(missionsData)) {
        exportMissions.value = missionsData.map(m => ({
          id: m.id || m.id_mission,
          name: m.name || m.nom_mission || 'Mission',
          eventName: m.eventName || m.nom_evenement || 'N/A',
          eventId: m.eventId || m.id_evenement || 0,
          date: m.date || m.date_mission || '',
          startTime: m.startTime || m.heure_debut_mission || '',
          endTime: m.endTime || m.heure_fin_mission || '',
          location: m.location || m.lieu_mission || ''
        }))
      } else {
        exportMissions.value = []
      }
    } catch (mErr) {
      console.error('Erreur chargement missions:', mErr)
      exportMissions.value = []
    }
    console.log('Missions transformed:', exportMissions.value)

    // Auto-select all
    selectedEvents.value   = exportEvents.value.map(e => e.id)
    selectedMissions.value = exportMissions.value.map(m => m.id)
    
    console.log('Auto-selected events:', selectedEvents.value)
    console.log('Auto-selected missions:', selectedMissions.value)
  } catch (e) {
    console.error('Erreur chargement statistiques global:', e)
    toast.error('Erreur au chargement des statistiques')
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
  const rows = exportEvents.value.filter((event) => selectedIds.has(String(event.id)))
  console.log('Selected event rows:', rows)
  return rows
})

const getMissionEventId = (mission) => {
  const id = mission?.eventId ?? mission?.id_evenement ?? mission?.eventId ?? 0
  return String(id)
}

const filteredExportMissions = computed(() => {
  if (!selectedDataTypes.value.events) {
    return exportMissions.value
  }

  const selectedEventIds = new Set(selectedEvents.value.map(String))
  const filtered = exportMissions.value.filter((mission) => selectedEventIds.has(getMissionEventId(mission)))
  console.log('Filtered export missions:', filtered)
  return filtered
})

const selectedMissionRows = computed(() => {
  const selectedIds = new Set(selectedMissions.value.map(String))
  const rows = filteredExportMissions.value.filter((mission) => selectedIds.has(String(mission.id)))
  console.log('Selected mission rows:', rows)
  return rows
})

watch(
  [filteredExportMissions, selectedDataTypes],
  () => {
    if (!selectedDataTypes.value.missions) return

    const allowedMissionIds = new Set(filteredExportMissions.value.map((mission) => String(mission.id)))
    selectedMissions.value = selectedMissions.value.filter((missionId) => allowedMissionIds.has(String(missionId)))
  },
  { immediate: true }
)

const buildExportSections = () => {
  const sections = []
  const roleRows = roleStats.value
  const kpis = rawKpis.value || {}

  // ── UTILISATEURS ───────────────────────────────────────────────────────────
  if (selectedDataTypes.value.users) {
    const totalUsers = Number(kpis.totalUsers ?? 0)
    const volunteers = Number(kpis.volunteerCount ?? 0)
    const activeMissions = Number(kpis.activeMissions ?? 0)
    const confirmations = Number(kpis.confirmedAffectations ?? 0)
    const completion = Number(kpis.completionRate ?? 0)

    const userRows = [
      ['KPI Global', 'Valeur'],
      ['Total utilisateurs', String(totalUsers)],
      ['Utilisateurs actifs (bénévoles)', String(volunteers)],
      ['Taux d\'utilisation', volunteers > 0 ? `${Math.round((volunteers / Math.max(totalUsers, 1)) * 100)}%` : '0%'],
      ['', ''],
      ['Missions & Affectations', 'Valeur'],
      ['Missions actives', String(activeMissions)],
      ['Affectations confirmées', String(confirmations)],
      ['Taux de complétion moyen', `${completion}%`],
      ['Capacité moyenne par mission', activeMissions > 0 ? `${Math.round(confirmations / activeMissions)}` : 'N/A'],
      ['', ''],
      ['Répartition par rôle', 'Nombre (%)'],
    ]

    if (roleRows && roleRows.length > 0) {
      for (const row of roleRows) {
        userRows.push([row.label, `${row.count} (${row.pct}%)`])
      }
    }

    sections.push({
      title: 'Utilisateurs & KPIs',
      columns: ['Indicateur', 'Valeur'],
      rows: userRows,
    })
  }

  // ── ÉVÉNEMENTS ─────────────────────────────────────────────────────────────
  if (selectedDataTypes.value.events) {
    const eventRows = [['ID', 'Nom', 'Missions', 'Bénévoles Requis', 'Bénévoles Actuels', 'Taux Remplissage']]

    if (selectedEventRows.value && selectedEventRows.value.length > 0) {
      for (const event of selectedEventRows.value) {
        const eventMissions = missionsByEvent.value.find(e => String(e.id) === String(event.id))
        if (eventMissions) {
          const ratio = eventMissions.totalNeeded > 0 
            ? Math.round((eventMissions.currentVolunteers / eventMissions.totalNeeded) * 100)
            : 0
          eventRows.push([
            String(event.id),
            event.name || 'Événement',
            String(eventMissions.missionsCount ?? 0),
            String(eventMissions.totalNeeded ?? 0),
            String(eventMissions.currentVolunteers ?? 0),
            `${ratio}%`,
          ])
        } else {
          eventRows.push([
            String(event.id),
            event.name || 'Événement',
            '0',
            'N/A',
            'N/A',
            'N/A',
          ])
        }
      }
    } else {
      eventRows.push(['Aucun', 'Aucun événement sélectionné', '', '', '', ''])
    }

    sections.push({
      title: 'Événements',
      columns: eventRows[0],
      rows: eventRows.slice(1),
    })
  }

  // ── MISSIONS ───────────────────────────────────────────────────────────────
  if (selectedDataTypes.value.missions) {
    const missionRows = [['ID', 'Nom', 'Événement', 'Date', 'Début', 'Fin', 'Lieu']]

    if (selectedMissionRows.value && selectedMissionRows.value.length > 0) {
      for (const mission of selectedMissionRows.value) {
        missionRows.push([
          String(mission.id),
          mission.name || 'Mission',
          mission.eventName || 'N/A',
          mission.date || '',
          mission.startTime || '',
          mission.endTime || '',
          mission.location || '',
        ])
      }
    } else {
      missionRows.push(['Aucune', 'Aucune mission sélectionnée', '', '', '', '', ''])
    }

    sections.push({
      title: 'Missions',
      columns: missionRows[0],
      rows: missionRows.slice(1),
    })
  }

  // ── BADGES & AUTRES STATS ──────────────────────────────────────────────────
  if (selectedDataTypes.value.badges) {
    const statsRows = [
      ['Indicateur', 'Valeur'],
      ['Total badges disponibles', String(kpis.totalBadges ?? 0)],
      ['Total missions (historique)', String(kpis.totalMissions ?? 0)],
      ['Total événements', String(kpis.totalEvents ?? 0)],
      ['', ''],
      ['Résumé Export', 'Nombre'],
      ['Événements exportés', String(selectedEventRows.value?.length ?? 0)],
      ['Missions exportées', String(selectedMissionRows.value?.length ?? 0)],
      ['Date de génération', new Date().toLocaleDateString('fr-FR')],
      ['Heure de génération', new Date().toLocaleTimeString('fr-FR')],
    ]

    sections.push({
      title: 'Badges & Resume',
      columns: statsRows[0],
      rows: statsRows.slice(1),
    })
  }

  return sections
}

const sanitizeFileName = (name) =>
  String(name || 'export-statistiques')
    .replace(/[\\/:*?"<>|]+/g, '-')
    .replace(/\s+/g, '-')
    .trim()

const exportAsExcel = (sections) => {
  try {
    const workbook = XLSX.utils.book_new()

    for (const section of sections) {
      if (!section || !section.rows || section.rows.length === 0) {
        continue
      }
      
      const rows = [section.columns, ...section.rows]
      const worksheet = XLSX.utils.aoa_to_sheet(rows)
      
      // Auto-size columns
      const colWidths = section.columns.map((col, idx) => {
        const maxLen = Math.max(
          col.length,
          ...section.rows.map(row => String(row[idx] || '').length)
        )
        return { wch: Math.min(maxLen + 2, 50) }
      })
      worksheet['!cols'] = colWidths
      
      XLSX.utils.book_append_sheet(workbook, worksheet, section.title.slice(0, 31))
    }

    const fileName = `${sanitizeFileName(`statistiques-${new Date().toISOString().slice(0, 10)}`)}.xlsx`
    XLSX.writeFile(workbook, fileName)
    console.log('Excel export success:', fileName)
  } catch (error) {
    console.error('Erreur export Excel:', error)
    throw error
  }
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
  if (!filteredExportMissions.value.some((mission) => String(mission.id) === String(id))) {
    return
  }

  const i = selectedMissions.value.indexOf(id)
  if (i === -1) {
    selectedMissions.value.push(id)
  } else {
    selectedMissions.value.splice(i, 1)
  }
}
const selectAllEvents     = () => { selectedEvents.value   = exportEvents.value.map(e => e.id) }
const deselectAllEvents   = () => { selectedEvents.value   = [] }
const selectAllMissions   = () => { selectedMissions.value = filteredExportMissions.value.map(m => m.id) }
const deselectAllMissions = () => { selectedMissions.value = [] }

const exportStatistics = async () => {
  if (exportLoading.value) return

  const selectedTypes = Object.entries(selectedDataTypes.value)
    .filter(([, enabled]) => enabled)
    .map(([key]) => key)

  if (selectedTypes.length === 0) {
    toast.error('Selectionnez au moins un type de donnees a exporter.')
    return
  }

  if (selectedDataTypes.value.events && selectedEvents.value.length === 0) {
    toast.error('Selectionnez au moins un evenement.')
    return
  }

  if (selectedDataTypes.value.missions && selectedMissions.value.length === 0) {
    toast.error('Selectionnez au moins une mission.')
    return
  }

  const sections = buildExportSections().filter((section) => section.rows && section.rows.length > 0)
  
  console.log('Export sections:', sections)
  console.log('Export events count:', exportEvents.value.length)
  console.log('Export missions count:', exportMissions.value.length)
  console.log('Selected events:', selectedEvents.value)
  console.log('Selected missions:', selectedMissions.value)
  
  if (sections.length === 0) {
    toast.error('Aucune donnee disponible pour cet export.')
    return
  }

  exportLoading.value = true
  try {
    exportAsExcel(sections)
    toast.success('Export Excel lance avec succes.')
    showExportDialog.value = false
  } catch (error) {
    console.error('Erreur lors de l\'export:', error)
    toast.error(`Erreur lors de l\'export Excel: ${error.message}`)
  } finally {
    exportLoading.value = false
  }
}
</script>