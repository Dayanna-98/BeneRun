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

            <div>
              <label class="form-label small fw-medium">Email</label>
              <div class="position-relative">
                <User class="position-absolute text-muted"
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

            <div>
              <label class="form-label small fw-medium">Mot de passe</label>
              <div class="position-relative">
                <Lock class="position-absolute text-muted"
                  style="width:20px;height:20px;top:50%;left:12px;transform:translateY(-50%)" />
                <input
                  v-model="password"
                  type="password"
                  class="form-control ps-5"
                  placeholder="••••••••"
                  required
                />
              </div>
            </div>

            <div class="text-end">
              <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none"
                @click="router.push('/reset-password')">
                Mot de passe oublié ?
              </button>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2">
              <LogIn style="width:20px;height:20px" />
              Se connecter
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

          <!-- Comptes démo -->
          <div class="mt-4 pt-3 border-top">
            <p class="small fw-medium text-center text-muted mb-3">Comptes de démonstration</p>
            <div class="d-flex flex-column gap-2">
              <button
                v-for="user in users" :key="user.id"
                type="button"
                class="btn btn-outline-secondary text-start p-3 d-flex align-items-center justify-content-between"
                @click="quickLogin(user.email)">
                <div>
                  <div class="small fw-medium">{{ user.firstName }} {{ user.lastName }}</div>
                  <div class="x-small text-muted">{{ user.email }}</div>
                </div>
                <span class="badge" style="background:rgba(26,34,48,.1);color:#1a2230">
                  {{ user.accountType }}
                </span>
              </button>
            </div>
            <p class="x-small text-muted text-center mt-2 mb-0">Cliquez sur un compte puis connectez-vous</p>
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
import { LogIn, User, Lock } from 'lucide-vue-next'
import { login } from '@/utils/auth'
import { users } from '@/data/mockData'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')

const handleLogin = () => {
  error.value = ''
  const user = login(email.value, password.value)
  if (user) {
    router.push('/')
  } else {
    error.value = 'Email ou mot de passe incorrect'
  }
}

const quickLogin = (userEmail) => {
  const user = users.find(u => u.email === userEmail)
  if (user) {
    email.value = user.email
    password.value = user.password
  }
}
</script>