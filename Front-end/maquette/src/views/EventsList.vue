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
        <p class="text-white-50 small mb-0">Découvrez les événements à Genève</p>
      </div>
    </header>

    <div class="px-3 pt-3 mx-auto" style="max-width:576px">

      <!-- Recherche -->
      <div class="mb-3 position-relative">
        <Search class="position-absolute text-muted"
          style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
        <input
          v-model="searchQuery"
          type="text"
          class="form-control ps-5 shadow-sm"
          placeholder="Rechercher un événement..."
        />
      </div>

      <!-- Event Cards -->
      <div v-if="loading" class="d-flex justify-content-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
      </div>
      <div v-else class="d-flex flex-column gap-4">
        <div
          v-for="event in filteredEvents" :key="event.id"
          class="card border-0 shadow overflow-hidden"
          style="cursor:pointer"
          @click="router.push(`/event/${event.id}`)">

          <!-- Image -->
          <div class="position-relative" style="height:192px">
            <img :src="event.imageUrl" :alt="event.name" class="w-100 h-100 object-fit-cover" />
            <div class="position-absolute" style="top:8px;right:8px">
              <span class="badge bg-white text-dark fw-semibold d-inline-flex align-items-center gap-1">
                <Tag style="width:12px;height:12px" />
                {{ event.category }}
              </span>
            </div>
          </div>

          <!-- Content -->
          <div class="card-body d-flex flex-column gap-3">
            <div>
              <h5 class="fw-bold mb-1">{{ event.name }}</h5>
              <p class="small text-muted mb-0" style="-webkit-line-clamp:2;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden">
                {{ event.description }}
              </p>
            </div>

            <div class="d-flex flex-column gap-2 small text-muted">
              <div class="d-flex align-items-center gap-2">
                <Calendar style="width:16px;height:16px" />
                <span>{{ formatDate(event.date) }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <MapPin style="width:16px;height:16px" />
                <span>{{ event.location }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Users style="width:16px;height:16px" />
                <span>{{ event.currentVolunteers }}/{{ event.totalVolunteersNeeded }} bénévoles • {{ event.missionsCount }} missions</span>
              </div>
            </div>

            <!-- Progress -->
            <div>
              <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Progression</span>
                <span>{{ completionPct(event) }}%</span>
              </div>
              <div class="progress" style="height:8px">
                <div class="progress-bar"
                  :style="`width:${completionPct(event)}%;background:linear-gradient(to right,#d4e645,#a3c200)`">
                </div>
              </div>
            </div>

            <p class="x-small text-muted mb-0">Organisé par {{ event.organizer }}</p>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="!loading && filteredEvents.length === 0" class="text-center py-5 text-muted">
        Aucun événement trouvé
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Search, MapPin, Users, Calendar, Tag } from 'lucide-vue-next'
import { eventService } from '@/services/eventService'

const router = useRouter()
const searchQuery = ref('')
const loading = ref(false)
const events = ref([])

onMounted(async () => {
  loading.value = true
  try {
    const res = await eventService.getAll()
    events.value = res
  } catch (e) {
    console.error('Erreur chargement événements:', e)
  } finally {
    loading.value = false
  }
})

const filteredEvents = computed(() => {
  const q = searchQuery.value.toLowerCase()
  if (!q) return events.value
  return events.value.filter(e =>
    [e.name, e.description, e.location].some(f =>
      (f || '').toLowerCase().includes(q)
    )
  )
})

const completionPct = (event) => {
  if (!event.totalVolunteersNeeded) return 0
  return Math.round((event.currentVolunteers / event.totalVolunteersNeeded) * 100)
}

const formatDate = (date) =>
  new Date(date).toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
</script>