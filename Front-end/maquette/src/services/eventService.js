import api from './api'
import { getCurrentUser } from '@/utils/auth'

export const eventService = {
  normalizeDateForInput: (value) => {
    if (!value) return ''
    return String(value).slice(0, 10)
  },

  mapApiEvent: (apiEvent) => {
    if (!apiEvent) return null

    return {
      id: String(apiEvent.id_evenement ?? apiEvent.id ?? ''),
      name: apiEvent.nom_evenement || '',
      description: apiEvent.description_evenement || '',
      startDate: eventService.normalizeDateForInput(apiEvent.date_debut_evenement),
      endDate: eventService.normalizeDateForInput(apiEvent.date_fin_evenement || apiEvent.date_debut_evenement),
      startTime: apiEvent.heure_debut_evenement ? String(apiEvent.heure_debut_evenement).slice(0, 5) : '',
      endTime: apiEvent.heure_fin_evenement ? String(apiEvent.heure_fin_evenement).slice(0, 5) : '',
      date: eventService.normalizeDateForInput(apiEvent.date_debut_evenement),
      location: apiEvent.lieu_evenement || '',
      organizer: apiEvent.organisateur_evenement || '',
      imageUrl: apiEvent.image_evenement || 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=800',
      totalVolunteersNeeded: Number(apiEvent.nombre_benevoles_requis || 0),
      currentVolunteers: 0,
      missionsCount: Number(apiEvent.missions_count || 0),
      category: 'Non défini',
      latitude: apiEvent.latitude_evenement == null ? '' : String(apiEvent.latitude_evenement),
      longitude: apiEvent.longitude_evenement == null ? '' : String(apiEvent.longitude_evenement),
      isPublished: !!apiEvent.est_publie_evenement,
      isCancelled: !!apiEvent.est_annule_evenement,
      cancellationDate: apiEvent.date_annulation_evenement || '',
      cancellationReason: apiEvent.raison_annulation_evenement || '',
      creatorId: apiEvent.cree_par_utilisateur_id ? String(apiEvent.cree_par_utilisateur_id) : '',
    }
  },

  toPayload: (formData, existingEvent = null) => {
    const currentUser = getCurrentUser()
    const creatorId = Number(currentUser?.id || existingEvent?.cree_par_utilisateur_id || 0)

    return {
      nom_evenement: formData.name,
      description_evenement: formData.description,
      date_debut_evenement: formData.startDate || formData.date,
      date_fin_evenement: formData.endDate || formData.startDate || formData.date,
      heure_debut_evenement: formData.startTime || null,
      heure_fin_evenement: formData.endTime || null,
      lieu_evenement: formData.location,
      organisateur_evenement: formData.organizer,
      latitude_evenement: formData.latitude ? Number(formData.latitude) : null,
      longitude_evenement: formData.longitude ? Number(formData.longitude) : null,
      image_evenement: formData.imageUrl || null,
      nombre_benevoles_requis: Number(formData.totalVolunteersNeeded || 0),
      est_annule_evenement: !!formData.isCancelled,
      date_annulation_evenement: formData.cancellationDate || null,
      raison_annulation_evenement: formData.cancellationReason || null,
      est_publie_evenement: formData.isPublished !== false,
      cree_par_utilisateur_id: creatorId,
    }
  },

  validatePayload: (payload) => {
    if (!payload.cree_par_utilisateur_id || Number.isNaN(payload.cree_par_utilisateur_id)) {
      throw { message: 'Utilisateur non identifié. Reconnectez-vous pour créer ou modifier un événement.' }
    }
  },

  formatApiError: (error, fallbackMessage) => {
    if (error?.message && !error?.response) {
      return { message: error.message }
    }

    if (!error.response) {
      return {
        message: 'Serveur API inaccessible. Vérifiez que Laravel tourne sur http://localhost:8000.',
      }
    }
    return error.response.data || { message: fallbackMessage }
  },

  getAll: async () => {
    try {
      const response = await api.get('/evenements')
      const payload = response.data
      const rows = Array.isArray(payload)
        ? payload
        : Array.isArray(payload?.data)
          ? payload.data
          : Array.isArray(payload?.events)
            ? payload.events
            : []

      return rows.map(eventService.mapApiEvent).filter(Boolean)
    } catch (error) {
      throw eventService.formatApiError(error, 'Erreur lors du chargement des événements')
    }
  },

  getById: async (id) => {
    try {
      const response = await api.get(`/evenements/${id}`)
      return eventService.mapApiEvent(response.data)
    } catch (error) {
      throw eventService.formatApiError(error, 'Événement non trouvé')
    }
  },

  create: async (formData) => {
    try {
      const payload = eventService.toPayload(formData)
      eventService.validatePayload(payload)
      const response = await api.post('/evenements', payload)
      return response.data
    } catch (error) {
      throw eventService.formatApiError(error, 'Erreur lors de la création de l\'événement')
    }
  },

  update: async (id, formData) => {
    try {
      const payload = eventService.toPayload(formData)
      eventService.validatePayload(payload)
      const response = await api.put(`/evenements/${id}`, payload)
      return response.data
    } catch (error) {
      throw eventService.formatApiError(error, 'Erreur lors de la mise à jour de l\'événement')
    }
  },

  delete: async (id) => {
    try {
      const response = await api.delete(`/evenements/${id}`)
      return response.data
    } catch (error) {
      throw eventService.formatApiError(error, 'Erreur lors de la suppression de l\'événement')
    }
  },
}

export default eventService
