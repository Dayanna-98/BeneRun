<template>
  <div class="min-vh-100 bg-light pb-5">
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center gap-3">
        <button class="btn btn-link p-0 text-dark" @click="router.push('/profile')">
          <ArrowLeft style="width:24px;height:24px" />
        </button>
        <h1 class="fs-5 fw-semibold mb-0">Modifier mon profil</h1>
      </div>
    </header>

    <div class="p-3 mx-auto" style="max-width:768px">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Informations personnelles</h5>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleSubmit" class="d-flex flex-column gap-3">
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label small fw-medium">Prénom *</label>
                <input v-model="form.firstName" class="form-control" required />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-medium">Nom *</label>
                <input v-model="form.lastName" class="form-control" required />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-medium">Email *</label>
                <input v-model="form.email" type="email" class="form-control" required />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-medium">Téléphone</label>
                <input v-model="form.phone" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label small fw-medium">Adresse</label>
                <input v-model="form.address" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-medium">Date de naissance</label>
                <input v-model="form.dateOfBirth" type="date" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label small fw-medium">Taille T-shirt</label>
                <select v-model="form.bibSize" class="form-select">
                  <option value="">Sélectionner</option>
                  <option value="XS">XS</option>
                  <option value="S">S</option>
                  <option value="M">M</option>
                  <option value="L">L</option>
                  <option value="XL">XL</option>
                </select>
              </div>
            </div>

            <div class="row g-2">
              <div class="col-12 col-md-4">
                <label class="form-check-label d-flex align-items-center gap-2">
                  <input v-model="form.hasLicense" class="form-check-input" type="checkbox" />
                  <span>Possède un permis</span>
                </label>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-check-label d-flex align-items-center gap-2">
                  <input v-model="form.isMotorized" class="form-check-input" type="checkbox" />
                  <span>Possède une moto</span>
                </label>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-check-label d-flex align-items-center gap-2">
                  <input v-model="form.hasVehicle" class="form-check-input" type="checkbox" />
                  <span>Possède une voiture</span>
                </label>
              </div>
            </div>

            <div class="bg-light border rounded p-3 d-flex flex-column gap-2">
              <h6 class="fw-medium mb-0">Allergies</h6>
              <div class="d-flex gap-2">
                <input
                  v-model="newAllergy"
                  class="form-control"
                  placeholder="Ajouter une allergie"
                  @keydown.enter.prevent="addAllergy"
                />
                <button type="button" class="btn btn-primary btn-sm px-3" @click="addAllergy">
                  <Plus style="width:16px;height:16px" />
                </button>
              </div>
              <div v-if="allergies.length > 0" class="d-flex flex-wrap gap-2">
                <span
                  v-for="(allergy, index) in allergies" :key="`${allergy}-${index}`"
                  class="badge rounded-pill d-flex align-items-center gap-1 px-3 py-2"
                  style="background-color:#fee2e2;color:#b91c1c;font-weight:normal"
                >
                  {{ allergy }}
                  <button type="button" class="btn p-0 border-0 bg-transparent" style="color:#b91c1c" @click="removeAllergy(index)">
                    <X style="width:12px;height:12px" />
                  </button>
                </span>
              </div>
            </div>

            <div class="bg-light border rounded p-3 d-flex flex-column gap-2">
              <h6 class="fw-medium mb-0">Problèmes de santé</h6>
              <div class="d-flex gap-2">
                <input
                  v-model="newHealthIssue"
                  class="form-control"
                  placeholder="Ajouter un problème de santé"
                  @keydown.enter.prevent="addHealthIssue"
                />
                <button type="button" class="btn btn-primary btn-sm px-3" @click="addHealthIssue">
                  <Plus style="width:16px;height:16px" />
                </button>
              </div>
              <div v-if="healthIssues.length > 0" class="d-flex flex-wrap gap-2">
                <span
                  v-for="(issue, index) in healthIssues" :key="`${issue}-${index}`"
                  class="badge rounded-pill d-flex align-items-center gap-1 px-3 py-2"
                  style="background-color:#ffedd5;color:#c2410c;font-weight:normal"
                >
                  {{ issue }}
                  <button type="button" class="btn p-0 border-0 bg-transparent" style="color:#c2410c" @click="removeHealthIssue(index)">
                    <X style="width:12px;height:12px" />
                  </button>
                </span>
              </div>
            </div>

            <div v-if="errorMessage" class="alert alert-danger mb-0">{{ errorMessage }}</div>
            <div v-if="successMessage" class="alert alert-success mb-0">{{ successMessage }}</div>

            <div class="d-flex gap-3 pt-2">
              <button type="button" class="btn btn-outline-secondary flex-fill" @click="router.push('/profile')" :disabled="isLoading">Annuler</button>
              <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2" :disabled="isLoading">
                <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <Save v-else style="width:16px;height:16px" />
                {{ isLoading ? 'Enregistrement...' : 'Enregistrer' }}
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
import { ArrowLeft, Save, Plus, X } from 'lucide-vue-next'
import { getCurrentUser, setCurrentUser } from '@/utils/auth'
import userService from '@/services/userService'

const router = useRouter()
const user = getCurrentUser()

if (!user) {
  router.push('/login')
}

const form = reactive({
  firstName: user?.firstName || '',
  lastName: user?.lastName || '',
  email: user?.email || '',
  phone: user?.phone || '',
  address: user?.address || '',
  dateOfBirth: user?.dateOfBirth || '',
  role: user?.role || 'volunteer',
  hasLicense: !!user?.hasLicense,
  isMotorized: !!user?.isMotorized,
  hasVehicle: !!user?.hasVehicle,
  bibSize: user?.bibSize || '',
})

const allergies = ref([...(user?.allergies || [])])
const healthIssues = ref([...(user?.healthIssues || [])])
const newAllergy = ref('')
const newHealthIssue = ref('')
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const addAllergy = () => {
  if (newAllergy.value.trim()) {
    allergies.value.push(newAllergy.value.trim())
    newAllergy.value = ''
  }
}

const removeAllergy = (index) => {
  allergies.value.splice(index, 1)
}

const addHealthIssue = () => {
  if (newHealthIssue.value.trim()) {
    healthIssues.value.push(newHealthIssue.value.trim())
    newHealthIssue.value = ''
  }
}

const removeHealthIssue = (index) => {
  healthIssues.value.splice(index, 1)
}

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (newAllergy.value.trim()) addAllergy()
  if (newHealthIssue.value.trim()) addHealthIssue()

  if (!user?.id) {
    errorMessage.value = 'Impossible de modifier le profil : identifiant utilisateur manquant.'
    return
  }

  isLoading.value = true

  try {
    const response = await userService.update(user.id, {
      firstName: form.firstName,
      lastName: form.lastName,
      email: form.email,
      phone: form.phone,
      address: form.address,
      dateOfBirth: form.dateOfBirth,
      role: form.role,
      allergies: allergies.value,
      healthIssues: healthIssues.value,
      hasLicense: form.hasLicense,
      isMotorized: form.isMotorized,
      hasVehicle: form.hasVehicle,
      bibSize: form.bibSize,
      isAnonymous: !!user.anonymous,
      isSuspended: !!user.suspended,
      suspensionReason: user.suspensionReason || null,
      missionCount: user.missionCount || 0,
    })

    if (response.user) {
      setCurrentUser(response.user)
    }

    successMessage.value = 'Profil mis à jour avec succès.'
    router.push('/profile')
  } catch (error) {
    errorMessage.value = error.message || 'Erreur lors de la mise à jour du profil.'
  } finally {
    isLoading.value = false
  }
}
</script>