<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center p-3 position-relative"
    style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">

    <div class="position-relative w-100" style="max-width:448px;z-index:1">

      <!-- Logo -->
      <div class="text-center mb-4">
        <div class="d-inline-block bg-white rounded-3 p-3 shadow mb-3">
          <img src="@/assets/logo.png" alt="Béné'Run" style="height:64px;width:auto" />
        </div>
        <h1 class="fs-2 fw-bold text-white mb-1">Béné'Run</h1>
        <p class="text-white-50 mb-0">Réinitialiser votre mot de passe</p>
      </div>

      <!-- Card -->
      <div class="card shadow-lg">
        
        <!-- ÉTAPE 1: Demander l'email -->
        <div v-if="step === 'request-email'">
          <div class="card-header text-center">
            <h4 class="mb-0">Mot de passe oublié</h4>
          </div>
          <div class="card-body">
            <form @submit.prevent="requestReset" class="d-flex flex-column gap-3">
              <p class="small text-muted text-center mb-0">
                Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
              </p>

              <div>
                <label class="form-label small fw-medium">Email</label>
                <div class="position-relative">
                  <Mail class="position-absolute text-muted"
                    style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
                  <input
                    v-model="email"
                    type="email"
                    class="form-control ps-5"
                    placeholder="votre.email@exemple.com"
                    :disabled="isLoading"
                    required
                  />
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-lg w-100" :disabled="isLoading || !email">
                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                {{ isLoading ? 'Envoi...' : 'Envoyer le lien' }}
              </button>

              <div class="text-center">
                <router-link to="/login"
                  class="btn btn-link btn-sm p-0 text-decoration-none d-inline-flex align-items-center gap-1">
                  <ArrowLeft style="width:16px;height:16px" />
                  Retour à la connexion
                </router-link>
              </div>
            </form>

            <div v-if="error" class="alert alert-danger mt-3 mb-0">
              {{ error }}
            </div>
          </div>
        </div>

        <!-- ÉTAPE 2: Succès d'envoi -->
        <div v-else-if="step === 'email-sent'">
          <div class="card-header text-center">
            <h4 class="mb-0">Email envoyé !</h4>
          </div>
          <div class="card-body text-center d-flex flex-column gap-3">
            <div class="d-flex justify-content-center">
              <div class="rounded-circle d-flex align-items-center justify-content-center"
                style="width:80px;height:80px;background:#dcfce7">
                <CheckCircle style="width:48px;height:48px;color:#16a34a" />
              </div>
            </div>
            <p class="text-muted mb-0">
              Un email de réinitialisation a été envoyé à <strong>{{ email }}</strong>
            </p>
            <p class="small text-muted mb-0">
              Vérifiez votre boîte de réception et cliquez sur le lien fourni (valide 30 minutes).
            </p>
            <button class="btn btn-outline-primary w-100" @click="step = 'request-email'">
              Réessayer avec un autre email
            </button>
          </div>
        </div>

        <!-- ÉTAPE 3: Réinitialiser le mot de passe -->
        <div v-else-if="step === 'reset-password'">
          <div class="card-header text-center">
            <h4 class="mb-0">Créer un nouveau mot de passe</h4>
          </div>
          <div class="card-body">
            <div v-if="tokenValid === false" class="alert alert-danger">
              Ce lien est invalide ou a expiré. Veuillez <router-link to="/reset-password">demander une nouvelle réinitialisation</router-link>.
            </div>
            <form v-else @submit.prevent="resetPassword" class="d-flex flex-column gap-3">
              <div>
                <label class="form-label small fw-medium">Nouveau mot de passe</label>
                <div class="position-relative">
                  <Lock class="position-absolute text-muted"
                    style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
                  <input
                    v-model="newPassword"
                    type="password"
                    class="form-control ps-5"
                    placeholder="Au moins 8 caractères"
                    :disabled="isLoading"
                    required
                  />
                </div>
                <small class="text-muted d-block mt-1">Minimum 8 caractères</small>
              </div>

              <div>
                <label class="form-label small fw-medium">Confirmer le mot de passe</label>
                <div class="position-relative">
                  <Lock class="position-absolute text-muted"
                    style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
                  <input
                    v-model="confirmPassword"
                    type="password"
                    class="form-control ps-5"
                    placeholder="Confirmez votre mot de passe"
                    :disabled="isLoading"
                    required
                  />
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-lg w-100"
                :disabled="isLoading || !newPassword || newPassword !== confirmPassword">
                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                {{ isLoading ? 'Réinitialisation...' : 'Réinitialiser le mot de passe' }}
              </button>
            </form>

            <div v-if="error" class="alert alert-danger mt-3 mb-0">
              {{ error }}
            </div>
          </div>
        </div>

        <!-- ÉTAPE 4: Succès -->
        <div v-else-if="step === 'success'">
          <div class="card-header text-center">
            <h4 class="mb-0">Succès !</h4>
          </div>
          <div class="card-body text-center d-flex flex-column gap-3">
            <div class="d-flex justify-content-center">
              <div class="rounded-circle d-flex align-items-center justify-content-center"
                style="width:80px;height:80px;background:#dcfce7">
                <CheckCircle style="width:48px;height:48px;color:#16a34a" />
              </div>
            </div>
            <p class="text-muted mb-0">
              Votre mot de passe a été réinitialisé avec succès.
            </p>
            <p class="small text-muted mb-0">
              Redirection vers la page de connexion...
            </p>
          </div>
        </div>

      </div>

      <p class="text-center text-white-50 x-small mt-4 mb-0">© 2025 Béné'Run • Plateforme de bénévolat</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, Mail, CheckCircle, Lock } from 'lucide-vue-next'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()

const step = ref('request-email')
const email = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const isLoading = ref(false)
const error = ref(null)
const tokenValid = ref(null)

const token = ref(route.query.token || null)
const resetEmail = ref(route.query.email || null)

// Vérifier le token au montage si présent
onMounted(async () => {
  if (token.value && resetEmail.value) {
    step.value = 'reset-password'
    email.value = resetEmail.value
    await verifyToken()
  }
})

// Demander la réinitialisation
const requestReset = async () => {
  error.value = null
  isLoading.value = true

  try {
    await api.post('/password-reset/request', {
      email: email.value
    })
    step.value = 'email-sent'
  } catch (err) {
    error.value = err.response?.data?.message || 'Une erreur est survenue. Veuillez vérifier votre email.'
  } finally {
    isLoading.value = false
  }
}

// Vérifier le token
const verifyToken = async () => {
  isLoading.value = true
  try {
    const res = await api.post('/password-reset/verify', {
      token: token.value,
      email: resetEmail.value
    })
    tokenValid.value = res.data.valid
  } catch (err) {
    tokenValid.value = false
    error.value = err.response?.data?.message || 'Lien invalide ou expiré'
  } finally {
    isLoading.value = false
  }
}

// Réinitialiser le mot de passe
const resetPassword = async () => {
  error.value = null

  if (newPassword.value !== confirmPassword.value) {
    error.value = 'Les mots de passe ne correspondent pas'
    return
  }

  if (newPassword.value.length < 8) {
    error.value = 'Le mot de passe doit contenir au moins 8 caractères'
    return
  }

  isLoading.value = true

  try {
    await api.post('/password-reset/reset', {
      token: token.value,
      email: resetEmail.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value
    })
    step.value = 'success'
    setTimeout(() => {
      router.push('/login')
    }, 2000)
  } catch (err) {
    error.value = err.response?.data?.message || 'Une erreur est survenue'
  } finally {
    isLoading.value = false
  }
}
</script>