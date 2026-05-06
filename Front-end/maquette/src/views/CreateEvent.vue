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
              <input v-model="formData.name" class="form-control" :class="fieldErrors.name ? 'is-invalid' : ''" placeholder="Ex: Marathon de Genève 2025" required />
              <div v-if="fieldErrors.name" class="invalid-feedback d-block">{{ fieldErrors.name }}</div>
            </div>

            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Date de début *</label>
                <input v-model="formData.startDate" type="date" class="form-control" :class="fieldErrors.startDate ? 'is-invalid' : ''" required />
                <div v-if="fieldErrors.startDate" class="invalid-feedback d-block">{{ fieldErrors.startDate }}</div>
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Date de fin *</label>
                <input v-model="formData.endDate" type="date" class="form-control" :class="fieldErrors.endDate ? 'is-invalid' : ''" required />
                <div v-if="fieldErrors.endDate" class="invalid-feedback d-block">{{ fieldErrors.endDate }}</div>
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
                <div class="form-text">Le lien doit contenir le point central du périmètre de l'événement.</div>
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
              <div v-if="imagePreviewUrl" class="mb-2">
                <img :src="imagePreviewUrl" alt="Aperçu de l'image" class="w-100 rounded-3 object-fit-cover" style="max-height:200px" />
              </div>
              <div class="small text-muted mb-2">Ou collez une URL si vous préférez.</div>
              <input v-model="formData.imageUrl" class="form-control" placeholder="https://..." />
            </div>

            <div v-if="submitError" class="alert alert-danger mb-0">{{ submitError }}</div>

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
import { reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Save } from 'lucide-vue-next'
import { getCurrentUser, hasMinRole } from '@/utils/auth'
import eventService from '@/services/eventService'
import LeafletPreview from '@/components/maps/LeafletPreview.vue'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const user = getCurrentUser()

if (!user || !hasMinRole('admin')) router.push('/')
const toast = useToast()

const formData = reactive({
  name: '', startDate: '', endDate: '', startTime: '', endTime: '',
  location: '', description: '',
  organizer: '', category: '', totalVolunteersNeeded: '', imageUrl: '',
  imageFile: null,
  googleMapsUrl: '', radiusMeters: '1000',
  isPublished: true, isCancelled: false,
  cancellationDate: '', cancellationReason: '',
})

const isSubmitting = ref(false)
const fieldErrors = ref({})
const submitError = ref('')
const imagePreviewUrl = ref('')

const validateForm = () => {
  const errors = eventService.validateFormData(formData)
  fieldErrors.value = errors
  return Object.keys(errors).length === 0
}

const handleImageFileChange = (event) => {
  const file = event.target.files?.[0] || null
  formData.imageFile = file
  imagePreviewUrl.value = file ? URL.createObjectURL(file) : ''
  fieldErrors.value = { ...fieldErrors.value, imageFile: undefined }
}

const handleSubmit = async () => {
  submitError.value = ''
  if (!validateForm()) return
  isSubmitting.value = true

  try {
    await eventService.create(formData)
    toast.success('Événement créé avec succès.')
    router.push('/manage-events')
  } catch (error) {
    submitError.value = error.message || 'Erreur lors de la création de l\'événement'
  } finally {
    isSubmitting.value = false
  }
}

watch(formData, () => {
  if (Object.keys(fieldErrors.value).length > 0) validateForm()
}, { deep: true })
</script>