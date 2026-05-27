import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'

const pushMock = vi.fn()

vi.mock('vue-router', () => ({
  useRouter: () => ({ push: pushMock }),
}))

vi.mock('@/services/userService', () => ({
  default: {
    register: vi.fn(),
    login: vi.fn(),
  },
}))

vi.mock('@/utils/auth', () => ({
  persistAuthSession: vi.fn(),
}))

vi.mock('@/composables/useToast', () => ({
  useToast: vi.fn(() => ({ success: vi.fn(), error: vi.fn(), warning: vi.fn(), info: vi.fn() })),
}))

vi.mock('@/utils/passwordPolicy', () => ({
  PASSWORD_HINT: 'Au moins 10 caractères.',
  validatePasswordStrength: vi.fn((password) => (password.length < 10 ? 'Le mot de passe doit contenir au moins 10 caractères.' : '')),
}))

import userService from '@/services/userService'
import Register from '@/views/Register.vue'

const mountRegister = () => mount(Register, {
  global: {
    stubs: {
      RouterLink: true,
    },
  },
})

describe('Register.vue', () => {
  beforeEach(() => {
    pushMock.mockClear()
    userService.register.mockReset()
    userService.login.mockReset()
  })

  it('affiche une erreur live quand les mots de passe ne correspondent pas', async () => {
    const wrapper = mountRegister()
    const inputs = wrapper.findAll('input')

    await inputs[0].setValue('Marie')
    await inputs[1].setValue('Dupont')
    await inputs[2].setValue('marie@example.com')
    await inputs[3].setValue('password123')
    await inputs[4].setValue('password321')
    await nextTick()

    expect(wrapper.text()).toContain('Les mots de passe ne correspondent pas')
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
  })

  it('crée un compte puis connecte automatiquement', async () => {
    userService.register.mockResolvedValue({})
    userService.login.mockResolvedValue({
      user: { id: 42, role: 'volunteer' },
      token: 'token-123',
    })

    const wrapper = mountRegister()
    const inputs = wrapper.findAll('input')

    await inputs[0].setValue('Marie')
    await inputs[1].setValue('Dupont')
    await inputs[2].setValue('marie@example.com')
    await inputs[3].setValue('password123')
    await inputs[4].setValue('password123')

    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(userService.register).toHaveBeenCalled()
    expect(userService.login).toHaveBeenCalledWith('marie@example.com', 'password123')
    expect(pushMock).toHaveBeenCalledWith('/welcome')
  })
})