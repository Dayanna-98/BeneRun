<template>
  <div class="min-vh-100 bg-light pb-5">

    <div v-if="isLoading" class="min-vh-100 d-flex align-items-center justify-content-center">
      <p class="text-muted mb-0">Chargement de la mission...</p>
    </div>

    <!-- Not found -->
    <div v-else-if="!mission" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-muted mb-3">Mission non trouvée</p>
        <button class="btn btn-primary" @click="router.push('/manage-missions')">Retour à la gestion</button>
      </div>
    </div>

    <template v-else>
      <!-- Header -->
      <header class="bg-white border-bottom sticky-top">
        <div class="p-3 d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Modifier la Mission</h1>
        </div>
      </header>

      <div class="p-3 mx-auto" style="max-width:768px">
        <div class="card mission-form-card shadow-sm border-0">
          <div class="card-header mission-form-header">
            <h5 class="card-title mb-1">Informations de la mission</h5>
            <p class="mb-0 small text-muted">Mettez a jour rapidement la mission avec les presets horaires et sections colorees.</p>
          </div>
          <div class="card-body mission-form-body">
            <form @submit.prevent="handleSubmit" class="d-flex flex-column gap-3">

              <div>
                <label class="form-label small fw-medium">Titre de la mission *</label>
                <input v-model="formData.name" class="form-control" :class="fieldErrors.name ? 'is-invalid' : ''" placeholder="Ex: Poste de Secours Zone A" required />
                <div v-if="fieldErrors.name" class="invalid-feedback d-block">{{ fieldErrors.name }}</div>
              </div>

              <div>
                <label class="form-label small fw-medium">Événement associé *</label>
                <select v-model="formData.eventId" class="form-select" :class="fieldErrors.eventId ? 'is-invalid' : ''" required @change="handleEventChange">
                  <option value="">Sélectionnez un événement...</option>
                  <option v-for="event in futureEvents" :key="event.id" :value="event.id">
                    {{ event.name }} - {{ new Date(event.startDate || event.date).toLocaleDateString('fr-FR') }}
                  </option>
                </select>
                <div v-if="fieldErrors.eventId" class="invalid-feedback d-block">{{ fieldErrors.eventId }}</div>
                <div v-if="isLoadingEvents" class="text-muted small mt-1">Chargement des événements...</div>
                <div v-else-if="eventsError" class="text-danger small mt-1">{{ eventsError }}</div>
                <div v-if="futureEvents.length === 0" class="text-warning small mt-1">
                  Aucun événement futur disponible
                </div>
              </div>

              <div>
                <label class="form-label small fw-medium">Type de mission *</label>
                <select v-model="formData.type" class="form-select" :class="fieldErrors.type ? 'is-invalid' : ''" required>
                  <option value="">Sélectionner...</option>
                  <option>Secours</option>
                  <option>Logistique</option>
                  <option>Accueil</option>
                  <option>Technique</option>
                  <option>Animation</option>
                  <option>Autre</option>
                </select>
                <div v-if="fieldErrors.type" class="invalid-feedback d-block">{{ fieldErrors.type }}</div>
              </div>

              <div class="row g-3">
                <div class="col-6">
                  <label class="form-label small fw-medium">Date *</label>
                  <input v-model="formData.date" type="date" class="form-control" :class="fieldErrors.date ? 'is-invalid' : ''" required />
                  <div v-if="fieldErrors.date" class="invalid-feedback d-block">{{ fieldErrors.date }}</div>
                  <div v-if="dateRangeError" class="text-danger small mt-1">{{ dateRangeError }}</div>
                </div>
                <div class="col-6">
                  <label class="form-label small fw-medium">Nombre de places *</label>
                  <input v-model="formData.maxVolunteers" type="number" min="1" class="form-control" :class="fieldErrors.maxVolunteers ? 'is-invalid' : ''" placeholder="Ex: 10" required />
                  <div v-if="fieldErrors.maxVolunteers" class="invalid-feedback d-block">{{ fieldErrors.maxVolunteers }}</div>
                  <div v-if="remainingEventPlaces !== null" class="small mt-1" :class="quotaError ? 'text-danger' : 'text-muted'">
                    Places restantes pour l'événement: {{ remainingEventPlaces }}
                  </div>
                  <div v-if="quotaError" class="text-danger small mt-1">{{ quotaError }}</div>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-6">
                  <label class="form-label small fw-medium">Heure de début *</label>
                  <input v-model="formData.startTime" type="time" class="form-control" :class="fieldErrors.startTime ? 'is-invalid' : ''" required />
                  <div v-if="fieldErrors.startTime" class="invalid-feedback d-block">{{ fieldErrors.startTime }}</div>
                </div>
                <div class="col-6">
                  <label class="form-label small fw-medium">Heure de fin *</label>
                  <input v-model="formData.endTime" type="time" class="form-control" :class="fieldErrors.endTime ? 'is-invalid' : ''" required />
                  <div v-if="fieldErrors.endTime" class="invalid-feedback d-block">{{ fieldErrors.endTime }}</div>
                </div>
              </div>

              <div class="time-helper-box">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                  <span class="small fw-semibold text-secondary">Créneaux rapides</span>
                  <button
                    v-for="preset in timePresets"
                    :key="preset.label"
                    type="button"
                    class="btn btn-sm btn-outline-primary rounded-pill"
                    @click="applyTimePreset(preset)">
                    {{ preset.label }}
                  </button>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                  <span class="badge text-bg-light border">Durée: {{ missionDurationLabel }}</span>
                  <button type="button" class="btn btn-sm btn-outline-secondary" @click="adjustEndTime(-30)">Fin -30 min</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" @click="adjustEndTime(30)">Fin +30 min</button>
                </div>
              </div>

              <div>
                <label class="form-label small fw-medium">Localisation *</label>
                <input v-model="formData.location" class="form-control" :class="fieldErrors.location ? 'is-invalid' : ''" placeholder="Ex: Place Neuve, Genève" required />
                <div v-if="fieldErrors.location" class="invalid-feedback d-block">{{ fieldErrors.location }}</div>
              </div>

              <div class="bg-light border rounded p-3 d-flex flex-column gap-3 section-panel section-panel--maps">
                <div class="d-flex align-items-center justify-content-between">
                  <label class="form-label small fw-medium mb-0 d-flex align-items-center gap-2">
                    <MapPin style="width:16px;height:16px" /> Point Google Maps de la mission
                  </label>
                  <button type="button" class="btn btn-outline-secondary btn-sm" @click="getCurrentLocation">
                    Utiliser ma position
                  </button>
                </div>

                <div>
                  <input
                    v-model="formData.googleMapsUrl"
                    class="form-control"
                    :class="fieldErrors.googleMapsUrl ? 'is-invalid' : ''"
                    placeholder="Collez le lien Google Maps du point de mission"
                    required
                  />
                  <div v-if="fieldErrors.googleMapsUrl" class="invalid-feedback d-block">{{ fieldErrors.googleMapsUrl }}</div>
                </div>

                <LeafletPreview
                  :maps-url="formData.googleMapsUrl"
                  link-label="Ouvrir le point de mission dans Google Maps"
                />

                <div v-if="selectedEvent?.googleMapsUrl" class="bg-white border rounded p-3 d-flex flex-column gap-2">
                  <div class="small fw-medium">Périmètre de l'événement sélectionné</div>
                  <LeafletPreview
                    :maps-url="selectedEvent.googleMapsUrl"
                    :radius-meters="selectedEvent.radiusMeters"
                    link-label="Ouvrir le périmètre de l'événement dans Google Maps"
                  />
                </div>

                <div v-if="locationPerimeterError" class="text-danger small">{{ locationPerimeterError }}</div>
              </div>

              <div>
                <label class="form-label small fw-medium">Visuel principal</label>
                <input type="file" accept="image/*" class="form-control mb-2" :class="fieldErrors.imageFile ? 'is-invalid' : ''" @change="handleMainImageChange" />
                <div v-if="fieldErrors.imageFile" class="invalid-feedback d-block">{{ fieldErrors.imageFile }}</div>
                <div v-if="mainImagePreviewUrl || formData.imageUrl" class="mb-2">
                  <img :src="mainImagePreviewUrl || formData.imageUrl" alt="Visuel principal" class="w-100 rounded object-fit-cover" style="height:140px" />
                </div>
              </div>

              <div>
                <label class="form-label small fw-medium">Description *</label>
                <textarea v-model="formData.description" class="form-control" :class="fieldErrors.description ? 'is-invalid' : ''" rows="4" placeholder="Décrivez la mission en détail..." required style="resize:none" />
                <div v-if="fieldErrors.description" class="invalid-feedback d-block">{{ fieldErrors.description }}</div>
              </div>

              <!-- Compétences -->
              <div class="bg-light border rounded p-3 section-panel section-panel--skills">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                  <label class="form-label small fw-medium mb-0">Compétences requises</label>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    @click="isSkillsDropdownOpen = !isSkillsDropdownOpen">
                    {{ isSkillsDropdownOpen ? 'Fermer la liste' : 'Ouvrir la liste' }}
                  </button>
                </div>
                <div v-if="isLoadingCompetences" class="text-muted small mb-2">Chargement des compétences...</div>
                <div v-else-if="competencesError" class="text-danger small mb-2">{{ competencesError }}</div>

                <div class="mb-2">
                  <input
                    v-model="competencesSearch"
                    type="text"
                    class="form-control form-control-sm"
                    placeholder="Rechercher une compétence..."
                    @focus="isSkillsDropdownOpen = true"
                  />
                </div>

                <div v-if="isSkillsDropdownOpen" class="skills-dropdown border rounded bg-white mb-2">
                  <button
                    v-for="skill in filteredCompetences"
                    :key="skill.id"
                    type="button"
                    class="skills-option"
                    @click="toggleSkill(skill.id)">
                    <span>{{ skill.name }}</span>
                    <span v-if="formData.competenceIds.includes(skill.id)" class="badge rounded-pill text-bg-success">Ajoutée</span>
                  </button>
                  <div v-if="filteredCompetences.length === 0" class="small text-muted p-2">
                    Aucune compétence trouvée.
                  </div>
                </div>

                <div v-if="suggestedSkillsForType.length > 0" class="suggestion-box mb-2">
                  <div class="small fw-semibold mb-1">Suggestions pour le type {{ formData.type }}</div>
                  <div class="d-flex flex-wrap gap-1 mb-2">
                    <span
                      v-for="skill in suggestedSkillsForType"
                      :key="`suggested-${skill.id}`"
                      class="badge rounded-pill text-bg-light border">
                      {{ skill.name }}
                    </span>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-success" @click="addSuggestedSkills">
                    Ajouter les suggestions
                  </button>
                </div>

                <div v-if="selectedSkills.length > 0" class="d-flex flex-wrap gap-1">
                  <button
                    v-for="skill in selectedSkills"
                    :key="`selected-${skill.id}`"
                    type="button"
                    class="skill-chip"
                    @click="removeSkill(skill.id)">
                    {{ skill.name }} ×
                  </button>
                </div>
                <p v-else class="small text-muted mb-0">Aucune compétence sélectionnée.</p>
              </div>

              <!-- Responsable -->
              <div class="bg-light border rounded p-3 d-flex flex-column gap-3 section-panel section-panel--owner">
                <h6 class="fw-medium mb-0">Responsable de la mission</h6>
                <div>
                  <label class="form-label small fw-medium">Nom du responsable *</label>
                  <select v-model="formData.responsibleUserId" class="form-select" :class="fieldErrors.responsibleUserId ? 'is-invalid' : ''" required>
                    <option value="">Sélectionner un responsable...</option>
                    <option v-for="responsible in responsiblesList" :key="responsible.id" :value="responsible.id">
                      {{ responsible.firstName }} {{ responsible.lastName }}
                    </option>
                  </select>
                  <div v-if="fieldErrors.responsibleUserId" class="invalid-feedback d-block">{{ fieldErrors.responsibleUserId }}</div>
                  <div v-if="isLoadingResponsibles" class="text-muted small mt-1">Chargement des responsables...</div>
                  <div v-else-if="responsiblesError" class="text-danger small mt-1">{{ responsiblesError }}</div>
                </div>
                <div class="row g-3">
                  <div class="col-6">
                    <label class="form-label small fw-medium">Téléphone *</label>
                    <input v-model="formData.responsiblePhone" type="tel" class="form-control" placeholder="+41 79 123 45 67" readonly required />
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-medium">Email *</label>
                    <input v-model="formData.responsibleEmail" type="email" class="form-control" placeholder="email@example.ch" readonly required />
                  </div>
                </div>
              </div>

              <!-- Options -->
              <div class="bg-light border rounded p-3 d-flex flex-column gap-3 section-panel section-panel--options">
                <h6 class="fw-medium mb-0">Options de la mission</h6>
                <div v-for="opt in switchOptions" :key="opt.key" class="d-flex align-items-center justify-content-between">
                  <div>
                    <div class="small fw-medium">{{ opt.label }}</div>
                    <div class="x-small text-muted">{{ opt.desc }}</div>
                  </div>
                  <div class="form-check form-switch mb-0">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      role="switch"
                      :checked="formData[opt.key]"
                      style="width:2rem;height:1.15rem;cursor:pointer"
                      @change="formData[opt.key] = $event.target.checked"
                    />
                  </div>
                </div>
              </div>

              <div v-if="submitError" class="alert alert-danger mb-0">{{ submitError }}</div>

              <div class="d-flex gap-3 pt-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" @click="router.go(-1)">Annuler</button>
                <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2" :disabled="isSubmitting || isLoadingEvents">
                  <Save style="width:16px;height:16px" />
                  {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer les modifications' }}
                </button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, MapPin, Save } from 'lucide-vue-next'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import LeafletPreview from '@/components/maps/LeafletPreview.vue'
import eventService from '@/services/eventService'
import missionService from '@/services/missionService'
import competenceService from '@/services/competenceService'
import userService from '@/services/userService'
import { distanceInMeters, extractGoogleMapsCoordinates, formatRadius } from '@/utils/googleMaps'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const route = useRoute()
const user = getCurrentUser()
if (!user || !hasMinRole('organizer')) router.push('/')
const toast = useToast()

const mission = ref(null)
const eventsList = ref([])
const eventMissions = ref([])
const competencesList = ref([])
const responsiblesList = ref([])
const isLoading = ref(true)
const isSubmitting = ref(false)
const isLoadingEvents = ref(true)
const isLoadingCompetences = ref(true)
const isLoadingResponsibles = ref(true)
const eventsError = ref('')
const competencesError = ref('')
const responsiblesError = ref('')
const fieldErrors = ref({})
const submitError = ref('')
const mainImagePreviewUrl = ref('')
const competencesSearch = ref('')
const isSkillsDropdownOpen = ref(false)

const timePresets = [
  { label: 'Matin', start: '08:00', end: '12:00' },
  { label: 'Après-midi', start: '13:00', end: '17:00' },
  { label: 'Soir', start: '18:00', end: '22:00' },
  { label: 'Journée', start: '09:00', end: '18:00' },
]

const formData = reactive({
  name: '',
  eventId: '',
  date: '',
  startTime: '',
  endTime: '',
  location: '',
  description: '',
  type: '',
  maxVolunteers: '',
  backupVolunteers: '0',
  responsibleUserId: '',
  responsiblePhone: '',
  responsibleEmail: '',
  postable: true,
  public: true,
  googleMapsUrl: '',
  status: 'À venir',
  imageFile: null,
  imageUrl: '',
  safetyInstructions: '',
  photoFiles: [],
  competenceIds: [],
})

const switchOptions = [
  { key: 'postable',    label: 'Postable',           desc: 'Les bénévoles peuvent postuler' },
  { key: 'public',      label: 'Mission publique',    desc: 'Visible par tous les bénévoles' },
]

const futureEvents = computed(() => {
  const today = new Date(); today.setHours(0, 0, 0, 0)
  return eventsList.value.filter((e) => {
    if (String(e.id) === String(formData.eventId)) return true
    return new Date(e.startDate || e.date) >= today
  })
})

const selectedEvent = computed(() =>
  eventsList.value.find((e) => String(e.id) === String(formData.eventId)) || null
)

const selectedResponsible = computed(() =>
  responsiblesList.value.find((u) => String(u.id) === String(formData.responsibleUserId)) || null
)

const selectedSkillsLabels = computed(() =>
  competencesList.value
    .filter((skill) => formData.competenceIds.includes(skill.id))
    .map((skill) => skill.name)
)

const selectedSkills = computed(() =>
  competencesList.value.filter((skill) => formData.competenceIds.includes(skill.id))
)

const filteredCompetences = computed(() => {
  const query = competencesSearch.value.trim().toLowerCase()
  if (!query) return competencesList.value
  return competencesList.value.filter((skill) => skill.name.toLowerCase().includes(query))
})

const suggestedSkillsForType = computed(() => {
  const type = String(formData.type || '').trim().toLowerCase()
  if (!type) return []

  return competencesList.value.filter((skill) =>
    Array.isArray(skill.missionTypes) && skill.missionTypes.includes(type)
  )
})

const usedEventPlaces = computed(() =>
  eventMissions.value
    .filter((m) => String(m.id) !== String(mission.value?.id || ''))
    .reduce((sum, m) => sum + Number(m.maxVolunteers || 0), 0)
)

const remainingEventPlaces = computed(() => {
  if (!selectedEvent.value) return null
  return Number(selectedEvent.value.totalVolunteersNeeded || 0) - usedEventPlaces.value
})

const dateRangeError = computed(() => {
  if (!selectedEvent.value || !formData.date) return ''
  const missionDate = formData.date
  const startDate = String(selectedEvent.value.startDate || '').slice(0, 10)
  const endDate = String(selectedEvent.value.endDate || selectedEvent.value.startDate || '').slice(0, 10)
  if (!startDate || !endDate) return ''

  if (missionDate < startDate || missionDate > endDate) {
    return `La date de mission doit être comprise entre ${startDate} et ${endDate}.`
  }

  return ''
})

const quotaError = computed(() => {
  if (remainingEventPlaces.value === null || !formData.maxVolunteers) return ''
  const requested = Number(formData.maxVolunteers)
  if (requested > remainingEventPlaces.value) {
    return `Le quota de l'événement est dépassé. Maximum autorisé: ${Math.max(remainingEventPlaces.value, 0)}.`
  }

  return ''
})

const locationPerimeterError = computed(() => {
  if (!selectedEvent.value?.googleMapsUrl || !selectedEvent.value?.radiusMeters || !formData.googleMapsUrl) {
    return ''
  }

  const eventCoordinates = extractGoogleMapsCoordinates(selectedEvent.value.googleMapsUrl)
  const missionCoordinates = extractGoogleMapsCoordinates(formData.googleMapsUrl)
  if (!eventCoordinates || !missionCoordinates) return ''

  const distance = distanceInMeters(eventCoordinates, missionCoordinates)
  if (distance === null || distance <= Number(selectedEvent.value.radiusMeters || 0)) {
    return ''
  }

  return `La mission est hors périmètre. Distance estimée: ${Math.round(distance)} m, périmètre autorisé: ${formatRadius(selectedEvent.value.radiusMeters)}.`
})

const missionDurationLabel = computed(() => {
  if (!formData.startTime || !formData.endTime) return 'Non définie'

  const [startHour, startMinute] = formData.startTime.split(':').map(Number)
  const [endHour, endMinute] = formData.endTime.split(':').map(Number)
  if ([startHour, startMinute, endHour, endMinute].some(Number.isNaN)) return 'Non définie'

  let duration = (endHour * 60 + endMinute) - (startHour * 60 + startMinute)
  if (duration <= 0) duration += 24 * 60

  const hours = Math.floor(duration / 60)
  const minutes = duration % 60
  if (!hours) return `${minutes} min`
  if (!minutes) return `${hours}h`
  return `${hours}h${String(minutes).padStart(2, '0')}`
})

const handleEventChange = () => {
  const selected = eventsList.value.find(e => e.id === formData.eventId)
  if (selected) {
    formData.date = formData.date || selected.startDate || selected.date || ''
    formData.location = selected.location || formData.location
  }
}

const applyTimePreset = (preset) => {
  formData.startTime = preset.start
  formData.endTime = preset.end
}

const adjustEndTime = (minutesDelta) => {
  if (!formData.startTime && !formData.endTime) return
  const base = formData.endTime || formData.startTime
  if (!base) return

  const [hour, minute] = base.split(':').map(Number)
  if (Number.isNaN(hour) || Number.isNaN(minute)) return

  let totalMinutes = (hour * 60) + minute + minutesDelta
  const dayMinutes = 24 * 60
  totalMinutes = ((totalMinutes % dayMinutes) + dayMinutes) % dayMinutes

  const h = String(Math.floor(totalMinutes / 60)).padStart(2, '0')
  const m = String(totalMinutes % 60).padStart(2, '0')
  formData.endTime = `${h}:${m}`
}

const getCurrentLocation = () => {
  if (!navigator.geolocation) return alert('Géolocalisation non supportée')

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      formData.googleMapsUrl = `https://www.google.com/maps?q=${pos.coords.latitude},${pos.coords.longitude}`
      alert('Position actuelle détectée !')
    },
    (err) => alert('Impossible de récupérer votre position : ' + err.message)
  )
}

const toggleSkill = (competenceId) => {
  const numericId = Number(competenceId)
  if (Number.isNaN(numericId)) return

  const current = formData.competenceIds
  const index = current.indexOf(numericId)
  if (index === -1) {
    current.push(numericId)
  } else {
    current.splice(index, 1)
  }
}

const removeSkill = (competenceId) => {
  const numericId = Number(competenceId)
  if (Number.isNaN(numericId)) return
  formData.competenceIds = formData.competenceIds.filter((id) => id !== numericId)
}

const addSuggestedSkills = () => {
  for (const skill of suggestedSkillsForType.value) {
    if (!formData.competenceIds.includes(skill.id)) {
      formData.competenceIds.push(skill.id)
    }
  }
}

const handleMainImageChange = (event) => {
  const file = event.target.files?.[0] || null
  formData.imageFile = file
  mainImagePreviewUrl.value = file ? URL.createObjectURL(file) : ''
}

const validateForm = () => {
  const errors = missionService.validateFormData(formData)
  if (dateRangeError.value) errors.date = dateRangeError.value
  if (quotaError.value) errors.maxVolunteers = quotaError.value
  if (locationPerimeterError.value) errors.googleMapsUrl = locationPerimeterError.value
  fieldErrors.value = errors
  return Object.keys(errors).length === 0
}

const loadEvents = async () => {
  isLoadingEvents.value = true
  eventsError.value = ''

  try {
    eventsList.value = await eventService.getAll()
  } catch (error) {
    eventsError.value = error.message || 'Impossible de charger les événements'
  } finally {
    isLoadingEvents.value = false
  }
}

const loadCompetences = async () => {
  isLoadingCompetences.value = true
  competencesError.value = ''

  try {
    const rows = await competenceService.getAll()
    competencesList.value = Array.isArray(rows)
      ? rows.map((row) => ({
        id: Number(row.id_competence),
        name: row.nom_competence,
        missionTypes: Array.isArray(row.types_mission_suggeres)
          ? row.types_mission_suggeres.map((t) => String(t).toLowerCase())
          : [],
      }))
      : []
  } catch (error) {
    competencesError.value = error.message || 'Impossible de charger les compétences'
  } finally {
    isLoadingCompetences.value = false
  }
}

const loadResponsibles = async () => {
  isLoadingResponsibles.value = true
  responsiblesError.value = ''

  try {
    const users = await userService.getAll()
    responsiblesList.value = (Array.isArray(users) ? users : [])
      .filter((u) => ['mission_manager', 'admin', 'superadmin', 'organizer'].includes(u.role))
  } catch (error) {
    responsiblesError.value = error.message || 'Impossible de charger les responsables'
  } finally {
    isLoadingResponsibles.value = false
  }
}

const loadEventMissions = async () => {
  if (!formData.eventId) {
    eventMissions.value = []
    return
  }

  try {
    eventMissions.value = await missionService.getAll({ id_evenement: formData.eventId })
  } catch {
    eventMissions.value = []
  }
}

const loadMission = async () => {
  isLoading.value = true

  try {
    const loadedMission = await missionService.getById(route.params.id)
    mission.value = loadedMission

    formData.name = loadedMission.name || ''
    formData.eventId = loadedMission.eventId || ''
    formData.date = loadedMission.date || ''
    formData.startTime = loadedMission.startTime || ''
    formData.endTime = loadedMission.endTime || ''
    formData.location = loadedMission.location || ''
    formData.description = loadedMission.description || ''
    formData.type = loadedMission.type || ''
    formData.maxVolunteers = loadedMission.maxVolunteers ? String(loadedMission.maxVolunteers) : ''
    formData.backupVolunteers = String(loadedMission.backupVolunteers || 0)
    formData.responsibleUserId = loadedMission.responsibleUserId || ''
    formData.responsiblePhone = loadedMission.responsiblePhone || ''
    formData.responsibleEmail = loadedMission.responsibleEmail || ''
    formData.googleMapsUrl = loadedMission.googleMapsUrl || ''
    formData.postable = loadedMission.postable !== false
    formData.public = loadedMission.public
    formData.status = loadedMission.status || 'À venir'
    formData.imageUrl = loadedMission.imageUrl || ''
    formData.safetyInstructions = loadedMission.safetyInstructions || ''
    formData.competenceIds = loadedMission.competenceIds || []

    await loadEventMissions()
  } catch {
    mission.value = null
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  if (!mission.value) return
  submitError.value = ''
  if (!validateForm()) return

  isSubmitting.value = true

  try {
    await missionService.update(mission.value.id, formData)
    toast.success('Mission mise à jour avec succès.')
    router.push('/manage-missions')
  } catch (error) {
    submitError.value = error.message || 'Erreur lors de la mise à jour de la mission'
  } finally {
    isSubmitting.value = false
  }
}

watch(() => formData.eventId, async () => {
  handleEventChange()
  await loadEventMissions()
})

watch(selectedResponsible, (responsible) => {
  formData.responsiblePhone = responsible?.phone || ''
  formData.responsibleEmail = responsible?.email || ''
})

// Auto-resolve short Google Maps URLs
watch(() => formData.googleMapsUrl, async (newUrl) => {
  if (!newUrl || !String(newUrl).includes('maps.app.goo.gl')) return
  
  try {
    const resolved = await missionService.resolveMapsUrl(newUrl)
    if (resolved && resolved !== newUrl) {
      formData.googleMapsUrl = resolved
    }
  } catch (error) {
    console.warn('Failed to resolve maps URL:', error.message)
    // Keep the original URL if resolution fails
  }
})

onMounted(async () => {
  await Promise.all([loadEvents(), loadCompetences(), loadResponsibles()])
  await loadMission()
})

watch(formData, () => {
  if (Object.keys(fieldErrors.value).length > 0) validateForm()
}, { deep: true })

watch(() => formData.type, () => {
  if (suggestedSkillsForType.value.length > 0) {
    isSkillsDropdownOpen.value = true
  }
})
</script>

<style scoped>
.mission-form-card {
  border-radius: 1rem;
}

.mission-form-header {
  border-bottom: 1px solid #e6edf2;
  background: linear-gradient(135deg, #f6fbff 0%, #f9f7ff 100%);
}

.mission-form-body {
  background: #fcfdff;
}

.time-helper-box {
  border: 1px dashed #c7d9f8;
  border-radius: 0.75rem;
  background: #f4f8ff;
  padding: 0.75rem;
}

.section-panel {
  border-color: #dfe8ef !important;
  background: #f8fafc !important;
}

.section-panel--maps {
  border-left: 4px solid #2b7fff;
}

.section-panel--skills {
  border-left: 4px solid #22a06b;
}

.skills-dropdown {
  max-height: 210px;
  overflow: auto;
}

.skills-option {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  border: none;
  border-bottom: 1px solid #eef2f7;
  background: #fff;
  padding: 0.5rem 0.65rem;
  text-align: left;
}

.skills-option:hover {
  background: #f6faff;
}

.suggestion-box {
  border: 1px dashed #a8d5bf;
  border-radius: 0.65rem;
  padding: 0.6rem;
  background: #f3fbf6;
}

.skill-chip {
  border: 1px solid #c6d4ea;
  background: #eef4ff;
  color: #26406b;
  border-radius: 999px;
  padding: 0.2rem 0.55rem;
  font-size: 0.78rem;
}

.section-panel--owner {
  border-left: 4px solid #5f6ad4;
}

.section-panel--options {
  border-left: 4px solid #7a7f87;
}

@media (max-width: 576px) {
  .time-helper-box .btn {
    padding: 0.25rem 0.55rem;
    font-size: 0.78rem;
  }
}
</style>