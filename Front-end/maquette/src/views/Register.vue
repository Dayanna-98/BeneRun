<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center p-3 position-relative"
    style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">

    <div class="position-relative w-100 my-4" style="max-width:448px;z-index:1">

      <!-- Logo -->
      <div class="text-center mb-4">
        <div class="d-inline-block bg-white rounded-3 p-3 shadow mb-3">
          <img src="@/assets/logo.png" alt="Running Geneva" style="height:48px;width:auto" />
        </div>
        <h1 class="fs-4 fw-bold text-white mb-1">Running Geneva</h1>
        <p class="text-white-50 small mb-0">Créer votre compte bénévole</p>
      </div>

      <!-- Card -->
      <div class="card shadow-lg">
        <div class="card-header text-center">
          <h4 class="mb-0">Inscription</h4>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleRegister" class="d-flex flex-column gap-3">

            <!-- Prénom / Nom -->
            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Prénom</label>
                <div class="position-relative">
                  <User class="position-absolute text-muted"
                    style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                  <input v-model="form.firstName" type="text" class="form-control form-control-sm ps-4"
                    placeholder="Marie" required />
                </div>
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Nom</label>
                <div class="position-relative">
                  <User class="position-absolute text-muted"
                    style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                  <input v-model="form.lastName" type="text" class="form-control form-control-sm ps-4"
                    placeholder="Dubois" required />
                </div>
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="form-label small fw-medium">Email</label>
              <div class="position-relative">
                <Mail class="position-absolute text-muted"
                  style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                <input v-model="form.email" type="email" class="form-control form-control-sm ps-4"
                  placeholder="votre.email@exemple.com" required />
              </div>
            </div>

            <!-- Téléphone -->
            <div>
              <label class="form-label small fw-medium">Téléphone</label>
              <div class="position-relative">
                <Phone class="position-absolute text-muted"
                  style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                <input v-model="form.phone" type="tel" class="form-control form-control-sm ps-4"
                  placeholder="+41 22 123 45 67" required />
              </div>
            </div>

            <!-- Adresse -->
            <div>
              <label class="form-label small fw-medium">Adresse</label>
              <div class="position-relative">
                <MapPin class="position-absolute text-muted"
                  style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                <input v-model="form.address" type="text" class="form-control form-control-sm ps-4"
                  placeholder="123 Rue des Volontaires, Genève" required />
              </div>
            </div>

            <!-- Mot de passe -->
            <div>
              <label class="form-label small fw-medium">Mot de passe</label>
              <div class="position-relative">
                <Lock class="position-absolute text-muted"
                  style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                  class="form-control form-control-sm ps-4 pe-5"
                  placeholder="••••••••" required />
                <button type="button" class="btn btn-link position-absolute p-0 text-muted"
                  style="top:50%;right:10px;transform:translateY(-50%)"
                  @click="showPassword = !showPassword">
                  <EyeOff v-if="showPassword" style="width:16px;height:16px" />
                  <Eye v-else style="width:16px;height:16px" />
                </button>
              </div>
              <p class="x-small text-muted mt-1 mb-0">Minimum 8 caractères</p>
            </div>

            <!-- Confirmer mot de passe -->
            <div>
              <label class="form-label small fw-medium">Confirmer le mot de passe</label>
              <div class="position-relative">
                <Lock class="position-absolute text-muted"
                  style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                <input v-model="form.confirmPassword" :type="showConfirmPassword ? 'text' : 'password'"
                  class="form-control form-control-sm ps-4 pe-5"
                  placeholder="••••••••" required />
                <button type="button" class="btn btn-link position-absolute p-0 text-muted"
                  style="top:50%;right:10px;transform:translateY(-50%)"
                  @click="showConfirmPassword = !showConfirmPassword">
                  <EyeOff v-if="showConfirmPassword" style="width:16px;height:16px" />
                  <Eye v-else style="width:16px;height:16px" />
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2" :disabled="isLoading">
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              {{ isLoading ? 'Création en cours...' : 'Créer mon compte' }}
            </button>

            <!-- Erreur -->
            <div v-if="errorMessage" class="alert alert-danger mb-3 mt-2">{{ errorMessage }}</div>

            <div class="text-center small d-flex flex-column gap-1">
              <p class="mb-0">
                Déjà un compte ?
                <button type="button" class="btn btn-link btn-sm p-0 fw-semibold text-decoration-none"
                  @click="router.push('/login')">Se connecter</button>
              </p>
              <p class="x-small text-muted mb-0">
                En vous inscrivant, vous acceptez nos
                <button type="button" class="btn btn-link btn-sm p-0 x-small text-decoration-none"
                  @click="alert('Page des conditions d\'utilisation à venir')">
                  conditions d'utilisation
                </button>
              </p>
            </div>

          </form>
        </div>
      </div>

      <p class="text-center text-white-50 x-small mt-3 mb-0">© 2025 Béné'Run • Plateforme de bénévolat</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Eye, EyeOff, Mail, Lock, User, Phone, MapPin } from 'lucide-vue-next'
import userService from '@/services/userService'
import { setCurrentUser } from '@/utils/auth'

const router = useRouter()
const showPassword        = ref(false)
const showConfirmPassword = ref(false)
const isLoading           = ref(false)
const errorMessage        = ref('')

const form = ref({
  firstName: '', lastName: '', email: '',
  phone: '', address: '', password: '', confirmPassword: ''
})

const handleRegister = async () => {
  errorMessage.value = ''
  
  // Validations
  if (!form.value.firstName.trim()) {
    errorMessage.value = 'Le prénom est requis'
    return
  }
  if (!form.value.lastName.trim()) {
    errorMessage.value = 'Le nom est requis'
    return
  }
  if (!form.value.email.trim()) {
    errorMessage.value = 'L\'email est requis'
    return
  }
  if (form.value.password !== form.value.confirmPassword) {
    errorMessage.value = 'Les mots de passe ne correspondent pas'
    return
  }
  if (form.value.password.length < 8) {
    errorMessage.value = 'Le mot de passe doit contenir au moins 8 caractères'
    return
  }

  isLoading.value = true
  try {
    const response = await userService.register({
      firstName: form.value.firstName,
      lastName: form.value.lastName,
      email: form.value.email,
      phone: form.value.phone,
      address: form.value.address,
      password: form.value.password,
      role: 'volunteer',
    })
    
    // Authentifier l'utilisateur automatiquement après inscription
    if (response.user) {
      const tokenValue = String(response.user.id)
      localStorage.setItem('isLoggedIn', 'true')
      localStorage.setItem('userEmail', response.user.email)
      localStorage.setItem('token', tokenValue)
      setCurrentUser(response.user)
      
      alert('Compte créé avec succès !')
      router.push('/')
    }
  } catch (error) {
    console.error('Erreur inscription:', error)
    errorMessage.value = error.message || 'Erreur lors de l\'inscription. Veuillez réessayer.'
  } finally {
    isLoading.value = false
  }
}
</script>