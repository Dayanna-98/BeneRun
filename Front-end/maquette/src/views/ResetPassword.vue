<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center p-3 position-relative"
    style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">

    <div class="position-relative w-100" style="max-width:448px;z-index:1">

      <!-- Logo -->
      <div class="text-center mb-4">
        <div class="d-inline-block bg-white rounded-3 p-3 shadow mb-3">
          <img src="@/assets/logo.png" alt="Running Geneva" style="height:64px;width:auto" />
        </div>
        <h1 class="fs-2 fw-bold text-white mb-1">Running Geneva</h1>
        <p class="text-white-50 mb-0">Réinitialiser votre mot de passe</p>
      </div>

      <!-- Card -->
      <div class="card shadow-lg">
        <div class="card-header text-center">
          <h4 class="mb-0">{{ isSubmitted ? 'Email envoyé !' : 'Mot de passe oublié' }}</h4>
        </div>
        <div class="card-body">

          <!-- Formulaire -->
          <form v-if="!isSubmitted" @submit.prevent="handleResetPassword" class="d-flex flex-column gap-3">
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
                  required
                />
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">
              Envoyer le lien
            </button>

            <div class="text-center">
              <button type="button"
                class="btn btn-link btn-sm p-0 text-decoration-none d-inline-flex align-items-center gap-1"
                @click="router.push('/login')">
                <ArrowLeft style="width:16px;height:16px" />
                Retour à la connexion
              </button>
            </div>
          </form>

          <!-- Succès -->
          <div v-else class="text-center d-flex flex-column gap-3">
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
              Vérifiez votre boîte de réception et suivez les instructions pour réinitialiser votre mot de passe.
            </p>
            <button class="btn btn-outline-primary w-100" @click="router.push('/login')">
              Retour à la connexion
            </button>
          </div>

        </div>
      </div>

      <p class="text-center text-white-50 x-small mt-4 mb-0">© 2025 Béné'Run • Plateforme de bénévolat</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Mail, CheckCircle } from 'lucide-vue-next'

const router = useRouter()
const email       = ref('')
const isSubmitted = ref(false)

const handleResetPassword = () => {
  if (email.value) {
    isSubmitted.value = true
  } else {
    alert('Veuillez entrer votre adresse email')
  }
}
</script>