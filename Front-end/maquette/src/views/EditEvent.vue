<template>
  <div class="min-vh-100 bg-light pb-5">

    <div v-if="isLoading" class="min-vh-100 d-flex align-items-center justify-content-center">
      <p class="text-muted mb-0">Chargement de l'événement...</p>
    </div>

    <!-- Not found -->
    <div v-else-if="!event" class="min-vh-100 d-flex align-items-center justify-content-center">
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
                <input v-model="formData.name" class="form-control" :class="fieldErrors.name ? 'is-invalid' : ''" placeholder="Ex: Marathon de Genève 2025" required />
                <div v-if="fieldErrors.name" class="invalid-feedback d-block">{{ fieldErrors.name }}</div>
              </div>

              <div>
                <label class="form-label small fw-medium">Date *</label>
                <input v-model="formData.date" type="date" class="form-control" :class="fieldErrors.startDate ? 'is-invalid' : ''" required />
                <div v-if="fieldErrors.startDate" class="invalid-feedback d-block">{{ fieldErrors.startDate }}</div>
              </div>

              <div>
                <label class="form-label small fw-medium">Lieu (Genève uniquement) *</label>
                <input v-model="formData.location" class="form-control" :class="fieldErrors.location ? 'is-invalid' : ''" placeholder="Ex: Parc des Bastions, Genève" required />
                <div v-if="fieldErrors.location" class="invalid-feedback d-block">{{ fieldErrors.location }}</div>
              </div>

              <div>
                <label class="form-label small fw-medium">Description *</label>
                <textarea v-model="formData.description" class="form-control" :class="fieldErrors.description ? 'is-invalid' : ''" rows="4" placeholder="Décrivez l'événement en détail..." required style="resize:none" />
                <div v-if="fieldErrors.description" class="invalid-feedback d-block">{{ fieldErrors.description }}</div>
              </div>

              <div class="row g-3">
                <div class="col-6">
                  <label class="form-label small fw-medium">Organisateur *</label>
                  <input v-model="formData.organizer" class="form-control" :class="fieldErrors.organizer ? 'is-invalid' : ''" placeholder="Ex: Ville de Genève" required />
                  <div v-if="fieldErrors.organizer" class="invalid-feedback d-block">{{ fieldErrors.organizer }}</div>
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
                <input v-model="formData.totalVolunteersNeeded" type="number" min="1" class="form-control" :class="fieldErrors.totalVolunteersNeeded ? 'is-invalid' : ''" placeholder="Ex: 100" required />
                <div v-if="fieldErrors.totalVolunteersNeeded" class="invalid-feedback d-block">{{ fieldErrors.totalVolunteersNeeded }}</div>
              </div>

              <div class="bg-light border rounded p-3 d-flex flex-column gap-3">
                <div>
                  <label class="form-label small fw-medium">Lien Google Maps du centre *</label>
                  <input
                    v-model="formData.googleMapsUrl"
                    class="form-control"
                    :class="fieldErrors.googleMapsUrl ? 'is-invalid' : ''"
                    placeholder="Collez le lien Google Maps du point central"
                    required
                  />
                  <div v-if="fieldErrors.googleMapsUrl" class="invalid-feedback d-block">{{ fieldErrors.googleMapsUrl }}</div>
                </div>

                <div>
                  <label class="form-label small fw-medium">Périmètre autorisé (mètres) *</label>
                  <input
                    v-model="formData.radiusMeters"
                    type="number"
                    min="1"
                    class="form-control"
                    :class="fieldErrors.radiusMeters ? 'is-invalid' : ''"
                    placeholder="Ex: 1000"
                    required
                  />
                  <div v-if="fieldErrors.radiusMeters" class="invalid-feedback d-block">{{ fieldErrors.radiusMeters }}</div>
                </div>

                <LeafletPreview
                  :maps-url="formData.googleMapsUrl"
                  :radius-meters="formData.radiusMeters"
                  link-label="Ouvrir le centre dans Google Maps"
                />
              </div>

              <div>
                <label class="form-label small fw-medium">Visuel principal</label>
                <input type="file" accept="image/*" class="form-control mb-2" :class="fieldErrors.imageFile ? 'is-invalid' : ''" @change="handleImageFileChange" />
                <div v-if="fieldErrors.imageFile" class="invalid-feedback d-block">{{ fieldErrors.imageFile }}</div>
                <div v-if="imagePreviewUrl || formData.imageUrl" class="mb-2">
                  <img :src="imagePreviewUrl || formData.imageUrl" alt="Aperçu de l'image" class="w-100 rounded-3 object-fit-cover" style="max-height:200px" />
                </div>
                <div class="small text-muted mb-2">Ou gardez/modifiez l'URL existante.</div>
                <input v-model="formData.imageUrl" class="form-control" placeholder="https://..." />
              </div>

              <div v-if="submitError" class="alert alert-danger mb-0">{{ submitError }}</div>

              <div class="d-flex gap-3 pt-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" @click="router.go(-1)">Annuler</button>
                <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2" :disabled="isSubmitting">
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
import { reactive, ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, Save } from 'lucide-vue-next'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import eventService from '@/services/eventService'
import LeafletPreview from '@/components/maps/LeafletPreview.vue'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const route = useRoute()
const user = getCurrentUser()
if (!user || !hasMinRole('admin')) router.push('/')
const toast = useToast()

const event = ref(null)
const isLoading = ref(true)
const isSubmitting = ref(false)
const fieldErrors = ref({})
const submitError = ref('')
const imagePreviewUrl = ref('')

const formData = reactive({
  name: '',
  date: '',
  location: '',
  description: '',
  organizer: '',
  category: '',
  totalVolunteersNeeded: '',
  googleMapsUrl: '',
  radiusMeters: '1000',
  imageFile: null,
  imageUrl: '',
})

const validateForm = () => {
  const errors = eventService.validateFormData({
    ...formData,
    startDate: formData.date,
    endDate: formData.date,
  })
  fieldErrors.value = errors
  return Object.keys(errors).length === 0
}

const handleImageFileChange = (event) => {
  const file = event.target.files?.[0] || null
  formData.imageFile = file
  imagePreviewUrl.value = file ? URL.createObjectURL(file) : ''
}

const loadEvent = async () => {
  isLoading.value = true

  try {
    const loadedEvent = await eventService.getById(route.params.id)
    event.value = loadedEvent

    formData.name = loadedEvent?.name || ''
    formData.date = loadedEvent?.date || ''
    formData.location = loadedEvent?.location || ''
    formData.description = loadedEvent?.description || ''
    formData.organizer = loadedEvent?.organizer || ''
    formData.category = loadedEvent?.category || ''
    formData.totalVolunteersNeeded = loadedEvent?.totalVolunteersNeeded?.toString() || ''
    formData.googleMapsUrl = loadedEvent?.googleMapsUrl || ''
    formData.radiusMeters = loadedEvent?.radiusMeters ? String(loadedEvent.radiusMeters) : '1000'
    formData.imageUrl = loadedEvent?.imageUrl || ''
  } catch {
    event.value = null
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  if (!event.value) return
  submitError.value = ''
  if (!validateForm()) return

  isSubmitting.value = true

  try {
    await eventService.update(event.value.id, formData)
    toast.success('Événement mis à jour avec succès.')
    router.push('/manage-events')
  } catch (error) {
    submitError.value = error.message || 'Erreur lors de la mise à jour de l\'événement'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(loadEvent)
watch(formData, () => {
  if (Object.keys(fieldErrors.value).length > 0) validateForm()
}, { deep: true })
</script>