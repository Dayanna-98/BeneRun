<template>
  <!-- Écran d'accès refusé -->
  <div v-if="!user || !isSuperAdmin" class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="alert alert-danger text-center">
      <h4>Accès refusé</h4>
      <p>Vous devez être super-admin pour créer des utilisateurs.</p>
      <button class="btn btn-primary mt-3" @click="router.push('/')">Retour à l'accueil</button>
    </div>
  </div>

  <!-- Formulaire de création -->
  <div v-else class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center gap-3">
        <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
          <ArrowLeft style="width:24px;height:24px" />
        </button>
        <h1 class="fs-5 fw-semibold mb-0">Créer un Utilisateur</h1>
      </div>
    </header>

    <div class="p-3 mx-auto" style="max-width:768px">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Informations de l'utilisateur</h5>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleSubmit" class="d-flex flex-column gap-3">

            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Prénom *</label>
                <input v-model="formData.firstName" class="form-control" placeholder="Ex: Marie" required />
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Nom *</label>
                <input v-model="formData.lastName" class="form-control" placeholder="Ex: Dubois" required />
              </div>
            </div>

            <div>
              <label class="form-label small fw-medium">Email *</label>
              <input v-model="formData.email" type="email" class="form-control" placeholder="exemple@email.com" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Mot de passe *</label>
              <input v-model="formData.password" type="password" class="form-control" placeholder="••••••••" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Téléphone *</label>
              <input v-model="formData.phone" type="tel" class="form-control" placeholder="+41 22 123 45 67" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Adresse *</label>
              <input v-model="formData.address" class="form-control" placeholder="Ex: 15 rue du Rhône, 1204 Genève" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Date de naissance *</label>
              <input v-model="formData.dateOfBirth" type="date" class="form-control" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Rôle *</label>
              <select v-model="formData.role" class="form-select" required>
                <option value="volunteer">Bénévole</option>
                <option value="mission_manager">Responsable</option>
                <option value="admin">Admin</option>
                <option value="superadmin">Super-admin</option>
              </select>
            </div>

            <div>
              <label class="form-label small fw-medium">Taille T-shirt</label>
              <select v-model="formData.bibSize" class="form-select">
                <option value="">Sélectionner</option>
                <option value="XS">XS</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
              </select>
            </div>

            <div class="row g-2">
              <div class="col-12 col-md-6">
                <label class="form-check-label d-flex align-items-center gap-2">
                  <input v-model="formData.hasLicense" class="form-check-input" type="checkbox" />
                  <span>Possède un permis</span>
                </label>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-check-label d-flex align-items-center gap-2">
                  <input v-model="formData.isMotorized" class="form-check-input" type="checkbox" />
                  <span>Possède une moto</span>
                </label>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-check-label d-flex align-items-center gap-2">
                  <input v-model="formData.hasVehicle" class="form-check-input" type="checkbox" />
                  <span>Possède une voiture</span>
                </label>
              </div>
            </div>

            <!-- Allergies -->
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
                  v-for="(allergy, i) in allergies" :key="i"
                  class="badge rounded-pill d-flex align-items-center gap-1 px-3 py-2"
                  style="background-color:#fee2e2;color:#b91c1c;font-weight:normal"
                >
                  {{ allergy }}
                  <button type="button" class="btn p-0 border-0 bg-transparent" style="color:#b91c1c" @click="removeAllergy(i)">
                    <X style="width:12px;height:12px" />
                  </button>
                </span>
              </div>
            </div>

            <!-- Problèmes de santé -->
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
                  v-for="(issue, i) in healthIssues" :key="i"
                  class="badge rounded-pill d-flex align-items-center gap-1 px-3 py-2"
                  style="background-color:#ffedd5;color:#c2410c;font-weight:normal"
                >
                  {{ issue }}
                  <button type="button" class="btn p-0 border-0 bg-transparent" style="color:#c2410c" @click="removeHealthIssue(i)">
                    <X style="width:12px;height:12px" />
                  </button>
                </span>
              </div>
            </div>

            <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

            <div class="d-flex gap-3 pt-2">
              <button type="button" class="btn btn-outline-secondary flex-fill" @click="router.go(-1)" :disabled="isLoading">Annuler</button>
              <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2" :disabled="isLoading">
                <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <Save v-else style="width:16px;height:16px" />
                {{ isLoading ? 'Création en cours...' : 'Créer l\'utilisateur' }}
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
import { getCurrentUser, isRole } from '@/utils/auth'
import userService from '@/services/userService'

const router = useRouter()
const user = getCurrentUser()
const isSuperAdmin = user && isRole('superadmin')

// Log pour debug
console.log('CreateUser.vue Debug:')
console.log('- User:', user)
console.log('- User role:', user?.role)
console.log('- isSuperAdmin:', isSuperAdmin)

// Ne PAS rediriger, on laisse le v-if gérer l'affichage
if (!user) {
  console.warn('Pas d\'utilisateur trouvé, localStorage currentUser:', localStorage.getItem('currentUser'))
}

const formData = reactive({
  firstName: '', lastName: '', email: '', password: '',
  phone: '', address: '', dateOfBirth: '', role: 'volunteer',
  hasLicense: false,
  isMotorized: false,
  hasVehicle: false,
  bibSize: '',
})

const allergies = ref([])
const healthIssues = ref([])
const newAllergy = ref('')
const newHealthIssue = ref('')
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const addAllergy = () => {
  if (newAllergy.value.trim()) { allergies.value.push(newAllergy.value.trim()); newAllergy.value = '' }
}
const removeAllergy = (i) => allergies.value.splice(i, 1)

const addHealthIssue = () => {
  if (newHealthIssue.value.trim()) { healthIssues.value.push(newHealthIssue.value.trim()); newHealthIssue.value = '' }
}
const removeHealthIssue = (i) => healthIssues.value.splice(i, 1)

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  // Inclure la valeur en cours de saisie même si l'utilisateur n'a pas cliqué sur +
  if (newAllergy.value.trim()) {
    allergies.value.push(newAllergy.value.trim())
    newAllergy.value = ''
  }
  if (newHealthIssue.value.trim()) {
    healthIssues.value.push(newHealthIssue.value.trim())
    newHealthIssue.value = ''
  }
  
  // Validations
  if (!formData.firstName.trim()) {
    errorMessage.value = 'Le prénom est requis'
    return
  }
  if (!formData.lastName.trim()) {
    errorMessage.value = 'Le nom est requis'
    return
  }
  if (!formData.email.trim()) {
    errorMessage.value = 'L\'email est requis'
    return
  }
  if (!formData.password.trim()) {
    errorMessage.value = 'Le mot de passe est requis'
    return
  }
  if (formData.password.length < 8) {
    errorMessage.value = 'Le mot de passe doit contenir au moins 8 caractères'
    return
  }

  isLoading.value = true
  try {
    const response = await userService.create({
      firstName: formData.firstName,
      lastName: formData.lastName,
      email: formData.email,
      password: formData.password,
      phone: formData.phone,
      address: formData.address,
      dateOfBirth: formData.dateOfBirth,
      role: formData.role,
      allergies: allergies.value,
      healthIssues: healthIssues.value,
      hasLicense: formData.hasLicense,
      isMotorized: formData.isMotorized,
      hasVehicle: formData.hasVehicle,
      bibSize: formData.bibSize,
    })
    
    successMessage.value = 'Utilisateur créé avec succès !'
    setTimeout(() => {
      router.push('/manage-users')
    }, 1500)
  } catch (error) {
    console.error('Erreur création utilisateur:', error)
    errorMessage.value = error.message || 'Erreur lors de la création de l\'utilisateur. Veuillez réessayer.'
  } finally {
    isLoading.value = false
  }
}
</script>