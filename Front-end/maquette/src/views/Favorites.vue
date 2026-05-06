<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="text-white p-4 pb-5 position-relative overflow-hidden"
      style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">
      <div class="position-relative" style="z-index:1">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="bg-white rounded-3 p-2 shadow">
            <img src="@/assets/logo.png" alt="Running Geneva" style="height:32px;width:auto" />
          </div>
          <div>
            <div class="x-small text-white-50">Running Geneva</div>
            <div class="small fw-bold">Mes Favoris</div>
          </div>
        </div>
        <h1 class="fs-4 fw-bold mb-1">Missions favorites</h1>
        <p class="text-white-50 small mb-0">
          {{ favoriteMissions.length }} mission{{ favoriteMissions.length > 1 ? 's' : '' }}
          enregistrée{{ favoriteMissions.length > 1 ? 's' : '' }}
        </p>
      </div>
    </header>

    <!-- Content -->
    <div class="p-3 d-flex flex-column gap-3">

      <!-- Loading state -->
      <div v-if="loading" class="d-flex justify-content-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
      </div>

      <!-- Empty state -->
      <div v-else-if="favoriteMissions.length === 0" class="card border border-dashed">
        <div class="card-body text-center py-5">
          <Heart class="text-muted mb-3" style="width:48px;height:48px" />
          <p class="text-muted mb-1">Aucune mission favorite</p>
          <p class="small text-muted mb-0">Ajoutez des missions à vos favoris pour les retrouver facilement ici</p>
        </div>
      </div>

      <!-- Mission Cards -->
      <div
        v-else
        v-for="mission in favoriteMissions" :key="mission.id"
        class="card"
        style="cursor:pointer"
        @click="router.push(`/mission/${mission.id}`)">
        <div class="card-body">

          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="flex-fill">
              <div class="d-flex align-items-center gap-2 mb-1">
                <h6 class="fw-semibold text-primary mb-0">{{ mission.name }}</h6>
                <button class="btn btn-link p-1" @click.stop="toggleFavorite(mission.id)">
                  <Heart
                    style="width:20px;height:20px"
                    :style="favoriteIds.has(mission.id) ? 'fill:#ef4444;color:#ef4444' : 'color:#9ca3af'"
                  />
                </button>
              </div>
              <p v-if="mission.eventName" class="x-small text-muted mb-0">
                {{ mission.eventName }}
              </p>
            </div>
            <ChevronRight class="text-muted flex-shrink-0" style="width:20px;height:20px" />
          </div>

          <div class="d-flex flex-column gap-2 small text-muted mb-3">
            <div class="d-flex align-items-center gap-2">
              <MapPin class="text-muted" style="width:16px;height:16px" />
              <span class="x-small">{{ mission.location }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <Calendar class="text-muted" style="width:16px;height:16px" />
              <span class="x-small">{{ formatDate(mission.date) }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <Clock class="text-muted" style="width:16px;height:16px" />
              <span class="x-small">{{ mission.startTime.substring(0,5) }} - {{ mission.endTime.substring(0,5) }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <Users class="text-muted" style="width:16px;height:16px" />
              <span class="x-small">{{ mission.currentVolunteers }}/{{ mission.maxVolunteers }} bénévoles</span>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2">
            <span v-for="skill in mission.requiredSkills" :key="skill"
              class="badge border small" style="background:rgba(26,34,48,.05);color:#1a2230;border-color:rgba(26,34,48,.2)">
              {{ skill }}
            </span>
            <span v-if="spotsLeft(mission) > 0 && spotsLeft(mission) <= 3"
              class="badge border small" style="background:#fff7ed;color:#ea580c;border-color:#fed7aa">
              Plus que {{ spotsLeft(mission) }} place{{ spotsLeft(mission) > 1 ? 's' : '' }}
            </span>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Heart, MapPin, Calendar, Users, Clock, ChevronRight } from 'lucide-vue-next'
import api from '@/services/api'
import { missionService } from '@/services/missionService'

const router = useRouter()

const loading = ref(false)
const favoriteMissions = ref([])
const favoriteIds = ref(new Set())

onMounted(async () => {
  loading.value = true
  try {
    const res = await api.get('/favorites')
    const rows = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
    favoriteMissions.value = rows.map(missionService.mapApiMission).filter(Boolean)
    favoriteIds.value = new Set(favoriteMissions.value.map(m => m.id))
  } catch (e) {
    console.error('Erreur chargement favoris:', e)
  } finally {
    loading.value = false
  }
})

const spotsLeft = (m) => m.maxVolunteers - m.currentVolunteers

const toggleFavorite = async (id) => {
  try {
    if (favoriteIds.value.has(id)) {
      await api.delete(`/favorites/${id}`)
      favoriteIds.value.delete(id)
      favoriteMissions.value = favoriteMissions.value.filter(m => m.id !== id)
    } else {
      await api.post(`/favorites/${id}`)
      favoriteIds.value.add(id)
      // Recharger la mission depuis l'API pour l'ajouter à la liste
      const res = await api.get(`/missions/${id}`)
      const mapped = missionService.mapApiMission(res.data)
      if (mapped) favoriteMissions.value.push(mapped)
    }
  } catch (e) {
    console.error('Erreur toggle favori:', e)
  }
}

const formatDate = (date) =>
  new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
</script>