import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'

const pushMock = vi.fn()
const routeMock = { query: {} }

vi.mock('vue-router', () => ({
  useRouter: () => ({ push: pushMock }),
  useRoute: () => routeMock,
}))

vi.mock('@/services/api', () => ({
  default: {
    post: vi.fn(),
  },
}))

vi.mock('@/utils/passwordPolicy', () => ({
  PASSWORD_HINT: 'Au moins 10 caractères.',
  validatePasswordStrength: vi.fn((password) => (password.length < 10 ? 'Le mot de passe doit contenir au moins 10 caractères.' : '')),
}))

import api from '@/services/api'
import ResetPassword from '@/views/ResetPassword.vue'

const mountResetPassword = () => mount(ResetPassword, {
  global: {
    stubs: {
      RouterLink: true,
    },
  },
})

describe('ResetPassword.vue', () => {
  beforeEach(() => {
    pushMock.mockClear()
    api.post.mockReset()
    routeMock.query = {}
  })

  it('affiche une erreur live quand l email est invalide', async () => {
    const wrapper = mountResetPassword()
    const emailInput = wrapper.find('input[type="email"]')

    await emailInput.setValue('invalid-email')
    await nextTick()

    expect(wrapper.text()).toContain("Format d'email invalide")
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
  })

  it('bloque la réinitialisation quand les mots de passe ne correspondent pas', async () => {
    routeMock.query = { token: 'token-123', email: 'emma@example.com' }
    api.post.mockResolvedValueOnce({ data: { valid: true } })

    const wrapper = mountResetPassword()
    await flushPromises()

    const passwordInputs = wrapper.findAll('input[type="password"]')
    await passwordInputs[0].setValue('password123')
    await passwordInputs[1].setValue('password321')
    await nextTick()

    expect(wrapper.text()).toContain('Les mots de passe ne correspondent pas')
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
  })
})