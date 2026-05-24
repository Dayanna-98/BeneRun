import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'

// ── mocks ────────────────────────────────────────────────────────────────────
vi.mock('@/utils/auth', () => ({
  getCurrentUser: vi.fn(),
}))
vi.mock('@/composables/useTutorial', () => ({
  useTutorial: vi.fn(() => ({ markOnboardingDone: vi.fn() })),
}))

import { getCurrentUser } from '@/utils/auth'
import Welcome from '@/views/Welcome.vue'

// Minimal router so useRouter() works inside the component
const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/', component: { template: '<div />' } },
    { path: '/welcome', component: Welcome },
  ],
})

async function mountWelcome(userOverride = {}) {
  getCurrentUser.mockReturnValue({
    prenom_utilisateur: 'Marie',
    role: 'volunteer',
    ...userOverride,
  })
  await router.push('/welcome')
  return mount(Welcome, {
    global: { plugins: [router] },
  })
}

describe('Welcome.vue', () => {
  it('renders without crashing', async () => {
    const wrapper = await mountWelcome()
    expect(wrapper.exists()).toBe(true)
  })

  it('displays the user first name in the greeting', async () => {
    const wrapper = await mountWelcome({ prenom_utilisateur: 'Marie' })
    expect(wrapper.text()).toContain('Marie')
  })

  it('falls back gracefully when first name is missing', async () => {
    const wrapper = await mountWelcome({ prenom_utilisateur: undefined })
    // Should not throw; "ici" is the fallback
    expect(wrapper.text()).toContain('ici')
  })

  it('starts on step 0', async () => {
    const wrapper = await mountWelcome()
    // Step 0 shows "Bienvenue" heading
    expect(wrapper.text()).toContain('Bienvenue')
  })

  it('advances to the next step when "Suivant" is clicked', async () => {
    const wrapper = await mountWelcome()
    const nextBtn = wrapper.find('button.welcome-next-btn')
    await nextBtn.trigger('click')
    // Step 1 shows role information
    expect(wrapper.text()).toContain('Votre rôle')
  })

  it('shows "Passer l\'introduction" skip link on every step', async () => {
    const wrapper = await mountWelcome()
    expect(wrapper.text()).toContain('Passer')
  })

  it('"Précédent" button is absent on the first step', async () => {
    const wrapper = await mountWelcome()
    const prevBtn = wrapper.findAll('button').find((b) => b.text().includes('Précédent'))
    expect(prevBtn).toBeUndefined()
  })

  it('"Précédent" button appears after moving to step 1', async () => {
    const wrapper = await mountWelcome()
    await wrapper.find('button.welcome-next-btn').trigger('click')
    const prevBtn = wrapper.findAll('button').find((b) => b.text().includes('Précédent'))
    expect(prevBtn).toBeDefined()
  })

  it('shows "Accéder à l\'app" button on the last step', async () => {
    const wrapper = await mountWelcome()
    // Navigate to last step (step 3)
    for (let i = 0; i < 3; i++) {
      await wrapper.find('button.welcome-next-btn').trigger('click')
    }
    expect(wrapper.find('button.welcome-next-btn').text()).toContain('Accéder')
  })

  it('has 4 navigation dots', async () => {
    const wrapper = await mountWelcome()
    const dots = wrapper.findAll('.welcome-dot')
    expect(dots).toHaveLength(4)
  })

  it('first dot is active on step 0', async () => {
    const wrapper = await mountWelcome()
    const activeDots = wrapper.findAll('.welcome-dot--active')
    expect(activeDots).toHaveLength(1)
    // The first dot should be active
    const firstDot = wrapper.findAll('.welcome-dot')[0]
    expect(firstDot.classes()).toContain('welcome-dot--active')
  })
})
