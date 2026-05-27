<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center p-3 position-relative"
    style="background:linear-gradient(145deg,#13232f 0%,#264257 100%)">

    <div class="position-relative w-100" style="max-width:520px;z-index:1">
      <div class="card shadow-lg border-0 overflow-hidden">
        <div class="card-header text-white py-3" :class="headerClass">
          <h4 class="mb-0 text-center">{{ title }}</h4>
        </div>

        <div class="card-body p-4 p-md-5">
          <p class="text-muted mb-3">{{ message }}</p>

          <div v-if="email" class="small text-muted mb-4">
            Adresse concernée: <strong>{{ email }}</strong>
          </div>

          <div v-if="showResend" class="mb-4">
            <button
              type="button"
              class="btn btn-outline-primary"
              :disabled="isResending"
              @click="handleResend"
            >
              {{ isResending ? 'Renvoi en cours...' : 'Renvoyer l\'email de vérification' }}
            </button>
            <p class="small text-muted mt-2 mb-0">Pense à vérifier les spams.</p>
          </div>

          <div v-if="feedback" class="alert" :class="feedbackClass">{{ feedback }}</div>

          <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="btn btn-primary" @click="router.push('/login')">
              Aller à la connexion
            </button>
            <button type="button" class="btn btn-light border" @click="router.push('/register')">
              Retour à l'inscription
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import userService from '@/services/userService'

const route = useRoute()
const router = useRouter()
const isResending = ref(false)
const feedback = ref('')

const status = computed(() => String(route.query.status || 'pending'))
const message = computed(() => {
  const provided = String(route.query.message || '').trim()
  if (provided) return provided

  if (status.value === 'success') return 'Ton adresse email a été vérifiée. Tu peux maintenant te connecter.'
  if (status.value === 'already') return 'Cette adresse email a déjà été vérifiée. Tu peux te connecter.'
  if (status.value === 'error') return 'Le lien est invalide ou expiré. Tu peux demander un nouveau lien.'
  return 'Ton compte a été créé. Vérifie ta boîte mail pour activer ton compte.'
})

const email = computed(() => String(route.query.email || '').trim())

const title = computed(() => {
  if (status.value === 'success') return 'Email confirmé'
  if (status.value === 'already') return 'Email déjà confirmé'
  if (status.value === 'error') return 'Lien de validation invalide'
  return 'Vérifie ton email'
})

const headerClass = computed(() => {
  if (status.value === 'success') return 'bg-success'
  if (status.value === 'already') return 'bg-info'
  if (status.value === 'error') return 'bg-danger'
  return 'bg-primary'
})

const showResend = computed(() => {
  return ['pending', 'error'].includes(status.value) && !!email.value
})

const feedbackClass = computed(() => {
  if (feedback.value.startsWith('Erreur')) return 'alert-danger'
  return 'alert-success'
})

const handleResend = async () => {
  if (!email.value || isResending.value) return

  isResending.value = true
  feedback.value = ''

  try {
    const response = await userService.resendVerificationEmail(email.value)
    feedback.value = response?.message || 'Un nouvel email de vérification a été envoyé.'
  } catch (error) {
    feedback.value = error?.message || 'Erreur: impossible de renvoyer l\'email de vérification.'
  } finally {
    isResending.value = false
  }
}
</script>
