import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'

const pushMock = vi.fn()
const routeMock = { params: { id: '12' } }

vi.mock('vue-router', () => ({
  useRouter: () => ({ push: pushMock }),
  useRoute: () => routeMock,
}))

vi.mock('@/services/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}))

vi.mock('@/services/eventService', () => ({
  default: {
    mapApiEvent: vi.fn((event) => ({
      id: String(event.id_evenement),
      name: event.nom_evenement,
      description: event.description_evenement,
      startDate: '2026-06-01',
      endDate: '2026-06-01',
      startTime: '08:00',
      endTime: '18:00',
      date: '2026-06-01',
      location: event.lieu_evenement,
      organizer: event.organisateur_evenement,
      currentVolunteers: 2,
      totalVolunteersNeeded: 10,
      isCancelled: false,
      locationMode: 'manual',
      googleMapsUrl: '',
      radiusMeters: 0,
    })),
  },
}))

vi.mock('@/utils/auth', () => ({
  getCurrentUser: vi.fn(),
}))

vi.mock('@/components/maps/LeafletPreview.vue', () => ({
  default: {
    name: 'LeafletPreviewStub',
    template: '<div data-test="leaflet-preview" />',
  },
}))

import api from '@/services/api'
import { getCurrentUser } from '@/utils/auth'
import EventDetails from '@/views/EventDetails.vue'

const mountEventDetails = async ({ postulations = [] } = {}) => {
  getCurrentUser.mockReturnValue({ id: '42', role: 'volunteer' })
  api.get.mockImplementation((url) => {
    if (url === '/evenements/12') {
      return Promise.resolve({
        data: {
          id_evenement: 12,
          nom_evenement: 'Run for Test',
          description_evenement: 'Event description',
          lieu_evenement: 'Geneva',
          organisateur_evenement: 'BeneRun',
          date_debut_evenement: '2026-06-01',
          date_fin_evenement: '2026-06-01',
          heure_debut_evenement: '08:00:00',
          heure_fin_evenement: '18:00:00',
        },
      })
    }

    if (url === '/missions') {
      return Promise.resolve({ data: [] })
    }

    if (url === '/affectations') {
      return Promise.resolve({ data: [] })
    }

    if (url === '/postulations') {
      return Promise.resolve({ data: postulations })
    }

    return Promise.resolve({ data: null })
  })

  api.post.mockResolvedValue({
    data: {
      postulation: {
        id_postulation: 77,
        id_evenement: 12,
        id_utilisateur: 42,
        statut_postulation: 'en_attente',
      },
    },
  })

  const wrapper = mount(EventDetails, {
    global: {
      stubs: {
        RouterLink: true,
      },
    },
  })

  await flushPromises()
  await nextTick()
  return wrapper
}

describe('EventDetails.vue', () => {
  beforeEach(() => {
    pushMock.mockClear()
    api.get.mockReset()
    api.post.mockReset()
    getCurrentUser.mockReset()
    routeMock.params.id = '12'
  })

  it('désactive le bouton d inscription quand l utilisateur est déjà en liste d attente', async () => {
    const wrapper = await mountEventDetails({
      postulations: [
        {
          id_postulation: 1,
          id_evenement: 12,
          id_utilisateur: 42,
          statut_postulation: 'en_attente',
        },
      ],
    })

    expect(wrapper.text()).toContain('Vous êtes déjà inscrit à cet événement')

    const signupButton = wrapper
      .findAll('button')
      .find((button) => button.text().includes('Déjà inscrit à cet événement'))

    expect(signupButton?.attributes('disabled')).toBeDefined()
    expect(api.post).not.toHaveBeenCalled()
  })

  it('inscrit le bénévole quand aucune postulation active n existe', async () => {
    const wrapper = await mountEventDetails()

    const signupButton = wrapper
      .findAll('button')
      .find((button) => button.text().includes("S'inscrire à l'événement"))

    expect(signupButton).toBeDefined()
    expect(signupButton?.attributes('disabled')).toBeUndefined()

    await signupButton.trigger('click')
    await flushPromises()
    await nextTick()

    expect(api.post).toHaveBeenCalledWith('/evenements/12/inscriptions', {
      id_utilisateur: 42,
      remarque: "Inscription en liste d'attente événement depuis la vue détails.",
    })
    expect(wrapper.text()).toContain("Vous êtes inscrit en liste d'attente pour cet événement")
  })
})