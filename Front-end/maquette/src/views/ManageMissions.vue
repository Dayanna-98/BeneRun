<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Gestion des Missions</h1>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
          @click="router.push('/create-mission')">
          <Plus style="width:16px;height:16px" /> Créer
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:1024px">

      <!-- Stats -->
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary">{{ missions.length }}</div>
              <div class="x-small text-muted">Total missions</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-success">{{ availableCount }}</div>
              <div class="x-small text-muted">Places disponibles</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-warning">{{ fullCount }}</div>
              <div class="x-small text-muted">Complets</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold" style="color:#2563eb">{{ totalVolunteers }}</div>
              <div class="x-small text-muted">Bénévoles inscrits</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Liste -->
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Toutes les missions</h5></div>
        <div class="card-body d-flex flex-column gap-3">
          <div v-for="mission in missions" :key="mission.id"
            class="border rounded p-3">

            <div class="d-flex align-items-start justify-content-between mb-3">
              <div class="flex-fill">
                <h6 class="fw-semibold text-primary mb-1">{{ mission.name }}</h6>
                <p class="small text-muted mb-0">{{ mission.eventName }}</p>
              </div>
              <div class="d-flex gap-1">
                <button class="btn btn-link p-1 text-secondary"
                  @click="router.push(`/edit-mission/${mission.id}`)">
                  <Edit style="width:16px;height:16px" />
                </button>
                <button class="btn btn-link p-1 text-danger"
                  @click="handleDeleteMission(mission.id)">
                  <Trash2 style="width:16px;height:16px" />
                </button>
              </div>
            </div>

            <div class="row g-2 small text-muted mb-3">
              <div class="col-6 d-flex align-items-center gap-2">
                <Calendar style="width:16px;height:16px" />
                <span class="x-small">{{ formatDate(mission.date) }}</span>
              </div>
              <div class="col-6 d-flex align-items-center gap-2">
                <Clock style="width:16px;height:16px" />
                <span class="x-small">{{ mission.startTime }} - {{ mission.endTime }}</span>
              </div>
              <div class="col-6 d-flex align-items-center gap-2">
                <MapPin style="width:16px;height:16px" />
                <span class="x-small text-truncate">{{ mission.location }}</span>
              </div>
              <div class="col-6 d-flex align-items-center gap-2">
                <Users style="width:16px;height:16px" />
                <span class="x-small">{{ mission.currentVolunteers }}/{{ mission.maxVolunteers }}</span>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <span v-for="skill in mission.requiredSkills" :key="skill"
                class="badge border"
                style="background:rgba(26,34,48,.05);color:#1a2230;border-color:rgba(26,34,48,.2)">
                {{ skill }}
              </span>
              <span v-if="mission.currentVolunteers === mission.maxVolunteers"
                class="badge bg-warning-subtle text-warning border border-warning">
                Complet
              </span>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Plus, Edit, Trash2, Users, Calendar, MapPin, Clock } from 'lucide-vue-next'
import { availableMissions } from '@/data/mockData'
import { getCurrentUser, hasMinRole } from '@/utils/auth'

const router = useRouter()
const user = getCurrentUser()
if (!user || !hasMinRole('organizer')) router.push('/')

const missions = ref([...availableMissions])

const availableCount = computed(() => missions.value.filter(m => m.currentVolunteers < m.maxVolunteers).length)
const fullCount       = computed(() => missions.value.filter(m => m.currentVolunteers === m.maxVolunteers).length)
const totalVolunteers = computed(() => missions.value.reduce((s, m) => s + m.currentVolunteers, 0))

const formatDate = (d) =>
  new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })

const handleDeleteMission = (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette mission ?')) {
    missions.value = missions.value.filter(m => m.id !== id)
    alert('Mission supprimée avec succès')
  }
}
</script>