import api from './api'
import { getCurrentUser } from '@/utils/auth'

const extractRows = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.events)) return payload.events
  return []
}

const extractMeta = (payload) => {
  if (!payload || Array.isArray(payload)) return null

  return {
    currentPage: Number(payload.current_page || 1),
    lastPage: Number(payload.last_page || 1),
    perPage: Number(payload.per_page || extractRows(payload).length || 0),
    total: Number(payload.total || extractRows(payload).length || 0),
  }
}

const appendNullable = (formData, key, value) => {
  if (value === undefined) return
  formData.append(key, value ?? '')
}

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
      googleMapsUrl: apiEvent.google_maps_url_evenement || '',
      radiusMeters: Number(apiEvent.rayon_localisation_evenement || 0),
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
      google_maps_url_evenement: formData.googleMapsUrl || null,
      rayon_localisation_evenement: Number(formData.radiusMeters || 0),
      image_evenement: formData.imageUrl || null,
      nombre_benevoles_requis: Number(formData.totalVolunteersNeeded || 0),
      est_annule_evenement: !!formData.isCancelled,
      date_annulation_evenement: formData.cancellationDate || null,
      raison_annulation_evenement: formData.cancellationReason || null,
      est_publie_evenement: formData.isPublished !== false,
      cree_par_utilisateur_id: creatorId,
    }
  },

  toMultipartPayload: (formData, existingEvent = null) => {
    const payload = eventService.toPayload(formData, existingEvent)
    const multipart = new FormData()

    appendNullable(multipart, 'nom_evenement', payload.nom_evenement)
    appendNullable(multipart, 'description_evenement', payload.description_evenement)
    appendNullable(multipart, 'date_debut_evenement', payload.date_debut_evenement)
    appendNullable(multipart, 'date_fin_evenement', payload.date_fin_evenement)
    appendNullable(multipart, 'heure_debut_evenement', payload.heure_debut_evenement)
    appendNullable(multipart, 'heure_fin_evenement', payload.heure_fin_evenement)
    appendNullable(multipart, 'lieu_evenement', payload.lieu_evenement)
    appendNullable(multipart, 'organisateur_evenement', payload.organisateur_evenement)
    appendNullable(multipart, 'google_maps_url_evenement', payload.google_maps_url_evenement)
    appendNullable(multipart, 'rayon_localisation_evenement', payload.rayon_localisation_evenement)
    appendNullable(multipart, 'image_evenement', payload.image_evenement)
    appendNullable(multipart, 'nombre_benevoles_requis', payload.nombre_benevoles_requis)
    appendNullable(multipart, 'est_annule_evenement', payload.est_annule_evenement ? '1' : '0')
    appendNullable(multipart, 'date_annulation_evenement', payload.date_annulation_evenement)
    appendNullable(multipart, 'raison_annulation_evenement', payload.raison_annulation_evenement)
    appendNullable(multipart, 'est_publie_evenement', payload.est_publie_evenement ? '1' : '0')
    appendNullable(multipart, 'cree_par_utilisateur_id', payload.cree_par_utilisateur_id)

    if (formData.imageFile instanceof File) {
      multipart.append('image_file', formData.imageFile)
    }

    return multipart
  },

  validateFormData: (formData) => {
    const errors = {}
    const startDate = formData.startDate || formData.date || ''
    const endDate = formData.endDate || formData.startDate || formData.date || ''

    if (!String(formData.name || '').trim()) errors.name = 'Le nom de l\'événement est obligatoire.'
    if (!startDate) errors.startDate = 'La date de début est obligatoire.'
    if (!endDate) errors.endDate = 'La date de fin est obligatoire.'
    if (startDate && endDate && endDate < startDate) errors.endDate = 'La date de fin doit être après la date de début.'
    if (!String(formData.location || '').trim()) errors.location = 'Le lieu est obligatoire.'
    if (!String(formData.description || '').trim()) errors.description = 'La description est obligatoire.'
    if (!String(formData.organizer || '').trim()) errors.organizer = 'L\'organisateur est obligatoire.'
    if (!formData.totalVolunteersNeeded || Number(formData.totalVolunteersNeeded) < 1) {
      errors.totalVolunteersNeeded = 'Le nombre de bénévoles doit être supérieur à 0.'
    }
    if (!String(formData.googleMapsUrl || '').trim()) errors.googleMapsUrl = 'Le lien Google Maps est obligatoire.'
    if (!formData.radiusMeters || Number(formData.radiusMeters) < 1) errors.radiusMeters = 'Le périmètre doit être supérieur à 0.'

    if (formData.imageFile instanceof File) {
      if (!String(formData.imageFile.type || '').startsWith('image/')) {
        errors.imageFile = 'Le fichier sélectionné doit être une image.'
      } else if (formData.imageFile.size > 5 * 1024 * 1024) {
        errors.imageFile = 'L\'image ne doit pas dépasser 5 Mo.'
      }
    }

    return errors
  },

  validatePayload: (payload) => {
    if (!payload.cree_par_utilisateur_id || Number.isNaN(payload.cree_par_utilisateur_id)) {
      throw { message: 'Utilisateur non identifié. Reconnectez-vous pour créer ou modifier un événement.' }
    }

    if (!payload.google_maps_url_evenement) {
      throw { message: 'Veuillez renseigner un lien Google Maps pour l\'événement.' }
    }

    if (!payload.rayon_localisation_evenement || Number.isNaN(payload.rayon_localisation_evenement)) {
      throw { message: 'Veuillez renseigner un périmètre valide pour l\'événement.' }
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
      const rows = extractRows(response.data)
      return rows.map(eventService.mapApiEvent).filter(Boolean)
    } catch (error) {
      throw eventService.formatApiError(error, 'Erreur lors du chargement des événements')
    }
  },

  getPage: async (params = {}) => {
    try {
      const response = await api.get('/evenements', { params })
      const rows = extractRows(response.data).map(eventService.mapApiEvent).filter(Boolean)
      return {
        data: rows,
        meta: extractMeta(response.data),
      }
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
      const rawPayload = eventService.toPayload(formData)
      eventService.validatePayload(rawPayload)
      const payload = eventService.toMultipartPayload(formData)
      const response = await api.post('/evenements', payload, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data
    } catch (error) {
      throw eventService.formatApiError(error, 'Erreur lors de la création de l\'événement')
    }
  },

  update: async (id, formData) => {
    try {
      const rawPayload = eventService.toPayload(formData)
      eventService.validatePayload(rawPayload)
      const payload = eventService.toMultipartPayload(formData)
      payload.append('_method', 'PUT')
      const response = await api.post(`/evenements/${id}`, payload, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
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
