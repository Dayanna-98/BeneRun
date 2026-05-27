<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center p-3 position-relative"
    style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">

    <div class="position-relative w-100" style="max-width:448px;z-index:1">

      <!-- Logo -->
      <div class="text-center mb-4">
        <div class="d-inline-block bg-white rounded-3 p-3 shadow mb-3">
          <img src="@/assets/logo.png" alt="Béné'Run" style="height:64px;width:auto" />
        </div>
        <h1 class="fs-2 fw-bold text-white mb-2">Béné'Run</h1>
        <p class="text-white-50 mb-2">Bienvenue sur votre plateforme de bénévolat</p>
        <p class="small text-white-50 mb-0">
          Rejoignez une communauté engagée et contribuez à des événements extraordinaires à Genève
        </p>
      </div>

      <!-- Card -->
      <div class="card shadow-lg">
        <div class="card-header text-center">
          <h4 class="mb-0">Connexion</h4>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleLogin" class="d-flex flex-column gap-3">
            <p class="x-small text-muted mb-0">Les champs marqués * sont obligatoires.</p>

            <div>
              <label class="form-label small fw-medium">Email *</label>
              <div class="position-relative">
                <User class="position-absolute text-muted"
                  style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
                <input
                  v-model="email"
                  type="email"
                  class="form-control ps-5"
                  :class="{ 'is-invalid': shouldShowEmailError }"
                  placeholder="votre.email@exemple.com"
                  required
                />
              </div>
              <div v-if="shouldShowEmailError" class="invalid-feedback d-block">{{ emailError }}</div>
            </div>

            <div>
              <label class="form-label small fw-medium">Mot de passe *</label>
              <div class="position-relative">
                <Lock class="position-absolute text-muted"
                  style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
                <input
                  v-model="password"
                  type="password"
                  class="form-control ps-5"
                  :class="{ 'is-invalid': shouldShowPasswordError }"
                  placeholder="••••••••"
                  required
                />
              </div>
              <div v-if="shouldShowPasswordError" class="invalid-feedback d-block">{{ passwordError }}</div>
            </div>

            <div class="text-end">
              <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none"
                @click="router.push('/reset-password')">
                Mot de passe oublié ?
              </button>
            </div>

            <button
              type="submit"
              class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2"
              :disabled="!canSubmit"
            >
              <LogIn style="width:20px;height:20px" />
              {{ isLoading ? 'Connexion...' : 'Se connecter' }}
            </button>

            <div class="text-center small">
              Pas encore de compte ?
              <button type="button" class="btn btn-link btn-sm p-0 fw-semibold text-decoration-none"
                @click="router.push('/register')">
                S'inscrire
              </button>
            </div>

          </form>

          <!-- Erreur -->
          <div v-if="error" class="alert alert-danger mt-3 mb-0 small">{{ error }}</div>
          <button
            v-if="pendingVerificationEmail"
            type="button"
            class="btn btn-outline-primary btn-sm mt-3 w-100"
            @click="goToEmailVerification"
          >
            Vérifier mon email
          </button>

        </div>
      </div>

      <p class="text-center text-white-50 x-small mt-4 mb-0">© 2025 Béné'Run • Plateforme de bénévolat</p>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { LogIn, User, Lock } from 'lucide-vue-next'
import { persistAuthSession } from '@/utils/auth'
import userService from '@/services/userService'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')
const isLoading = ref(false)
const hasSubmitted = ref(false)
const pendingVerificationEmail = ref('')

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const emailError = computed(() => {
  if (!email.value.trim()) return 'L\'email est requis.'
  if (!emailPattern.test(email.value.trim())) return 'Format d\'email invalide.'
  return ''
})

const passwordError = computed(() => {
  if (!password.value.trim()) return 'Le mot de passe est requis.'
  return ''
})

const shouldShowEmailError = computed(() => (hasSubmitted.value || !!email.value) && !!emailError.value)
const shouldShowPasswordError = computed(() => (hasSubmitted.value || !!password.value) && !!passwordError.value)
const canSubmit = computed(() => !isLoading.value && !emailError.value && !passwordError.value)

watch([email, password], () => {
  error.value = ''
  pendingVerificationEmail.value = ''
})

const goToEmailVerification = () => {
  router.push({
    path: '/email-verification',
    query: {
      status: 'pending',
      email: pendingVerificationEmail.value,
      message: 'Veuillez vérifier votre adresse email avant de vous connecter.',
    },
  })
}

const handleLogin = async () => {
  hasSubmitted.value = true
  error.value = ''

  if (emailError.value || passwordError.value) {
    error.value = emailError.value || passwordError.value
    return
  }

  isLoading.value = true

  try {
    const response = await userService.login(email.value, password.value)
    if (response.user && response.token) {
      persistAuthSession({ user: response.user, token: response.token })
      router.push('/')
      return
    }

    error.value = 'Réponse de connexion invalide'
  } catch (apiError) {
    console.error('Erreur connexion API:', apiError)
    if (apiError?.status === 'email_not_verified') {
      pendingVerificationEmail.value = String(apiError?.email || email.value || '').trim()
    }
    error.value = apiError.message || 'Email ou mot de passe incorrect'
  } finally {
    isLoading.value = false
  }
}

</script>