import { describe, it, expect } from 'vitest'
import { TUTORIAL_CONTENT, getNewStepsForRole, getTutorialStepForRoute } from '@/data/tutorialContent'

describe('tutorialContent', () => {
  const ROLES = ['volunteer', 'mission_manager', 'admin', 'superadmin']

  it('exports content for all 4 roles', () => {
    for (const role of ROLES) {
      expect(TUTORIAL_CONTENT[role], `missing role "${role}"`).toBeDefined()
    }
  })

  it('every role has a label, color and steps array', () => {
    for (const role of ROLES) {
      const content = TUTORIAL_CONTENT[role]
      expect(typeof content.label).toBe('string')
      expect(content.label.length).toBeGreaterThan(0)
      expect(typeof content.color).toBe('string')
      expect(content.color).toMatch(/^#/)
      expect(Array.isArray(content.steps)).toBe(true)
      expect(content.steps.length).toBeGreaterThan(0)
    }
  })

  it('every step has required fields: id, category, icon, title, description', () => {
    for (const role of ROLES) {
      for (const step of TUTORIAL_CONTENT[role].steps) {
        expect(step.id, `${role} step missing id`).toBeTruthy()
        expect(step.category, `${role}/${step.id} missing category`).toBeTruthy()
        expect(step.icon, `${role}/${step.id} missing icon`).toBeTruthy()
        expect(step.title, `${role}/${step.id} missing title`).toBeTruthy()
        expect(step.description, `${role}/${step.id} missing description`).toBeTruthy()
      }
    }
  })

  it('step IDs are unique within each role', () => {
    for (const role of ROLES) {
      const ids = TUTORIAL_CONTENT[role].steps.map((s) => s.id)
      const uniqueIds = new Set(ids)
      expect(uniqueIds.size).toBe(ids.length)
    }
  })

  it('steps with a route also have a cta label', () => {
    for (const role of ROLES) {
      for (const step of TUTORIAL_CONTENT[role].steps) {
        if (step.route) {
          expect(step.cta, `${role}/${step.id} has route but no cta`).toBeTruthy()
        }
      }
    }
  })

  describe('getNewStepsForRole', () => {
    it('returns full steps for volunteer (no newStepIds)', () => {
      const steps = getNewStepsForRole('volunteer')
      expect(steps).toEqual(TUTORIAL_CONTENT.volunteer.steps)
    })

    it('returns only new steps for mission_manager', () => {
      const steps = getNewStepsForRole('mission_manager')
      const ids = steps.map((s) => s.id)
      expect(ids).toEqual(expect.arrayContaining(TUTORIAL_CONTENT.mission_manager.newStepIds))
      // Should not contain steps NOT in newStepIds
      for (const id of ids) {
        expect(TUTORIAL_CONTENT.mission_manager.newStepIds).toContain(id)
      }
    })

    it('returns only new steps for admin', () => {
      const steps = getNewStepsForRole('admin')
      const ids = steps.map((s) => s.id)
      for (const id of ids) {
        expect(TUTORIAL_CONTENT.admin.newStepIds).toContain(id)
      }
    })

    it('returns only new steps for superadmin', () => {
      const steps = getNewStepsForRole('superadmin')
      const ids = steps.map((s) => s.id)
      for (const id of ids) {
        expect(TUTORIAL_CONTENT.superadmin.newStepIds).toContain(id)
      }
    })

    it('returns empty array for unknown role', () => {
      const steps = getNewStepsForRole('unknown_role')
      expect(steps).toEqual([])
    })

    it('finds the tutorial step attached to a route for the current role chain', () => {
      const match = getTutorialStepForRoute('admin', '/statistics')
      expect(match).toBeTruthy()
      expect(match.step.id).toBe('statistics')
      expect(match.index).toBeGreaterThanOrEqual(0)
    })

    it('all new steps are marked isNew:true', () => {
      for (const role of ['mission_manager', 'admin', 'superadmin']) {
        const steps = getNewStepsForRole(role)
        for (const step of steps) {
          expect(step.isNew, `${role}/${step.id} should have isNew:true`).toBe(true)
        }
      }
    })
  })
})
