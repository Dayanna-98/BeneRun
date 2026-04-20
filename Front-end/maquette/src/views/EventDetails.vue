<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Not found -->
    <div v-if="!event" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-muted mb-3">Événement non trouvé</p>
        <button class="btn btn-primary" @click="router.push('/events')">Retour aux événements</button>
      </div>
    </div>

    <template v-else>
      <!-- Header Image -->
      <div class="position-relative" style="height:256px">
        <img :src="event.imageUrl" :alt="event.name" class="w-100 h-100 object-fit-cover" />
        <div class="position-absolute inset-0 w-100 h-100"
          style="background:linear-gradient(to top, rgba(0,0,0,.6), transparent)"></div>

        <button class="btn btn-light position-absolute rounded-circle p-2 shadow"
          style="top:16px;left:16px;opacity:.9"
          @click="router.push('/events')">
          <ArrowLeft style="width:24px;height:24px" />
        </button>

        <div class="position-absolute text-white px-3" style="bottom:16px;left:0;right:0">
          <span class="badge bg-white text-dark fw-semibold mb-2 d-inline-flex align-items-center gap-1">
            <Tag style="width:12px;height:12px" />
            {{ event.category }}
          </span>
          <h1 class="fs-4 fw-bold mb-1">{{ event.name }}</h1>
          <p class="small mb-0 text-white-50">Organisé par {{ event.organizer }}</p>
        </div>
      </div>

      <div class="px-3 pt-3 mx-auto d-flex flex-column gap-3" style="max-width:576px">

        <!-- Info Card -->
        <div class="card">
          <div class="card-body d-flex flex-column gap-3">
            <p class="text-secondary mb-0">{{ event.description }}</p>

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
                <span>{{ event.currentVolunteers }}/{{ event.totalVolunteersNeeded }} bénévoles inscrits</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <Briefcase style="width:16px;height:16px" />
                <span>{{ event.missionsCount }} missions disponibles</span>
              </div>
            </div>

            <!-- Progress -->
            <div>
              <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Progression de l'événement</span>
                <span>{{ completionPercentage }}%</span>
              </div>
              <div class="progress" style="height:10px">
                <div class="progress-bar"
                  :style="`width:${completionPercentage}%;background:linear-gradient(to right,#d4e645,#a3c200)`">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Missions -->
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Missions de l'événement ({{ eventMissions.length }})</h5>
          </div>
          <div class="card-body d-flex flex-column gap-3">
            <div v-if="eventMissions.length === 0" class="text-center text-muted py-4 small">
              Aucune mission disponible pour cet événement
            </div>

            <div v-for="mission in eventMissions" :key="mission.id"
              class="border rounded p-3 cursor-pointer"
              style="cursor:pointer"
              @click="router.push(`/mission/${mission.id}`)">

              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-semibold mb-0">{{ mission.name }}</h6>
                <span v-if="spotsLeft(mission) === 0" class="badge bg-secondary">Complet</span>
              </div>

              <div class="d-flex flex-column gap-1 small text-muted mb-2">
                <div class="d-flex align-items-center gap-2">
                  <Calendar style="width:12px;height:12px" />
                  <span>{{ new Date(mission.date).toLocaleDateString('fr-FR') }} • {{ mission.startTime }} - {{ mission.endTime }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <MapPin style="width:12px;height:12px" />
                  <span>{{ mission.location }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <Users style="width:12px;height:12px" />
                  <span>
                    {{ mission.currentVolunteers }}/{{ mission.maxVolunteers }} bénévoles
                    <span v-if="spotsLeft(mission) > 0 && spotsLeft(mission) <= 3" class="text-warning fw-semibold ms-1">
                      • {{ spotsLeft(mission) }} places restantes
                    </span>
                  </span>
                </div>
              </div>

              <div class="d-flex flex-wrap gap-1 mb-2">
                <span v-for="skill in mission.requiredSkills" :key="skill"
                  :class="['badge', userSkillNames.includes(skill) ? 'text-dark' : 'bg-secondary']"
                  :style="userSkillNames.includes(skill) ? 'background:#d4e645' : ''">
                  {{ skill }}
                </span>
              </div>

              <button
                class="btn btn-primary btn-sm w-100"
                :disabled="!canApplyToMission(mission)"
                @click.stop="router.push(`/mission/${mission.id}`)">
                {{ canApplyToMission(mission) ? "S'inscrire" : 'Voir les détails' }}
              </button>

              <p v-if="!canApplyToMission(mission) && spotsLeft(mission) > 0"
                class="small text-danger text-center mt-2 mb-0">
                Compétences requises manquantes
              </p>
            </div>
          </div>
        </div>

      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, MapPin, Calendar, Users, Tag, Briefcase } from 'lucide-vue-next'
import { events, availableMissions, skills } from '@/data/mockData'

const router = useRouter()
const route = useRoute()

const event = events.find(e => e.id === route.params.id)
const eventMissions = availableMissions.filter(m => m.eventId === route.params.id)
const userSkillNames = skills.map(s => s.name)

const completionPercentage = event
  ? Math.round((event.currentVolunteers / event.totalVolunteersNeeded) * 100)
  : 0

const spotsLeft = (mission) => mission.maxVolunteers - mission.currentVolunteers

const canApplyToMission = (mission) =>
  mission.requiredSkills.every(s => userSkillNames.includes(s)) && spotsLeft(mission) > 0

const formatDate = (date) =>
  new Date(date).toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
</script>