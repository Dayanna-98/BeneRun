<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center gap-3">
        <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
          <ArrowLeft style="width:24px;height:24px" />
        </button>
        <h1 class="fs-5 fw-semibold mb-0">Créer un Événement</h1>
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

            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Date de début *</label>
                <input v-model="formData.startDate" type="date" class="form-control" required />
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Date de fin *</label>
                <input v-model="formData.endDate" type="date" class="form-control" required />
              </div>
            </div>

            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Heure de début</label>
                <input v-model="formData.startTime" type="time" class="form-control" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Heure de fin</label>
                <input v-model="formData.endTime" type="time" class="form-control" />
              </div>
            </div>

            <div>
              <label class="form-label small fw-medium">Lieu (Genève uniquement) *</label>
              <input v-model="formData.location" class="form-control" placeholder="Ex: Parc des Bastions, Genève" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Description *</label>
              <textarea v-model="formData.description" class="form-control" rows="4" placeholder="Décrivez l'événement en détail..." required style="resize:none" />
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
                  <option>Non défini</option>
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

            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Latitude</label>
                <input v-model="formData.latitude" type="number" step="0.0000001" class="form-control" placeholder="46.2044" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Longitude</label>
                <input v-model="formData.longitude" type="number" step="0.0000001" class="form-control" placeholder="6.1432" />
              </div>
            </div>

            <div>
              <label class="form-label small fw-medium">URL de l'image</label>
              <input v-model="formData.imageUrl" class="form-control" placeholder="https://..." />
            </div>

            <div class="row g-3">
              <div class="col-6">
                <div class="form-check">
                  <input id="isPublished" v-model="formData.isPublished" class="form-check-input" type="checkbox" />
                  <label class="form-check-label small fw-medium" for="isPublished">Événement publié</label>
                </div>
              </div>
              <div class="col-6">
                <div class="form-check">
                  <input id="isCancelled" v-model="formData.isCancelled" class="form-check-input" type="checkbox" />
                  <label class="form-check-label small fw-medium" for="isCancelled">Événement annulé</label>
                </div>
              </div>
            </div>

            <div v-if="formData.isCancelled" class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Date d'annulation</label>
                <input v-model="formData.cancellationDate" type="date" class="form-control" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Raison d'annulation</label>
                <input v-model="formData.cancellationReason" class="form-control" placeholder="Ex: météo" />
              </div>
            </div>

            <div class="d-flex gap-3 pt-2">
              <button type="button" class="btn btn-outline-secondary flex-fill" @click="router.go(-1)">Annuler</button>
              <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2" :disabled="isSubmitting">
                <Save style="width:16px;height:16px" />
                {{ isSubmitting ? 'Création...' : "Créer l'événement" }}
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Save } from 'lucide-vue-next'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import eventService from '@/services/eventService'

const router = useRouter()
const user = getCurrentUser()

if (!user || !hasMinRole('admin')) router.push('/')

const formData = reactive({
  name: '', startDate: '', endDate: '', startTime: '', endTime: '',
  location: '', description: '',
  organizer: '', category: '', totalVolunteersNeeded: '', imageUrl: '',
  latitude: '', longitude: '',
  isPublished: true, isCancelled: false,
  cancellationDate: '', cancellationReason: '',
})

const isSubmitting = ref(false)

const handleSubmit = async () => {
  isSubmitting.value = true

  try {
    await eventService.create(formData)
    alert('Événement créé avec succès !')
    router.push('/manage-events')
  } catch (error) {
    alert(error.message || 'Erreur lors de la création de l\'événement')
  } finally {
    isSubmitting.value = false
  }
}
</script>