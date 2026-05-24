import { ref, watch } from 'vue'
import { getCurrentUser } from '@/utils/auth'
import { TUTORIAL_CONTENT, getNewStepsForRole } from '@/data/tutorialContent'

const LS_LAST_ROLE = 'benerun_tutorial_last_role'
const LS_ONBOARDING_DONE = 'benerun_onboarding_done'

// État global partagé entre composants
const isGuideOpen = ref(false)
const guideSteps = ref([])
const guideTitle = ref('')
const guideColor = ref('#c5d82e')
const isNewRoleGuide = ref(false)

export function useTutorial() {
  /** Ouvre le guide complet pour le rôle courant. */
  function openFullGuide() {
    const user = getCurrentUser()
    if (!user) return
    const role = user.role || 'volunteer'
    const content = TUTORIAL_CONTENT[role]
    if (!content) return

    guideTitle.value = `Guide — ${content.label}`
    guideColor.value = content.color
    guideSteps.value = content.steps
    isNewRoleGuide.value = false
    isGuideOpen.value = true
  }

  /**
   * Vérifie si le rôle de l'utilisateur a changé depuis la dernière session.
   * Si oui, ouvre automatiquement le guide des nouvelles fonctionnalités.
   * Doit être appelé dans onMounted depuis App.vue.
   */
  function checkRoleChange() {
    const user = getCurrentUser()
    if (!user) return

    const currentRole = user.role || 'volunteer'
    const lastRole = localStorage.getItem(LS_LAST_ROLE)

    if (lastRole && lastRole !== currentRole) {
      // Rôle changé : montrer les nouvelles fonctionnalités
      const content = TUTORIAL_CONTENT[currentRole]
      if (!content) return

      const newSteps = getNewStepsForRole(currentRole)
      if (!newSteps.length) return

      guideTitle.value = `Nouvelles fonctionnalités — ${content.label}`
      guideColor.value = content.color
      guideSteps.value = newSteps
      isNewRoleGuide.value = true
      isGuideOpen.value = true
    }

    // Mémorise le rôle vu
    localStorage.setItem(LS_LAST_ROLE, currentRole)
  }

  /** Appelé lors d'une toute première connexion (après Welcome.vue). */
  function markOnboardingDone() {
    const user = getCurrentUser()
    if (!user) return
    localStorage.setItem(LS_ONBOARDING_DONE, '1')
    localStorage.setItem(LS_LAST_ROLE, user.role || 'volunteer')
  }

  /** True si l'onboarding initial n'a pas encore été vu. */
  function needsOnboarding() {
    return localStorage.getItem(LS_ONBOARDING_DONE) !== '1'
  }

  function closeGuide() {
    isGuideOpen.value = false
  }

  return {
    isGuideOpen,
    guideSteps,
    guideTitle,
    guideColor,
    isNewRoleGuide,
    openFullGuide,
    checkRoleChange,
    markOnboardingDone,
    needsOnboarding,
    closeGuide,
  }
}
