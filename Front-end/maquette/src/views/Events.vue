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
            <div class="fs-5 fw-bold">Événements</div>
          </div>
        </div>
        <p class="text-white-50 small mb-0">Découvrez les missions disponibles</p>
      </div>
    </header>

    <div class="px-3 pt-3 mx-auto" style="max-width:576px">

      <!-- Recherche -->
      <div class="mb-3 position-relative">
        <Search class="position-absolute text-muted" style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
        <input
          v-model="searchQuery"
          type="text"
          class="form-control ps-5 shadow-sm"
          placeholder="Rechercher une mission..."
        />
      </div>

      <!-- Mission Cards -->
      <div class="d-flex flex-column gap-4">
        <div v-for="mission in filteredMissions" :key="mission.id"
          class="card border-0 shadow overflow-hidden"
          style="cursor:pointer"
          @click="router.push(`/mission/${mission.id}`)">

          <!-- Image -->
          <div class="position-relative" style="height:160px">
            <img
              :src="mission.imageUrl || 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop'"
              :alt="mission.name"
              class="w-100 h-100 object-fit-cover"
            />
            <div class="position-absolute d-flex gap-2" style="top:8px;right:8px">
              <button
                class="btn rounded-circle p-2 shadow"
                :class="favorites.includes(mission.id) ? 'btn-danger' : 'btn-light'"
                style="opacity:.9"
                @click.stop="toggleFavorite(mission.id)">
                <Star :style="favorites.includes(mission.id) ? 'fill:white' : ''" style="width:20px;height:20px" />
              </button>
              <button class="btn btn-light rounded-circle p-2 shadow" style="opacity:.9"
                @click.stop="alert('Partager la mission')">
                <Share2 style="width:20px;height:20px" />
              </button>
            </div>
            <div v-if="!canApply(mission)"
              class="position-absolute inset-0 w-100 h-100 d-flex align-items-center justify-content-center"
              style="background:rgba(0,0,0,.6)">
              <span class="badge bg-danger fs-6">Compétences requises manquantes</span>
            </div>
          </div>

          <!-- Content -->
          <div class="card-body d-flex flex-column gap-3">
            <div>
              <h5 class="fw-bold mb-1">{{ mission.name }}</h5>
              <p class="small text-muted mb-0">{{ mission.eventName }}</p>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <span v-for="skill in mission.requiredSkills" :key="skill"
                :class="['badge', userSkillNames.includes(skill) ? 'text-dark fw-semibold' : 'bg-secondary']"
                :style="userSkillNames.includes(skill) ? 'background:#d4e645' : ''">
                {{ skill }}
              </span>
            </div>

            <div class="d-flex flex-column gap-2 small text-muted">
              <div class="d-flex align-items-center gap-2">
                <MapPin style="width:16px;height:16px" />
                <span>{{ mission.location }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Clock style="width:16px;height:16px" />
                <span>{{ new Date(mission.date).toLocaleDateString('fr-FR') }} • {{ mission.startTime }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Users style="width:16px;height:16px" />
                <span>
                  {{ mission.currentVolunteers }}/{{ mission.maxVolunteers }} bénévoles
                  <span v-if="spotsLeft(mission) > 0 && spotsLeft(mission) <= 3" class="text-warning fw-semibold ms-1">
                    • {{ spotsLeft(mission) }} places restantes
                  </span>
                </span>
              </div>
            </div>

            <button
              class="btn btn-primary w-100"
              :disabled="!canApply(mission)"
              @click.stop="router.push(`/mission/${mission.id}`)">
              {{ canApply(mission) ? "S'inscrire" : 'Voir les détails' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="filteredMissions.length === 0" class="text-center py-5 text-muted">
        Aucune mission trouvée
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Search, MapPin, Users, Star, Share2, Clock } from 'lucide-vue-next'
import { availableMissions, skills } from '@/data/mockData'

const router = useRouter()
const searchQuery = ref('')
const favorites = ref(availableMissions.filter(m => m.isFavorite).map(m => m.id))
const userSkillNames = skills.map(s => s.name)

const filteredMissions = computed(() =>
  availableMissions.filter(m =>
    [m.name, m.eventName, m.location].some(f =>
      f.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  )
)

const spotsLeft = (m) => m.maxVolunteers - m.currentVolunteers

const canApply = (m) =>
  m.requiredSkills.every(s => userSkillNames.includes(s)) && spotsLeft(m) > 0

const toggleFavorite = (id) => {
  const i = favorites.value.indexOf(id)
  i === -1 ? favorites.value.push(id) : favorites.value.splice(i, 1)
}
</script>