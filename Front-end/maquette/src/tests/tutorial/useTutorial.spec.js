import { describe, it, expect, beforeEach, vi } from 'vitest'

// ── helpers ──────────────────────────────────────────────────────────────────
// We mock the auth util so tests don't depend on localStorage user format
vi.mock('@/utils/auth', () => ({
  getCurrentUser: vi.fn(),
}))

import { getCurrentUser } from '@/utils/auth'
import { useTutorial } from '@/composables/useTutorial'

const LS_LAST_ROLE = 'benerun_tutorial_last_role'
const LS_ONBOARDING_DONE = 'benerun_onboarding_done'

// Reset shared reactive state between tests by re-importing the composable
// (module-level refs persist across imports — we reset via localStorage.clear in setup.js)

describe('useTutorial', () => {
  describe('needsOnboarding', () => {
    it('returns true when no onboarding key in localStorage', () => {
      const { needsOnboarding } = useTutorial()
      expect(needsOnboarding()).toBe(true)
    })

    it('returns false after markOnboardingDone', () => {
      getCurrentUser.mockReturnValue({ role: 'volunteer' })
      const { markOnboardingDone, needsOnboarding } = useTutorial()
      markOnboardingDone()
      expect(needsOnboarding()).toBe(false)
    })
  })

  describe('markOnboardingDone', () => {
    it('sets onboarding done flag in localStorage', () => {
      getCurrentUser.mockReturnValue({ role: 'volunteer' })
      const { markOnboardingDone } = useTutorial()
      markOnboardingDone()
      expect(localStorage.getItem(LS_ONBOARDING_DONE)).toBe('1')
    })

    it('stores current role as last_seen_role', () => {
      getCurrentUser.mockReturnValue({ role: 'admin' })
      const { markOnboardingDone } = useTutorial()
      markOnboardingDone()
      expect(localStorage.getItem(LS_LAST_ROLE)).toBe('admin')
    })

    it('does nothing when user is null', () => {
      getCurrentUser.mockReturnValue(null)
      const { markOnboardingDone } = useTutorial()
      markOnboardingDone()
      expect(localStorage.getItem(LS_ONBOARDING_DONE)).toBeNull()
    })
  })

  describe('checkRoleChange', () => {
    it('does nothing when no user', () => {
      getCurrentUser.mockReturnValue(null)
      const { checkRoleChange, isGuideOpen } = useTutorial()
      checkRoleChange()
      expect(isGuideOpen.value).toBe(false)
    })

    it('does not open guide on first login (no previous role stored)', () => {
      getCurrentUser.mockReturnValue({ role: 'volunteer' })
      const { checkRoleChange, isGuideOpen } = useTutorial()
      // No prior role in localStorage
      checkRoleChange()
      expect(isGuideOpen.value).toBe(false)
    })

    it('stores the current role after checkRoleChange', () => {
      getCurrentUser.mockReturnValue({ role: 'volunteer' })
      const { checkRoleChange } = useTutorial()
      checkRoleChange()
      expect(localStorage.getItem(LS_LAST_ROLE)).toBe('volunteer')
    })

    it('opens guide with new-role steps when role changed', () => {
      // Simulate user previously being volunteer, now mission_manager
      localStorage.setItem(LS_LAST_ROLE, 'volunteer')
      getCurrentUser.mockReturnValue({ role: 'mission_manager' })

      const { checkRoleChange, isGuideOpen, guideSteps, isNewRoleGuide } = useTutorial()
      // Reset guide state manually (shared ref from previous tests may be open)
      isGuideOpen.value = false

      checkRoleChange()

      expect(isGuideOpen.value).toBe(true)
      expect(isNewRoleGuide.value).toBe(true)
      expect(guideSteps.value.length).toBeGreaterThan(0)
      // All shown steps should be "new" steps
      for (const step of guideSteps.value) {
        expect(step.isNew).toBe(true)
      }
    })

    it('updates stored role after detecting a change', () => {
      localStorage.setItem(LS_LAST_ROLE, 'volunteer')
      getCurrentUser.mockReturnValue({ role: 'admin' })

      const { checkRoleChange } = useTutorial()
      checkRoleChange()

      expect(localStorage.getItem(LS_LAST_ROLE)).toBe('admin')
    })

    it('does not open guide when role is unchanged', () => {
      localStorage.setItem(LS_LAST_ROLE, 'volunteer')
      getCurrentUser.mockReturnValue({ role: 'volunteer' })

      const { checkRoleChange, isGuideOpen } = useTutorial()
      isGuideOpen.value = false
      checkRoleChange()

      expect(isGuideOpen.value).toBe(false)
    })
  })

  describe('openFullGuide', () => {
    it('opens guide with full role steps', () => {
      getCurrentUser.mockReturnValue({ role: 'volunteer' })
      const { openFullGuide, isGuideOpen, guideSteps, isNewRoleGuide } = useTutorial()
      openFullGuide()

      expect(isGuideOpen.value).toBe(true)
      expect(isNewRoleGuide.value).toBe(false)
      expect(guideSteps.value.length).toBeGreaterThan(0)
    })

    it('does nothing when user is null', () => {
      getCurrentUser.mockReturnValue(null)
      // The shared ref may have been set to true by a previous test — reset explicitly
      const { openFullGuide, isGuideOpen } = useTutorial()
      isGuideOpen.value = false
      openFullGuide()
      expect(isGuideOpen.value).toBe(false)
    })

    it('uses correct color for each role', () => {
      const roleColors = {
        volunteer: '#c5d82e',
        mission_manager: '#3b82f6',
        admin: '#f59e0b',
        superadmin: '#ef4444',
      }
      for (const [role, color] of Object.entries(roleColors)) {
        getCurrentUser.mockReturnValue({ role })
        const { openFullGuide, guideColor } = useTutorial()
        openFullGuide()
        expect(guideColor.value).toBe(color)
      }
    })
  })

  describe('closeGuide', () => {
    it('sets isGuideOpen to false', () => {
      getCurrentUser.mockReturnValue({ role: 'volunteer' })
      const { openFullGuide, closeGuide, isGuideOpen } = useTutorial()
      openFullGuide()
      expect(isGuideOpen.value).toBe(true)
      closeGuide()
      expect(isGuideOpen.value).toBe(false)
    })
  })
})
