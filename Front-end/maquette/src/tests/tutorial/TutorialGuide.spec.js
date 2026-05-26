import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref } from 'vue'

// ── shared reactive state mock ────────────────────────────────────────────────
const mockIsGuideOpen = ref(false)
const mockGuideSteps = ref([])
const mockGuideTitle = ref('')
const mockGuideColor = ref('#c5d82e')
const mockGuideRole = ref('volunteer')
const mockIsNewRoleGuide = ref(false)
const mockCloseGuide = vi.fn(() => { mockIsGuideOpen.value = false })

vi.mock('@/composables/useTutorial', () => ({
  useTutorial: vi.fn(() => ({
    isGuideOpen: mockIsGuideOpen,
    guideSteps: mockGuideSteps,
    guideTitle: mockGuideTitle,
    guideColor: mockGuideColor,
    guideRole: mockGuideRole,
    isNewRoleGuide: mockIsNewRoleGuide,
    closeGuide: mockCloseGuide,
  })),
}))

import TutorialGuide from '@/components/tutorial/TutorialGuide.vue'
import { createRouter, createMemoryHistory } from 'vue-router'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [{ path: '/', component: { template: '<div />' } }],
})

const SAMPLE_STEPS = [
  {
    id: 'step-1',
    icon: 'Heart',
    title: 'Bienvenue !',
    description: 'Description step 1',
    isNew: false,
  },
  {
    id: 'step-2',
    icon: 'Award',
    title: 'Étape 2',
    description: 'Description step 2',
    isNew: true,
    route: '/missions',
    cta: 'Voir les missions',
  },
  {
    id: 'step-3',
    icon: 'Star',
    title: 'Étape 3',
    description: 'Description step 3',
    isNew: false,
  },
]

async function mountGuide(open = true, steps = SAMPLE_STEPS) {
  mockIsGuideOpen.value = open
  mockGuideSteps.value = steps
  mockGuideTitle.value = 'Guide Test'
  mockGuideColor.value = '#c5d82e'
  mockIsNewRoleGuide.value = false

  await router.push('/')
  return mount(TutorialGuide, {
    global: {
      plugins: [router],
      stubs: { Teleport: true }, // render Teleport inline for test
    },
  })
}

describe('TutorialGuide.vue', () => {
  beforeEach(() => {
    mockCloseGuide.mockClear()
  })

  it('renders nothing when isGuideOpen is false', async () => {
    const wrapper = await mountGuide(false)
    expect(wrapper.find('.tutorial-modal').exists()).toBe(false)
  })

  it('renders the modal when isGuideOpen is true', async () => {
    const wrapper = await mountGuide(true)
    expect(wrapper.find('.tutorial-modal').exists()).toBe(true)
  })

  it('displays the guide title', async () => {
    const wrapper = await mountGuide(true)
    expect(wrapper.text()).toContain('Guide Test')
  })

  it('shows step 1 content on initial render', async () => {
    const wrapper = await mountGuide(true)
    expect(wrapper.text()).toContain('Bienvenue !')
    expect(wrapper.text()).toContain('Description step 1')
  })

  it('shows the correct step counter', async () => {
    const wrapper = await mountGuide(true)
    expect(wrapper.text()).toContain('Étape 1 / 3')
  })

  it('shows correct number of navigation dots', async () => {
    const wrapper = await mountGuide(true)
    const dots = wrapper.findAll('.tutorial-dot')
    expect(dots).toHaveLength(3)
  })

  it('first dot is active on step 0', async () => {
    const wrapper = await mountGuide(true)
    const activeDot = wrapper.find('.tutorial-dot--active')
    expect(activeDot.exists()).toBe(true)
    const dots = wrapper.findAll('.tutorial-dot')
    expect(dots[0].classes()).toContain('tutorial-dot--active')
  })

  it('shows "Suivant" button, not "Terminer", on non-last step', async () => {
    const wrapper = await mountGuide(true)
    expect(wrapper.text()).toContain('Suivant')
    expect(wrapper.text()).not.toContain('Terminer')
  })

  it('navigates to next step on "Suivant" click', async () => {
    const wrapper = await mountGuide(true)
    const nextBtn = wrapper.find('.tutorial-nav--next')
    await nextBtn.trigger('click')
    expect(wrapper.text()).toContain('Étape 2')
    expect(wrapper.text()).toContain('Description step 2')
  })

  it('shows "Nouvelle fonctionnalité" badge on new steps', async () => {
    const wrapper = await mountGuide(true)
    // Navigate to step 2 which has isNew:true
    await wrapper.find('.tutorial-nav--next').trigger('click')
    expect(wrapper.text()).toContain('Nouvelle fonctionnalité')
  })

  it('does NOT show "Nouvelle fonctionnalité" badge on regular steps', async () => {
    const wrapper = await mountGuide(true)
    // Step 1 has isNew:false
    expect(wrapper.text()).not.toContain('Nouvelle fonctionnalité')
  })

  it('shows CTA button on step with route', async () => {
    const wrapper = await mountGuide(true)
    await wrapper.find('.tutorial-nav--next').trigger('click')
    expect(wrapper.find('.tutorial-cta').exists()).toBe(true)
    expect(wrapper.find('.tutorial-cta').text()).toContain('Voir les missions')
  })

  it('does NOT show CTA button on step without route', async () => {
    const wrapper = await mountGuide(true)
    // Step 1 has no route
    expect(wrapper.find('.tutorial-cta').exists()).toBe(false)
  })

  it('shows "Terminer" button on last step', async () => {
    const wrapper = await mountGuide(true)
    // Navigate to last step
    await wrapper.find('.tutorial-nav--next').trigger('click')
    await wrapper.find('.tutorial-nav--next').trigger('click')

    // Validate current step to unlock final completion action
    await wrapper.find('.tutorial-check').trigger('click')

    expect(wrapper.text()).toContain('Terminer')
    expect(wrapper.find('.tutorial-nav--done').exists()).toBe(true)
  })

  it('calls closeGuide when close button is clicked', async () => {
    const wrapper = await mountGuide(true)
    await wrapper.find('.tutorial-close').trigger('click')
    expect(mockCloseGuide).toHaveBeenCalledOnce()
  })

  it('calls closeGuide when "Terminer" is clicked on last step', async () => {
    const wrapper = await mountGuide(true)
    await wrapper.find('.tutorial-nav--next').trigger('click')
    await wrapper.find('.tutorial-nav--next').trigger('click')

    // Last step must be marked as done before close is enabled
    await wrapper.find('.tutorial-check').trigger('click')

    await wrapper.find('.tutorial-nav--done').trigger('click')
    expect(mockCloseGuide).toHaveBeenCalled()
  })

  it('"Précédent" button is disabled on first step', async () => {
    const wrapper = await mountGuide(true)
    const prevBtn = wrapper.find('.tutorial-nav--prev')
    expect(prevBtn.attributes('disabled')).toBeDefined()
  })

  it('"Précédent" button is enabled on step > 0', async () => {
    const wrapper = await mountGuide(true)
    await wrapper.find('.tutorial-nav--next').trigger('click')
    const prevBtn = wrapper.find('.tutorial-nav--prev')
    expect(prevBtn.attributes('disabled')).toBeUndefined()
  })

  it('navigates back when "Précédent" is clicked', async () => {
    const wrapper = await mountGuide(true)
    await wrapper.find('.tutorial-nav--next').trigger('click')
    expect(wrapper.text()).toContain('Étape 2')
    await wrapper.find('.tutorial-nav--prev').trigger('click')
    expect(wrapper.text()).toContain('Bienvenue !')
  })

  it('renders with a single step without crashing', async () => {
    const wrapper = await mountGuide(true, [SAMPLE_STEPS[0]])
    expect(wrapper.find('.tutorial-modal').exists()).toBe(true)
    expect(wrapper.find('.tutorial-nav--done').exists()).toBe(true)
  })
})
