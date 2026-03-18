<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Not found -->
    <div v-if="!mission" class="min-vh-100 d-flex align-items-center justify-content-center">
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
import { reactive, ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, Save } from 'lucide-vue-next'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import { availableMissions, events, availableSkills } from '@/data/mockData'

const router = useRouter()
const route = useRoute()
const user = getCurrentUser()
if (!user || !hasMinRole('organizer')) router.push('/')

const mission = availableMissions.find(m => m.id === route.params.id)

const formData = reactive({
  name: mission?.name || '',
  eventId: mission?.eventId || '',
  date: mission?.date || '',
  startTime: mission?.startTime || '',
  endTime: mission?.endTime || '',
  location: mission?.location || '',
  description: mission?.description || '',
  type: mission?.type || '',
  maxVolunteers: mission?.maxVolunteers?.toString() || '',
  backupVolunteers: mission?.backupVolunteers?.toString() || '0',
  responsibleName: mission?.responsible?.name || '',
  responsiblePhone: mission?.responsible?.phone || '',
  responsibleEmail: mission?.responsible?.email || '',
  postable: mission?.postable ?? true,
  inscription: mission?.inscription ?? true,
  public: mission?.public ?? true,
  palette: mission?.palette ?? false,
})

const selectedSkills = ref(mission?.requiredSkills || [])

const switchOptions = [
  { key: 'postable',    label: 'Postable',           desc: 'Les bénévoles peuvent postuler' },
  { key: 'inscription', label: 'Inscription requise', desc: 'Inscription obligatoire pour participer' },
  { key: 'public',      label: 'Mission publique',    desc: 'Visible par tous les bénévoles' },
  { key: 'palette',     label: 'Palette',             desc: 'Afficher avec palette spéciale' },
]

const futureEvents = computed(() => {
  const today = new Date(); today.setHours(0, 0, 0, 0)
  return events.filter(e => new Date(e.date) >= today)
})

const handleEventChange = () => {
  const selected = events.find(e => e.id === formData.eventId)
  if (selected) {
    formData.date = selected.date || formData.date
    formData.location = selected.location || formData.location
  }
}

const toggleSkill = (name) => {
  const i = selectedSkills.value.indexOf(name)
  i === -1 ? selectedSkills.value.push(name) : selectedSkills.value.splice(i, 1)
}

const handleSubmit = () => {
  alert('Mission modifiée avec succès !')
  router.push('/manage-missions')
}
</script>