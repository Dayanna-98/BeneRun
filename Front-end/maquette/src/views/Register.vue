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
            <p class="x-small text-muted mb-0">Les champs marqués * sont obligatoires.</p>

            <!-- Prénom / Nom -->
            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Prénom *</label>
                <div class="position-relative">
                  <User class="position-absolute text-muted"
                    style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                  <input v-model="form.firstName" type="text" class="form-control form-control-sm ps-4"
                    :class="{ 'is-invalid': shouldShowFieldError('firstName') }"
                    placeholder="Marie" required />
                </div>
                <div v-if="shouldShowFieldError('firstName')" class="invalid-feedback d-block">{{ validationErrors.firstName }}</div>
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Nom *</label>
                <div class="position-relative">
                  <User class="position-absolute text-muted"
                    style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                  <input v-model="form.lastName" type="text" class="form-control form-control-sm ps-4"
                    :class="{ 'is-invalid': shouldShowFieldError('lastName') }"
                    placeholder="Dubois" required />
                </div>
                <div v-if="shouldShowFieldError('lastName')" class="invalid-feedback d-block">{{ validationErrors.lastName }}</div>
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="form-label small fw-medium">Email *</label>
              <div class="position-relative">
                <Mail class="position-absolute text-muted"
                  style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                <input v-model="form.email" type="email" class="form-control form-control-sm ps-4"
                  :class="{ 'is-invalid': shouldShowFieldError('email') }"
                  placeholder="votre.email@exemple.com" required />
              </div>
              <div v-if="shouldShowFieldError('email')" class="invalid-feedback d-block">{{ validationErrors.email }}</div>
            </div>

            <!-- Mot de passe -->
            <div>
              <label class="form-label small fw-medium">Mot de passe *</label>
              <div class="position-relative">
                <Lock class="position-absolute text-muted"
                  style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                  class="form-control form-control-sm ps-4 pe-5"
                  :class="{ 'is-invalid': shouldShowFieldError('password') }"
                  placeholder="••••••••" required />
                <button type="button" class="btn btn-link position-absolute p-0 text-muted"
                  style="top:50%;right:10px;transform:translateY(-50%)"
                  @click="showPassword = !showPassword">
                  <EyeOff v-if="showPassword" style="width:16px;height:16px" />
                  <Eye v-else style="width:16px;height:16px" />
                </button>
              </div>
              <p class="x-small text-muted mt-1 mb-0">{{ PASSWORD_HINT }}</p>
              <div v-if="shouldShowFieldError('password')" class="invalid-feedback d-block">{{ validationErrors.password }}</div>
            </div>

            <!-- Confirmer mot de passe -->
            <div>
              <label class="form-label small fw-medium">Confirmer le mot de passe *</label>
              <div class="position-relative">
                <Lock class="position-absolute text-muted"
                  style="width:16px;height:16px;top:50%;left:10px;transform:translateY(-50%)" />
                <input v-model="form.confirmPassword" :type="showConfirmPassword ? 'text' : 'password'"
                  class="form-control form-control-sm ps-4 pe-5"
                  :class="{ 'is-invalid': shouldShowFieldError('confirmPassword') }"
                  placeholder="••••••••" required />
                <button type="button" class="btn btn-link position-absolute p-0 text-muted"
                  style="top:50%;right:10px;transform:translateY(-50%)"
                  @click="showConfirmPassword = !showConfirmPassword">
                  <EyeOff v-if="showConfirmPassword" style="width:16px;height:16px" />
                  <Eye v-else style="width:16px;height:16px" />
                </button>
              </div>
              <div v-if="shouldShowFieldError('confirmPassword')" class="invalid-feedback d-block">{{ validationErrors.confirmPassword }}</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2" :disabled="isLoading || hasClientErrors">
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
                  @click="router.push('/cgu')">
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
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Eye, EyeOff, Mail, Lock, User } from 'lucide-vue-next'
import userService from '@/services/userService'
import { persistAuthSession } from '@/utils/auth'
import { useToast } from '@/composables/useToast'
import { PASSWORD_HINT, validatePasswordStrength } from '@/utils/passwordPolicy'

const router = useRouter()
const showPassword        = ref(false)
const showConfirmPassword = ref(false)
const isLoading           = ref(false)
const errorMessage        = ref('')
const toast = useToast()
const hasSubmitted = ref(false)

const form = ref({
  firstName: '', lastName: '', email: '',
  password: '', confirmPassword: ''
})

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const validationErrors = computed(() => {
  const errors = {
    firstName: '',
    lastName: '',
    email: '',
    password: '',
    confirmPassword: '',
  }

  if (!form.value.firstName.trim()) errors.firstName = 'Le prénom est requis.'
  if (!form.value.lastName.trim()) errors.lastName = 'Le nom est requis.'

  if (!form.value.email.trim()) {
    errors.email = 'L\'email est requis.'
  } else if (!emailPattern.test(form.value.email.trim())) {
    errors.email = 'Format d\'email invalide.'
  }

  if (!form.value.password) {
    errors.password = 'Le mot de passe est requis.'
  } else {
    const passwordError = validatePasswordStrength(form.value.password)
    if (passwordError) {
      errors.password = passwordError
    }
  }

  if (!form.value.confirmPassword) {
    errors.confirmPassword = 'La confirmation du mot de passe est requise.'
  } else if (form.value.password !== form.value.confirmPassword) {
    errors.confirmPassword = 'Les mots de passe ne correspondent pas.'
  }

  return errors
})

const hasClientErrors = computed(() => Object.values(validationErrors.value).some(Boolean))

const shouldShowFieldError = (fieldName) => {
  const value = form.value[fieldName]
  return (hasSubmitted.value || !!String(value || '').trim()) && !!validationErrors.value[fieldName]
}

watch(form, () => {
  errorMessage.value = ''
}, { deep: true })

const handleRegister = async () => {
  hasSubmitted.value = true
  errorMessage.value = ''

  if (hasClientErrors.value) {
    errorMessage.value = Object.values(validationErrors.value).find(Boolean) || 'Veuillez corriger le formulaire.'
    return
  }

  isLoading.value = true
  try {
    await userService.register({
      firstName: form.value.firstName,
      lastName: form.value.lastName,
      email: form.value.email,
      password: form.value.password,
      role: 'volunteer',
    })

    // Authentification immédiate via l'API après création du compte.
    const loginResponse = await userService.login(form.value.email, form.value.password)

    if (loginResponse.user && loginResponse.token) {
      persistAuthSession({ user: loginResponse.user, token: loginResponse.token })
      toast.success('Compte créé avec succès.')
      router.push('/welcome')
      return
    }

    errorMessage.value = 'Compte créé, mais connexion automatique impossible. Veuillez vous connecter.'
    router.push('/login')
  } catch (error) {
    console.error('Erreur inscription:', error)
    errorMessage.value = error.message || 'Erreur lors de l\'inscription. Veuillez réessayer.'
  } finally {
    isLoading.value = false
  }
}
</script>