<template>
  <div class="min-vh-100 bg-light pb-5">

    <div v-if="isLoading" class="min-vh-100 d-flex align-items-center justify-content-center">
      <p class="text-muted mb-0">Chargement de l'événement...</p>
    </div>

    <div v-else-if="loadError" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-danger mb-3">{{ loadError }}</p>
        <button class="btn btn-outline-danger" @click="loadEvent">Réessayer</button>
      </div>
    </div>

    <!-- Not found -->
    <div v-else-if="!event" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-muted mb-3">Événement non trouvé</p>
        <button class="btn btn-primary" @click="router.push('/manage-events')">Retour à la gestion</button>
      </div>
    </div>

    <template v-else>
      <header class="bg-white border-bottom sticky-top">
        <div class="p-3 d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Modifier l'Événement</h1>
        </div>
      </header>

      <div class="p-3 mx-auto" style="max-width:768px">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Informations de l'événement</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="handleSubmit" class="d-flex flex-column gap-3">

              <div>
                <label class="form-label small fw-medium">Nom de l'événement *</label>
                <input v-model="formData.name" class="form-control" placeholder="Ex: Marathon de Genève 2025" required />
              </div>

              <div>
                <label class="form-label small fw-medium">Date *</label>
                <input v-model="formData.date" type="date" class="form-control" required />
              </div>

              <div>
                <label class="form-label small fw-medium">Lieu (Genève uniquement) *</label>
                <input v-model="formData.location" class="form-control" placeholder="Ex: Parc des Bastions, Genève" required />
              </div>

              <div>
                <label class="form-label small fw-medium">Description *</label>
                <textarea v-model="formData.description" class="form-control" rows="4" placeholder="Décrivez l'événement en détail..." required style="resize:none"></textarea>
              </div>

              <div class="row g-3">
                <div class="col-6">
                  <label class="form-label small fw-medium">Organisateur *</label>
                  <input v-model="formData.organizer" class="form-control" placeholder="Ex: Ville de Genève" required />
                </div>
                <div class="col-6">
                  <label class="form-label small fw-medium">Catégorie *</label>
                  <select v-model="formData.category" class="form-select" required>
                    <option value="">Sélectionner...</option>
                    <option>Sport</option>
                    <option>Festival</option>
                    <option>Salon</option>
                    <option>Écologie</option>
                    <option>Culture</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="form-label small fw-medium">Nombre total de bénévoles nécessaires *</label>
                <input v-model="formData.totalVolunteersNeeded" type="number" class="form-control" placeholder="Ex: 100" required />
              </div>

              <div>
                <label class="form-label small fw-medium">URL de l'image</label>
                <input v-model="formData.imageUrl" class="form-control" placeholder="https://..." />
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
import { reactive, ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, Save } from 'lucide-vue-next'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()
const user = getCurrentUser()
if (!user || !hasMinRole('admin')) router.push('/')

const event = ref(null)
const isLoading = ref(false)
const loadError = ref('')

const formData = reactive({
  name: '',
  date: '',
  location: '',
  description: '',
  organizer: '',
  category: '',
  totalVolunteersNeeded: '',
  imageUrl: '',
})

const loadEvent = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const id = route.params.id
    const { data } = await api.get(`/evenements/${id}`)

    event.value = data
    formData.name = data.nom_evenement || ''
    formData.date = data.date_debut_evenement || ''
    formData.location = data.lieu_evenement || ''
    formData.description = data.description_evenement || ''
    formData.organizer = data.organisateur_evenement || ''
    formData.category = 'Sport'
    formData.totalVolunteersNeeded = String(data.nombre_benevoles_requis ?? '')
    formData.imageUrl = data.image_evenement || ''
  } catch (error) {
    if (error?.response?.status === 404) {
      event.value = null
    } else {
      loadError.value = "Impossible de charger l'evenement."
      console.error('Erreur chargement événement:', error)
    }
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  try {
    const id = route.params.id

    await api.put(`/evenements/${id}`, {
      nom_evenement: formData.name,
      description_evenement: formData.description,
      date_debut_evenement: formData.date,
      date_fin_evenement: formData.date,
      heure_debut_evenement: event.value?.heure_debut_evenement || null,
      heure_fin_evenement: event.value?.heure_fin_evenement || null,
      lieu_evenement: formData.location,
      latitude_evenement: event.value?.latitude_evenement || null,
      longitude_evenement: event.value?.longitude_evenement || null,
      organisateur_evenement: formData.organizer,
      image_evenement: formData.imageUrl || null,
      nombre_benevoles_requis: Number(formData.totalVolunteersNeeded) || 0,
      est_annule_evenement: event.value?.est_annule_evenement ?? false,
      date_annulation_evenement: event.value?.date_annulation_evenement || null,
      raison_annulation_evenement: event.value?.raison_annulation_evenement || null,
      est_publie_evenement: event.value?.est_publie_evenement ?? true,
      cree_par_utilisateur_id: event.value?.cree_par_utilisateur_id ?? Number(user.id) ?? 1,
    })

    alert('Événement modifié avec succès !')
    router.push('/manage-events')
  } catch (error) {
    console.error('Erreur mise à jour événement:', error)
    alert("Impossible de modifier l'événement.")
  }
}

onMounted(loadEvent)
</script>
