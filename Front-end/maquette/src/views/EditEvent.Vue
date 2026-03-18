<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Not found -->
    <div v-if="!event" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-muted mb-3">Événement non trouvé</p>
        <button class="btn btn-primary" @click="router.push('/manage-events')">Retour à la gestion</button>
      </div>
    </div>

    <template v-else>
      <!-- Header -->
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
import { reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, Save } from 'lucide-vue-next'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import { events } from '@/data/mockData'

const router = useRouter()
const route = useRoute()
const user = getCurrentUser()
if (!user || !hasMinRole('admin')) router.push('/')

const event = events.find(e => e.id === route.params.id)

const formData = reactive({
  name: event?.name || '',
  date: event?.date || '',
  location: event?.location || '',
  description: event?.description || '',
  organizer: event?.organizer || '',
  category: event?.category || '',
  totalVolunteersNeeded: event?.totalVolunteersNeeded?.toString() || '',
  imageUrl: event?.imageUrl || '',
})

const handleSubmit = () => {
  alert('Événement modifié avec succès !')
  router.push('/manage-events')
}
</script>