<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="text-white p-4 pb-4 position-relative overflow-hidden"
      style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">
      <div class="position-relative" style="z-index:1">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="bg-white rounded-3 p-2 shadow">
            <img src="@/assets/logo.png" alt="Running Geneva" style="height:40px;width:auto" />
          </div>
          <div>
            <div class="small text-white-50">Running Geneva</div>
            <div class="fs-5 fw-bold">Mes Missions</div>
          </div>
        </div>
        <span class="badge fw-bold px-3 py-2 text-dark"
          style="background:linear-gradient(135deg,#d4e645,#a3c200)">
          {{ myActiveMissions.length }} en cours
        </span>
      </div>
    </header>

    <div class="px-3 pt-3 d-flex flex-column gap-3 mx-auto" style="max-width:576px">

      <!-- Empty state -->
      <div v-if="myActiveMissions.length === 0" class="text-center py-5">
        <Calendar class="text-muted mb-3" style="width:64px;height:64px" />
        <p class="text-muted mb-3">Aucune mission en cours</p>
        <button class="btn btn-primary" @click="router.push('/events')">
          Découvrir les événements
        </button>
      </div>

      <!-- Mission Cards -->
      <div v-for="mission in myActiveMissions" :key="mission.id"
        class="card overflow-hidden">

        <div class="position-relative">
          <img :src="mission.imageUrl" :alt="mission.name"
            class="w-100 object-fit-cover" style="height:160px" />

          <div v-if="daysUntil(mission) > 0 && daysUntil(mission) <= 7"
            class="position-absolute" style="top:8px;left:8px">
            <span class="badge bg-warning text-dark px-3 py-2">
              Dans {{ daysUntil(mission) }} jour{{ daysUntil(mission) > 1 ? 's' : '' }}
            </span>
          </div>
          <div v-else-if="daysUntil(mission) === 0"
            class="position-absolute" style="top:8px;left:8px">
            <span class="badge bg-danger px-3 py-2">Aujourd'hui !</span>
          </div>
        </div>

        <div class="card-body d-flex flex-column gap-3">
          <div>
            <div class="x-small text-muted mb-1">{{ mission.eventName }}</div>
            <h5 class="fw-semibold mb-0">{{ mission.name }}</h5>
          </div>

          <div class="d-flex flex-column gap-2 small text-muted">
            <div class="d-flex align-items-center gap-2">
              <Calendar style="width:16px;height:16px" />
              <span>{{ formatDate(mission.date) }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <Clock style="width:16px;height:16px" />
              <span>{{ mission.startTime }} - {{ mission.endTime }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <MapPin style="width:16px;height:16px" />
              <span>{{ mission.location }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <Users style="width:16px;height:16px" />
              <span>{{ mission.currentVolunteers }}/{{ mission.maxVolunteers }} bénévoles inscrits</span>
            </div>
          </div>

          <div class="rounded p-3" style="background:#eff6ff;border:1px solid #bfdbfe">
            <div class="x-small fw-medium mb-1" style="color:#1e3a5f">Responsable</div>
            <div class="small" style="color:#1d4ed8">{{ mission.responsible.name }}</div>
            <div class="x-small mt-1" style="color:#2563eb">{{ mission.responsible.phone }}</div>
          </div>

          <button class="btn btn-primary w-100"
            @click="router.push(`/mission/${mission.id}`)">
            Voir les détails et la carte
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { MapPin, Calendar, Users, Clock } from 'lucide-vue-next'
import { myActiveMissions } from '@/data/mockData'

const router = useRouter()

const daysUntil = (m) =>
  Math.ceil((new Date(m.date).getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24))

const formatDate = (d) =>
  new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
</script>