<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center gap-3">
        <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
          <ArrowLeft style="width:24px;height:24px" />
        </button>
        <h1 class="fs-5 fw-semibold mb-0">Créer une Mission</h1>
      </div>
    </header>

    <div class="p-3 mx-auto" style="max-width:768px">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Informations de la mission</h5>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleSubmit" class="d-flex flex-column gap-3">

            <div>
              <label class="form-label small fw-medium">Titre de la mission *</label>
              <input v-model="formData.name" class="form-control" placeholder="Ex: Poste de Secours Zone A" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Événement associé *</label>
              <select v-model="formData.eventId" class="form-select" required @change="handleEventChange">
                <option value="">Sélectionnez un événement...</option>
                <option v-for="event in futureEvents" :key="event.id" :value="event.id">
                  {{ event.name }} - {{ new Date(event.startDate || event.date).toLocaleDateString('fr-FR') }}
                </option>
              </select>
              <div v-if="isLoadingEvents" class="text-muted small mt-1">Chargement des événements...</div>
              <div v-else-if="eventsError" class="text-danger small mt-1">{{ eventsError }}</div>
              <div v-if="futureEvents.length === 0" class="text-warning small mt-1">
                Aucun événement futur disponible
              </div>
            </div>

            <div>
              <label class="form-label small fw-medium">Type de mission *</label>
              <select v-model="formData.type" class="form-select" required>
                <option value="">Sélectionner...</option>
                <option>Secours</option>
                <option>Logistique</option>
                <option>Accueil</option>
                <option>Technique</option>
                <option>Animation</option>
                <option>Autre</option>
              </select>
            </div>

            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Date *</label>
                <input v-model="formData.date" type="date" class="form-control" required />
                <div v-if="dateRangeError" class="text-danger small mt-1">{{ dateRangeError }}</div>
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Nombre de places *</label>
                <input v-model="formData.maxVolunteers" type="number" min="1" class="form-control" placeholder="Ex: 10" required />
                <div v-if="remainingEventPlaces !== null" class="small mt-1" :class="quotaError ? 'text-danger' : 'text-muted'">
                  Places restantes pour l'événement: {{ remainingEventPlaces }}
                </div>
                <div v-if="quotaError" class="text-danger small mt-1">{{ quotaError }}</div>
              </div>
            </div>

            <div>
              <label class="form-label small fw-medium">Quota de bénévoles de backup</label>
              <input v-model="formData.backupVolunteers" type="number" min="0" class="form-control" placeholder="Ex: 2" />
              <div class="form-text">Nombre de bénévoles en réserve en cas d'absence</div>
            </div>

            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Heure de début *</label>
                <input v-model="formData.startTime" type="time" class="form-control" required />
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Heure de fin *</label>
                <input v-model="formData.endTime" type="time" class="form-control" required />
              </div>
            </div>

            <div>
              <label class="form-label small fw-medium">Localisation *</label>
              <input v-model="formData.location" class="form-control" placeholder="Ex: Place Neuve, Genève" required />
            </div>

            <!-- Géolocalisation -->
            <div class="bg-light border rounded p-3 d-flex flex-column gap-3">
              <div class="d-flex align-items-center justify-content-between">
                <label class="form-label small fw-medium mb-0 d-flex align-items-center gap-2">
                  <MapPin style="width:16px;height:16px" /> Position GPS exacte
                </label>
                <button type="button" class="btn btn-outline-secondary btn-sm" @click="getCurrentLocation">
                  Utiliser ma position
                </button>
              </div>
              <div class="row g-3">
                <div class="col-6">
                  <label class="form-label x-small text-muted">Latitude</label>
                  <input v-model="formData.latitude" type="number" step="any" class="form-control form-control-sm" placeholder="Ex: 46.2044" />
                </div>
                <div class="col-6">
                  <label class="form-label x-small text-muted">Longitude</label>
                  <input v-model="formData.longitude" type="number" step="any" class="form-control form-control-sm" placeholder="Ex: 6.1432" />
                </div>
              </div>
              <div v-if="formData.latitude && formData.longitude" class="bg-white border rounded p-2">
                <p class="small text-muted mb-2">Aperçu sur la carte :</p>
                <a
                  :href="`https://www.google.com/maps?q=${formData.latitude},${formData.longitude}`"
                  target="_blank"
                  class="small text-primary d-flex align-items-center gap-1 mb-2"
                >
                  <MapPin style="width:12px;height:12px" /> Voir sur Google Maps
                </a>
                <iframe
                  width="100%" height="200" frameborder="0" style="border:0;border-radius:4px"
                  :src="`https://www.google.com/maps?q=${formData.latitude},${formData.longitude}&output=embed`"
                  allowfullscreen
                />
              </div>
            </div>

            <!-- Photos -->
            <div class="bg-light border rounded p-3 d-flex flex-column gap-2">
              <label class="form-label small fw-medium mb-0 d-flex align-items-center gap-2">
                <Upload style="width:16px;height:16px" /> Photos du lieu (optionnel)
              </label>
              <p class="small text-muted mb-0">Ajoutez des photos pour aider les bénévoles à localiser le lieu</p>
              <div class="row g-2">
                <div v-for="(photo, index) in photos" :key="index" class="col-4 position-relative">
                  <img :src="getPhotoUrl(photo)" :alt="`Photo ${index + 1}`" class="w-100 rounded object-fit-cover" style="height:96px" />
                  <button
                    type="button"
                    class="btn btn-danger btn-sm position-absolute top-0 end-0 p-1 rounded-circle m-1"
                    style="width:22px;height:22px;line-height:1"
                    @click="removePhoto(index)"
                  >
                    <X style="width:10px;height:10px" />
                  </button>
                </div>
                <div v-if="photos.length < 6" class="col-4">
                  <label class="d-flex flex-column align-items-center justify-content-center border border-2 border-dashed rounded cursor-pointer w-100" style="height:96px;cursor:pointer">
                    <input type="file" accept="image/*" multiple class="d-none" @change="handlePhotoUpload" />
                    <Upload style="width:24px;height:24px" class="text-muted mb-1" />
                    <span class="small text-muted">Ajouter</span>
                  </label>
                </div>
              </div>
              <p v-if="photos.length > 0" class="small text-muted mb-0">
                {{ photos.length }} photo(s) ajoutée(s) • Maximum 6 photos
              </p>
            </div>

            <div>
              <label class="form-label small fw-medium">Description *</label>
              <textarea v-model="formData.description" class="form-control" rows="4" placeholder="Décrivez la mission en détail..." required style="resize:none" />
            </div>

            <!-- Compétences -->
            <div class="bg-light border rounded p-3">
              <label class="form-label small fw-medium">Compétences requises</label>
              <div v-if="isLoadingCompetences" class="text-muted small mb-2">Chargement des compétences...</div>
              <div v-else-if="competencesError" class="text-danger small mb-2">{{ competencesError }}</div>
              <div class="row g-2">
                <div v-for="skill in competencesList" :key="skill.id" class="col-6 d-flex align-items-center gap-2">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    :id="`skill-${skill.id}`"
                    :checked="formData.competenceIds.includes(skill.id)"
                    @change="toggleSkill(skill.id)"
                  />
                  <label :for="`skill-${skill.id}`" class="form-check-label small" style="cursor:pointer">
                    {{ skill.name }}
                  </label>
                </div>
              </div>
              <p v-if="selectedSkillsLabels.length > 0" class="small text-muted mt-2 mb-0">
                Sélectionnées : {{ selectedSkillsLabels.join(', ') }}
              </p>
            </div>

            <!-- Responsable -->
            <div class="bg-light border rounded p-3 d-flex flex-column gap-3">
              <h6 class="fw-medium mb-0">Responsable de la mission</h6>
              <div>
                <label class="form-label small fw-medium">Nom du responsable *</label>
                <select v-model="formData.responsibleUserId" class="form-select" required>
                  <option value="">Sélectionner un responsable...</option>
                  <option v-for="responsible in responsiblesList" :key="responsible.id" :value="responsible.id">
                    {{ responsible.firstName }} {{ responsible.lastName }}
                  </option>
                </select>
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
            <div class="bg-light border rounded p-3 d-flex flex-column gap-3">
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

            <div class="d-flex gap-3 pt-2">
              <button type="button" class="btn btn-outline-secondary flex-fill" @click="router.go(-1)">Annuler</button>
              <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2" :disabled="isSubmitting || isLoadingEvents">
                <Save style="width:16px;height:16px" />
                {{ isSubmitting ? 'Création...' : 'Créer la mission' }}
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Save, MapPin, Upload, X } from 'lucide-vue-next'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import eventService from '@/services/eventService'
import missionService from '@/services/missionService'
import competenceService from '@/services/competenceService'
import userService from '@/services/userService'

const router = useRouter()
const user = getCurrentUser()
if (!user || !hasMinRole('organizer')) router.push('/')

const eventsList = ref([])
const eventMissions = ref([])
const competencesList = ref([])
const responsiblesList = ref([])
const isSubmitting = ref(false)
const isLoadingEvents = ref(true)
const isLoadingCompetences = ref(true)
const isLoadingResponsibles = ref(true)
const eventsError = ref('')
const competencesError = ref('')
const responsiblesError = ref('')

const formData = reactive({
  name: '', eventId: '', date: '', startTime: '', endTime: '',
  location: '', description: '', type: '', maxVolunteers: '',
  backupVolunteers: '', responsibleUserId: '', responsiblePhone: '',
  responsibleEmail: '', latitude: '', longitude: '',
  postable: true, inscription: true, public: true,
  competenceIds: [],
})

const photos = ref([])

const switchOptions = [
  { key: 'postable',    label: 'Postable',           desc: 'Les bénévoles peuvent postuler' },
  { key: 'inscription', label: 'Inscription requise', desc: 'Inscription obligatoire pour participer' },
  { key: 'public',      label: 'Mission publique',    desc: 'Visible par tous les bénévoles' },
]

const futureEvents = computed(() => {
  const today = new Date(); today.setHours(0, 0, 0, 0)
  return eventsList.value.filter(e => new Date(e.startDate || e.date) >= today)
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

const usedEventPlaces = computed(() =>
  eventMissions.value.reduce((sum, mission) => sum + Number(mission.maxVolunteers || 0), 0)
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

const handleEventChange = () => {
  const selected = eventsList.value.find(e => e.id === formData.eventId)
  if (selected) {
    formData.date = selected.startDate || selected.date || ''
    formData.location = selected.location || ''
    formData.latitude = selected.latitude || ''
    formData.longitude = selected.longitude || ''
  }
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

const handlePhotoUpload = (e) => {
  const files = Array.from(e.target.files || [])
  photos.value = [...photos.value, ...files].slice(0, 6)
}

const removePhoto = (index) => { photos.value.splice(index, 1) }
const getPhotoUrl = (file) => URL.createObjectURL(file)

const getCurrentLocation = () => {
  if (!navigator.geolocation) return alert("Géolocalisation non supportée")
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      formData.latitude = pos.coords.latitude.toString()
      formData.longitude = pos.coords.longitude.toString()
      alert('Position actuelle détectée !')
    },
    (err) => alert('Impossible de récupérer votre position : ' + err.message)
  )
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

const handleSubmit = async () => {
  if (dateRangeError.value) {
    alert(dateRangeError.value)
    return
  }

  if (quotaError.value) {
    alert(quotaError.value)
    return
  }

  isSubmitting.value = true

  try {
    await missionService.create(formData)
    alert('Mission créée avec succès !')
    router.push('/manage-missions')
  } catch (error) {
    alert(error.message || 'Erreur lors de la création de la mission')
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

onMounted(async () => {
  await Promise.all([loadEvents(), loadCompetences(), loadResponsibles()])
})
</script>