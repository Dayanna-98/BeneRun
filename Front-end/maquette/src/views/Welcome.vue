<template>
  <div class="welcome-shell">
    <!-- Background aurora -->
    <div class="welcome-aurora welcome-aurora--one" />
    <div class="welcome-aurora welcome-aurora--two" />

    <div class="welcome-content">
      <!-- Logo -->
      <div class="text-center mb-4">
        <div class="d-inline-block bg-white rounded-3 p-3 shadow mb-3">
          <img src="@/assets/logo.png" alt="Béné'Run" style="height:52px;width:auto" />
        </div>
      </div>

      <!-- Card -->
      <div class="welcome-card">
        <Transition name="step-fade" mode="out-in">
          <div :key="step" class="welcome-step">

            <!-- Step 0 : accueil personnalisé -->
            <template v-if="step === 0">
              <div class="welcome-icon-wrap" style="background: rgba(197,216,46,0.15)">
                <Sparkles class="welcome-icon" style="color:#7b8c10" />
              </div>
              <h1 class="welcome-title">Bienvenue, {{ firstName }} ! 🎉</h1>
              <p class="welcome-text">
                Votre compte a été créé avec succès. Vous rejoignez la communauté des bénévoles
                de <strong>Béné'Run</strong>, la plateforme officielle de Running Geneva.
              </p>
              <p class="welcome-text text-muted small">
                Ensemble, nous rendons chaque course possible grâce à votre engagement.
              </p>
            </template>

            <!-- Step 1 : votre rôle -->
            <template v-else-if="step === 1">
              <div class="welcome-icon-wrap" style="background: rgba(197,216,46,0.15)">
                <ShieldCheck class="welcome-icon" style="color:#7b8c10" />
              </div>
              <h2 class="welcome-title">Votre rôle</h2>
              <div class="welcome-role-badge">
                <Heart style="width:16px;height:16px" />
                Bénévole
              </div>
              <p class="welcome-text">
                En tant que <strong>bénévole</strong>, vous pouvez :
              </p>
              <ul class="welcome-list">
                <li><ListChecks class="welcome-list__icon" /> Parcourir et rejoindre des missions</li>
                <li><CalendarDays class="welcome-list__icon" /> Gérer votre planning personnel</li>
                <li><MessageCircle class="welcome-list__icon" /> Communiquer avec votre équipe</li>
                <li><Award class="welcome-list__icon" /> Gagner des badges et compétences</li>
              </ul>
            </template>

            <!-- Step 2 : comment ça marche -->
            <template v-else-if="step === 2">
              <div class="welcome-icon-wrap" style="background: rgba(59,130,246,0.12)">
                <Map class="welcome-icon" style="color:#2563eb" />
              </div>
              <h2 class="welcome-title">Comment ça marche ?</h2>
              <div class="welcome-steps-list">
                <div class="welcome-step-item">
                  <span class="welcome-step-num">1</span>
                  <div>
                    <strong>Parcourez les missions</strong>
                    <p class="mb-0 small text-muted">Trouvez une mission qui vous correspond</p>
                  </div>
                </div>
                <div class="welcome-step-item">
                  <span class="welcome-step-num">2</span>
                  <div>
                    <strong>Postulez</strong>
                    <p class="mb-0 small text-muted">Envoyez votre candidature en un clic</p>
                  </div>
                </div>
                <div class="welcome-step-item">
                  <span class="welcome-step-num">3</span>
                  <div>
                    <strong>Participez & progressez</strong>
                    <p class="mb-0 small text-muted">Gagnez des badges et des compétences</p>
                  </div>
                </div>
              </div>
            </template>

            <!-- Step 3 : prêt -->
            <template v-else-if="step === 3">
              <div class="welcome-icon-wrap" style="background: rgba(197,216,46,0.15)">
                <Rocket class="welcome-icon" style="color:#7b8c10" />
              </div>
              <h2 class="welcome-title">Vous êtes prêt·e !</h2>
              <p class="welcome-text">
                Le guide d'utilisation est accessible à tout moment via le bouton
                <span class="welcome-inline-badge"><HelpCircle style="width:12px;height:12px" /> ?</span>
                en haut de l'écran.
              </p>
              <p class="welcome-text text-muted small">
                Commencez par explorer les missions disponibles ou complétez votre profil pour augmenter
                vos chances d'être sélectionné·e.
              </p>
            </template>

          </div>
        </Transition>

        <!-- Progress dots -->
        <div class="welcome-dots">
          <span
            v-for="i in totalSteps"
            :key="i"
            class="welcome-dot"
            :class="{ 'welcome-dot--active': step === i - 1 }"
          />
        </div>

        <!-- Actions -->
        <div class="welcome-actions">
          <button
            v-if="step > 0"
            class="btn btn-outline-secondary btn-sm"
            @click="step--"
          >
            <ChevronLeft style="width:14px;height:14px" /> Précédent
          </button>
          <span v-else />

          <button
            v-if="step < totalSteps - 1"
            class="btn btn-primary btn-sm welcome-next-btn"
            @click="step++"
          >
            Suivant <ChevronRight style="width:14px;height:14px" />
          </button>

          <button
            v-else
            class="btn btn-primary btn-sm welcome-next-btn"
            :disabled="isLoading"
            @click="finish"
          >
            <span v-if="isLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" />
            Accéder à l'app <ArrowRight style="width:14px;height:14px" />
          </button>
        </div>

        <!-- Skip link -->
        <p class="text-center mt-3 mb-0">
          <button
            class="btn btn-link btn-sm text-muted x-small p-0"
            @click="finish"
          >
            Passer l'introduction
          </button>
        </p>
      </div>

      <p class="text-center text-white-50 x-small mt-3 mb-0">© 2025 Béné'Run • Plateforme de bénévolat</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import {
  Sparkles, ShieldCheck, Heart, ListChecks, CalendarDays,
  MessageCircle, Award, Map, Rocket, HelpCircle,
  ChevronLeft, ChevronRight, ArrowRight,
} from 'lucide-vue-next'
import { getCurrentUser } from '@/utils/auth'
import { useTutorial } from '@/composables/useTutorial'

const router = useRouter()
const { markOnboardingDone } = useTutorial()

const user = getCurrentUser()
const firstName = computed(() => user?.prenom_utilisateur || user?.firstName || 'ici')

const step = ref(0)
const totalSteps = 4
const isLoading = ref(false)

function finish() {
  isLoading.value = true
  markOnboardingDone()
  router.replace('/')
}
</script>

<style scoped>
.welcome-shell {
  min-height: 100vh;
  background: linear-gradient(135deg, #1a2230 0%, #2d3a4a 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
}

.welcome-aurora {
  position: fixed;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(80px);
}
.welcome-aurora--one {
  width: 400px; height: 400px;
  top: -120px; left: -80px;
  background: radial-gradient(circle, rgba(197,216,46,0.18) 0%, transparent 70%);
}
.welcome-aurora--two {
  width: 350px; height: 350px;
  bottom: -100px; right: -60px;
  background: radial-gradient(circle, rgba(61,78,106,0.22) 0%, transparent 70%);
}

.welcome-content {
  width: 100%;
  max-width: 440px;
  position: relative;
  z-index: 1;
}

.welcome-card {
  background: #fff;
  border-radius: 1.25rem;
  padding: 2rem 1.75rem 1.25rem;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
}

.welcome-step {
  min-height: 260px;
}

.welcome-icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.25rem;
}
.welcome-icon {
  width: 28px;
  height: 28px;
}

.welcome-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.75rem;
}
.welcome-text {
  font-size: 0.9rem;
  color: #374151;
  line-height: 1.6;
  margin-bottom: 0.5rem;
}

.welcome-role-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(197,216,46,0.15);
  color: #4a550a;
  font-weight: 600;
  font-size: 0.8rem;
  border-radius: 999px;
  padding: 4px 12px;
  margin-bottom: 0.875rem;
}

.welcome-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.welcome-list li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
}
.welcome-list__icon {
  width: 15px;
  height: 15px;
  color: #c5d82e;
  flex-shrink: 0;
}

.welcome-steps-list {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
  margin-top: 0.5rem;
}
.welcome-step-item {
  display: flex;
  align-items: flex-start;
  gap: 0.875rem;
}
.welcome-step-num {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #c5d82e;
  color: #1a2230;
  font-weight: 700;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.welcome-inline-badge {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  background: rgba(197,216,46,0.15);
  color: #4a550a;
  border-radius: 999px;
  padding: 1px 8px;
  font-size: 0.78rem;
  font-weight: 600;
}

/* Dots */
.welcome-dots {
  display: flex;
  gap: 6px;
  justify-content: center;
  margin: 1.25rem 0 1rem;
}
.welcome-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #e5e7eb;
  transition: background 0.2s, transform 0.2s;
}
.welcome-dot--active {
  background: #c5d82e;
  transform: scale(1.4);
}

/* Actions */
.welcome-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.welcome-next-btn {
  margin-left: auto;
}

/* Transition */
.step-fade-enter-active,
.step-fade-leave-active {
  transition: opacity 0.2s, transform 0.2s;
}
.step-fade-enter-from { opacity: 0; transform: translateX(16px); }
.step-fade-leave-to   { opacity: 0; transform: translateX(-16px); }
</style>
