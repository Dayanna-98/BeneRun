import { ref } from 'vue'
import { getCurrentUser } from '@/utils/auth'
import { TUTORIAL_CONTENT, getNewStepsForRole, getTutorialContentForRole, getTutorialStepForRoute, resolveTutorialRole } from '@/data/tutorialContent'

const LS_LAST_ROLE = 'benerun_tutorial_last_role'
const LS_ONBOARDING_DONE = 'benerun_onboarding_done'
const LS_AUTO_ROUTE_PREFIX = 'benerun_tutorial_seen_routes_'

// État global partagé entre composants
const isGuideOpen = ref(false)
const guideSteps = ref([])
const guideTitle = ref('')
const guideColor = ref('#c5d82e')
const guideRole = ref('volunteer')
const isNewRoleGuide = ref(false)
const guideStartIndex = ref(0)
const guideRestoreLastIndex = ref(true)

const getAutoRouteKey = (role) => `${LS_AUTO_ROUTE_PREFIX}${resolveTutorialRole(role || 'volunteer')}`

const readSeenRoutes = (role) => {
  const raw = localStorage.getItem(getAutoRouteKey(role))
  if (!raw) return []

  try {
    const parsed = JSON.parse(raw)
    return Array.isArray(parsed) ? parsed.filter(Boolean) : []
  } catch {
    return []
  }
}

const writeSeenRoutes = (role, routes) => {
  localStorage.setItem(getAutoRouteKey(role), JSON.stringify(Array.from(new Set(routes.filter(Boolean)))))
}

const openGuide = ({ title, color, steps, role, isNewRole = false, startIndex = 0, restoreLastIndex = true }) => {
  if (!steps?.length) return false

  guideTitle.value = title
  guideColor.value = color
  guideSteps.value = steps
  guideRole.value = role
  isNewRoleGuide.value = isNewRole
  guideStartIndex.value = Math.max(0, Math.min(Number(startIndex) || 0, steps.length - 1))
  guideRestoreLastIndex.value = restoreLastIndex
  isGuideOpen.value = true

  return true
}

export function useTutorial() {
  /** Ouvre le guide complet pour le rôle courant. */
  function openFullGuide() {
    const user = getCurrentUser()
    if (!user) return
    const role = resolveTutorialRole(user.role || 'volunteer')
    const content = getTutorialContentForRole(role)
    if (!content) return

    openGuide({
      title: `Guide — ${content.label}`,
      color: content.color,
      steps: content.steps,
      role,
      isNewRole: false,
      startIndex: 0,
      restoreLastIndex: true,
    })
  }

  function openGuideForRoute(routePath) {
    const user = getCurrentUser()
    if (!user || !routePath) return false

    const role = resolveTutorialRole(user.role || 'volunteer')
    const match = getTutorialStepForRoute(role, routePath)
    if (!match) return false

    const seenRoutes = readSeenRoutes(role)
    if (seenRoutes.includes(routePath)) return false

    writeSeenRoutes(role, [...seenRoutes, routePath])

    return openGuide({
      title: `Guide — ${match.content.label}`,
      color: match.content.color,
      steps: match.content.steps,
      role,
      isNewRole: false,
      startIndex: match.index,
      restoreLastIndex: false,
    })
  }

  /**
   * Vérifie si le rôle de l'utilisateur a changé depuis la dernière session.
   * Si oui, ouvre automatiquement le guide des nouvelles fonctionnalités.
   * Doit être appelé dans onMounted depuis App.vue.
   */
  function checkRoleChange() {
    const user = getCurrentUser()
    if (!user) return

    const currentRole = resolveTutorialRole(user.role || 'volunteer')
    const lastRole = localStorage.getItem(LS_LAST_ROLE)

    if (lastRole && lastRole !== currentRole) {
      // Rôle changé : montrer les nouvelles fonctionnalités
      const content = TUTORIAL_CONTENT[currentRole]
      if (!content) return

      const newSteps = getNewStepsForRole(currentRole)
      if (!newSteps.length) return

      openGuide({
        title: `Nouvelles fonctionnalités — ${content.label}`,
        color: content.color,
        steps: newSteps,
        role: currentRole,
        isNewRole: true,
        startIndex: 0,
        restoreLastIndex: true,
      })
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
    guideRole,
    isNewRoleGuide,
    guideStartIndex,
    guideRestoreLastIndex,
    openFullGuide,
    openGuideForRoute,
    checkRoleChange,
    markOnboardingDone,
    needsOnboarding,
    closeGuide,
  }
}
