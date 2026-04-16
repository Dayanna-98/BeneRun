<template>
  <div class="min-vh-100 bg-light pb-5">

    <div v-if="isLoading" class="min-vh-100 d-flex align-items-center justify-content-center">
      <p class="text-muted mb-0">Chargement de la mission...</p>
    </div>

    <div v-else-if="loadError" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-danger mb-3">{{ loadError }}</p>
        <button class="btn btn-outline-danger" @click="loadMission">Réessayer</button>
      </div>
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
                    {{ event.name }} - {{ new Date(event.date).toLocaleDateString('fr-FR') }}
                  </option>
                </select>
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
                </div>
                <div class="col-6">
                  <label class="form-label small fw-medium">Nombre de places *</label>
                  <input v-model="formData.maxVolunteers" type="number" min="1" class="form-control" placeholder="Ex: 10" required />
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

              <div>
                <label class="form-label small fw-medium">Description *</label>
                <textarea v-model="formData.description" class="form-control" rows="4" placeholder="Décrivez la mission en détail..." required style="resize:none" />
              </div>

              <!-- Compétences -->
              <div class="bg-light border rounded p-3">
                <label class="form-label small fw-medium">Compétences requises</label>
                <div class="row g-2">
                  <div v-for="skill in availableSkills" :key="skill.id" class="col-6 d-flex align-items-center gap-2">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      :id="`skill-${skill.id}`"
                      :checked="selectedSkills.includes(skill.name)"
                      @change="toggleSkill(skill.name)"
                    />
                    <label :for="`skill-${skill.id}`" class="form-check-label small" style="cursor:pointer">
                      {{ skill.name }}
                    </label>
                  </div>
                </div>
                <p v-if="selectedSkills.length > 0" class="small text-muted mt-2 mb-0">
                  Sélectionnées : {{ selectedSkills.join(', ') }}
                </p>
              </div>

              <!-- Responsable -->
              <div class="bg-light border rounded p-3 d-flex flex-column gap-3">
                <h6 class="fw-medium mb-0">Responsable de la mission</h6>
                <div>
                  <label class="form-label small fw-medium">Nom du responsable *</label>
                  <input v-model="formData.responsibleName" class="form-control" placeholder="Ex: Dr. Laurent Perrin" required />
                </div>
                <div class="row g-3">
                  <div class="col-6">
                    <label class="form-label small fw-medium">Téléphone *</label>
                    <input v-model="formData.responsiblePhone" type="tel" class="form-control" placeholder="+41 79 123 45 67" required />
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-medium">Email *</label>
                    <input v-model="formData.responsibleEmail" type="email" class="form-control" placeholder="email@example.ch" required />
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
                <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2">
                  <Save style="width:16px;height:16px" />
                  Enregistrer les modifications
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
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, Save } from 'lucide-vue-next'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import { availableSkills } from '@/data/mockData'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()
const user = getCurrentUser()
if (!user || !hasMinRole('organizer')) router.push('/')

const mission = ref(null)
const isLoading = ref(false)
const loadError = ref('')
const eventsList = ref([])

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
  responsibleName: '',
  responsiblePhone: '',
  responsibleEmail: '',
  postable: true,
  inscription: true,
  public: true,
  palette: false,
})

const selectedSkills = ref([])

const switchOptions = [
  { key: 'postable',    label: 'Postable',           desc: 'Les bénévoles peuvent postuler' },
  { key: 'inscription', label: 'Inscription requise', desc: 'Inscription obligatoire pour participer' },
  { key: 'public',      label: 'Mission publique',    desc: 'Visible par tous les bénévoles' },
  { key: 'palette',     label: 'Palette',             desc: 'Afficher avec palette spéciale' },
]

const futureEvents = computed(() => {
  const today = new Date(); today.setHours(0, 0, 0, 0)
  return eventsList.value.filter(e => new Date(e.date) >= today)
})

const handleEventChange = () => {
  const selected = eventsList.value.find(e => String(e.id) === String(formData.eventId))
  if (selected) {
    formData.date = selected.date || formData.date
    formData.location = selected.location || formData.location
  }
}

const toggleSkill = (name) => {
  const i = selectedSkills.value.indexOf(name)
  i === -1 ? selectedSkills.value.push(name) : selectedSkills.value.splice(i, 1)
}

const toDisplayType = (value) => {
  if (!value) return ''
  return value.charAt(0).toUpperCase() + value.slice(1)
}

const loadEvents = async () => {
  try {
    const { data } = await api.get('/evenements')
    eventsList.value = (data ?? []).map((event) => ({
      id: String(event.id_evenement),
      name: event.nom_evenement,
      date: event.date_debut_evenement,
      location: event.lieu_evenement,
    }))
  } catch (error) {
    console.error('Erreur chargement événements:', error)
  }
}

const loadMission = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    await loadEvents()
    const { data } = await api.get(`/missions/${route.params.id}`)
    mission.value = data

    formData.name = data.titre_mission || ''
    formData.eventId = String(data.id_evenement ?? '')
    formData.date = data.date_mission || ''
    formData.startTime = (data.heure_debut_mission || '').slice(0, 5)
    formData.endTime = (data.heure_fin_mission || '').slice(0, 5)
    formData.location = data.lieu_mission || ''
    formData.description = data.description_mission || ''
    formData.type = toDisplayType(data.type_mission || '')
    formData.maxVolunteers = String(data.nombre_benevoles_max ?? '')
    formData.backupVolunteers = String(data.nombre_benevoles_backup ?? 0)
    formData.postable = !!data.publie_le_mission
    formData.inscription = data.inscription_requise ?? true
    formData.public = (data.visibilite_mission || 'public') === 'public'
  } catch (error) {
    if (error?.response?.status === 404) {
      mission.value = null
    } else {
      loadError.value = 'Impossible de charger la mission.'
      console.error('Erreur chargement mission:', error)
    }
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  try {
    const id = route.params.id

    await api.put(`/missions/${id}`, {
      id_evenement: Number(formData.eventId),
      responsable_utilisateur_id: mission.value?.responsable_utilisateur_id ?? (Number(user.id) || null),
      titre_mission: formData.name,
      type_mission: (formData.type || 'autre').toLowerCase(),
      description_mission: formData.description,
      date_mission: formData.date,
      heure_debut_mission: formData.startTime,
      heure_fin_mission: formData.endTime,
      lieu_mission: formData.location,
      latitude_mission: mission.value?.latitude_mission || null,
      longitude_mission: mission.value?.longitude_mission || null,
      nombre_benevoles_max: Number(formData.maxVolunteers) || 0,
      nombre_benevoles_backup: Number(formData.backupVolunteers) || 0,
      statut_mission: mission.value?.statut_mission || 'open',
      inscription_requise: !!formData.inscription,
      visibilite_mission: formData.public ? 'public' : 'private',
      consignes_securite: mission.value?.consignes_securite || null,
      image_mission: mission.value?.image_mission || null,
      est_publie_mission: !!formData.postable,
      contact_nom: formData.responsibleName,
      contact_telephone: formData.responsiblePhone,
      contact_email: formData.responsibleEmail || null,
    })

    alert('Mission modifiée avec succès !')
    router.push('/manage-missions')
  } catch (error) {
    console.error('Erreur mise à jour mission:', error)
    alert('Impossible de modifier la mission.')
  }
}

onMounted(loadMission)
</script>