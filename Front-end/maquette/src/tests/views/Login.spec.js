import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'

const pushMock = vi.fn()

vi.mock('vue-router', () => ({
  useRouter: () => ({ push: pushMock }),
}))

vi.mock('@/services/userService', () => ({
  default: {
    login: vi.fn(),
  },
}))

vi.mock('@/utils/auth', () => ({
  persistAuthSession: vi.fn(),
}))

import userService from '@/services/userService'
import Login from '@/views/Login.vue'

const mountLogin = () => mount(Login, {
  global: {
    stubs: {
      RouterLink: true,
    },
  },
})

describe('Login.vue', () => {
  beforeEach(() => {
    pushMock.mockClear()
    userService.login.mockReset()
  })

  it('affiche une erreur live quand l email est invalide', async () => {
    const wrapper = mountLogin()

    const inputs = wrapper.findAll('input')
    await inputs[0].setValue('invalid-email')
    await inputs[1].setValue('secret123')
    await nextTick()

    expect(wrapper.text()).toContain("Format d'email invalide")
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
  })

  it('soumet une connexion valide et redirige', async () => {
    userService.login.mockResolvedValue({
      user: { id: 42, role: 'volunteer' },
      token: 'token-123',
    })

    const wrapper = mountLogin()
    const inputs = wrapper.findAll('input')

    await inputs[0].setValue('emma@example.com')
    await inputs[1].setValue('secret123')

    const submitButton = wrapper.find('button[type="submit"]')
    expect(submitButton.attributes('disabled')).toBeUndefined()

    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(userService.login).toHaveBeenCalledWith('emma@example.com', 'secret123')
    expect(pushMock).toHaveBeenCalledWith('/')
  })
})