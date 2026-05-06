import api from './api'
import { getCurrentUser } from '@/utils/auth'

const extractRows = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
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

const toLabelType = (value) => {
  const map = {
    secours: 'Secours',
    logistique: 'Logistique',
    accueil: 'Accueil',
    technique: 'Technique',
    animation: 'Animation',
    autre: 'Autre',
  }
  return map[value] || 'Autre'
}

const toApiType = (value) => {
  const normalized = String(value || '').trim().toLowerCase()
  const map = {
    secours: 'secours',
    logistique: 'logistique',
    accueil: 'accueil',
    technique: 'technique',
    animation: 'animation',
    autre: 'autre',
  }
  return map[normalized] || 'autre'
}

export const missionService = {
  mapApiMission: (apiMission) => {
    if (!apiMission) return null

    return {
      id: String(apiMission.id_mission ?? apiMission.id ?? ''),
      eventId: String(apiMission.id_evenement ?? ''),
      eventName: apiMission.evenement?.nom_evenement || 'Événement inconnu',
      name: apiMission.titre_mission || '',
      type: toLabelType(apiMission.type_mission),
      description: apiMission.description_mission || '',
      date: apiMission.date_mission ? String(apiMission.date_mission).slice(0, 10) : '',
      startTime: apiMission.heure_debut_mission ? String(apiMission.heure_debut_mission).slice(0, 5) : '',
      endTime: apiMission.heure_fin_mission ? String(apiMission.heure_fin_mission).slice(0, 5) : '',
      location: apiMission.lieu_mission || '',
      googleMapsUrl: apiMission.google_maps_url_mission || '',
      maxVolunteers: Number(apiMission.nombre_benevoles_max || 0),
      backupVolunteers: Number(apiMission.nombre_benevoles_backup || 0),
      currentVolunteers: Number(apiMission.current_volunteers_count || 0),
      inscription: !!apiMission.inscription_requise,
      public: apiMission.visibilite_mission === 'publique',
      visibility: apiMission.visibilite_mission || 'publique',
      status: apiMission.statut_mission || 'À venir',
      imageUrl: apiMission.image_mission || '',
      responsibleUserId: String(apiMission.responsable_utilisateur_id || ''),
      responsibleName: apiMission.responsable
        ? `${apiMission.responsable.prenom_utilisateur || ''} ${apiMission.responsable.nom_utilisateur || ''}`.trim()
        : '',
      responsiblePhone: apiMission.responsable?.telephone_utilisateur || '',
      responsibleEmail: apiMission.responsable?.email || '',
      safetyInstructions: apiMission.consignes_securite || '',
      requiredSkills: Array.isArray(apiMission.competences)
        ? apiMission.competences.map((c) => c.nom_competence).filter(Boolean)
        : [],
      competenceIds: Array.isArray(apiMission.competences)
        ? apiMission.competences.map((c) => Number(c.id_competence)).filter((id) => !Number.isNaN(id))
        : [],
      eventGoogleMapsUrl: apiMission.evenement?.google_maps_url_evenement || '',
      eventRadiusMeters: Number(apiMission.evenement?.rayon_localisation_evenement || 0),
    }
  },

  toPayload: (formData, existingMission = null) => {
    const currentUser = getCurrentUser()
    const responsableId = Number(formData.responsibleUserId || currentUser?.id || existingMission?.responsable_utilisateur_id || 0)

    return {
      id_evenement: Number(formData.eventId),
      responsable_utilisateur_id: responsableId,
      titre_mission: formData.name,
      type_mission: toApiType(formData.type),
      description_mission: formData.description,
      date_mission: formData.date,
      heure_debut_mission: formData.startTime,
      heure_fin_mission: formData.endTime,
      lieu_mission: formData.location,
      google_maps_url_mission: formData.googleMapsUrl || null,
      nombre_benevoles_max: Number(formData.maxVolunteers || 0),
      nombre_benevoles_backup: Number(formData.backupVolunteers || 0),
      statut_mission: formData.status || 'À venir',
      inscription_requise: formData.inscription !== false,
      visibilite_mission: formData.public === false ? 'privée' : 'publique',
      consignes_securite: formData.safetyInstructions || null,
      image_mission: formData.imageUrl || null,
      competence_ids: Array.isArray(formData.competenceIds)
        ? formData.competenceIds.map((id) => Number(id)).filter((id) => !Number.isNaN(id))
        : [],
    }
  },

  toMultipartPayload: (formData, existingMission = null) => {
    const payload = missionService.toPayload(formData, existingMission)
    const multipart = new FormData()

    appendNullable(multipart, 'id_evenement', payload.id_evenement)
    appendNullable(multipart, 'responsable_utilisateur_id', payload.responsable_utilisateur_id)
    appendNullable(multipart, 'titre_mission', payload.titre_mission)
    appendNullable(multipart, 'type_mission', payload.type_mission)
    appendNullable(multipart, 'description_mission', payload.description_mission)
    appendNullable(multipart, 'date_mission', payload.date_mission)
    appendNullable(multipart, 'heure_debut_mission', payload.heure_debut_mission)
    appendNullable(multipart, 'heure_fin_mission', payload.heure_fin_mission)
    appendNullable(multipart, 'lieu_mission', payload.lieu_mission)
    appendNullable(multipart, 'google_maps_url_mission', payload.google_maps_url_mission)
    appendNullable(multipart, 'nombre_benevoles_max', payload.nombre_benevoles_max)
    appendNullable(multipart, 'nombre_benevoles_backup', payload.nombre_benevoles_backup)
    appendNullable(multipart, 'statut_mission', payload.statut_mission)
    appendNullable(multipart, 'inscription_requise', payload.inscription_requise ? '1' : '0')
    appendNullable(multipart, 'visibilite_mission', payload.visibilite_mission)
    appendNullable(multipart, 'consignes_securite', payload.consignes_securite)
    appendNullable(multipart, 'image_mission', payload.image_mission)

    for (const competenceId of payload.competence_ids || []) {
      multipart.append('competence_ids[]', competenceId)
    }

    if (formData.imageFile instanceof File) {
      multipart.append('image_file', formData.imageFile)
    }

    for (const file of Array.isArray(formData.photoFiles) ? formData.photoFiles : []) {
      if (file instanceof File) {
        multipart.append('media_files[]', file)
      }
    }

    return multipart
  },

  validateFormData: (formData) => {
    const errors = {}

    if (!String(formData.name || '').trim()) errors.name = 'Le titre de la mission est obligatoire.'
    if (!String(formData.eventId || '').trim()) errors.eventId = 'Sélectionnez un événement.'
    if (!String(formData.type || '').trim()) errors.type = 'Le type de mission est obligatoire.'
    if (!String(formData.date || '').trim()) errors.date = 'La date est obligatoire.'
    if (!String(formData.startTime || '').trim()) errors.startTime = 'L\'heure de début est obligatoire.'
    if (!String(formData.endTime || '').trim()) errors.endTime = 'L\'heure de fin est obligatoire.'
    if (formData.startTime && formData.endTime && formData.endTime <= formData.startTime) {
      errors.endTime = 'L\'heure de fin doit être après l\'heure de début.'
    }
    if (!String(formData.location || '').trim()) errors.location = 'La localisation est obligatoire.'
    if (!String(formData.description || '').trim()) errors.description = 'La description est obligatoire.'
    if (!formData.maxVolunteers || Number(formData.maxVolunteers) < 1) errors.maxVolunteers = 'Le nombre de places doit être supérieur à 0.'
    if (formData.backupVolunteers && Number(formData.backupVolunteers) < 0) errors.backupVolunteers = 'Le quota de backup doit être positif.'
    if (!String(formData.responsibleUserId || '').trim()) errors.responsibleUserId = 'Sélectionnez un responsable.'
    if (!String(formData.googleMapsUrl || '').trim()) errors.googleMapsUrl = 'Le lien Google Maps est obligatoire.'

    if (formData.imageFile instanceof File) {
      if (!String(formData.imageFile.type || '').startsWith('image/')) {
        errors.imageFile = 'Le visuel principal doit être une image.'
      } else if (formData.imageFile.size > 5 * 1024 * 1024) {
        errors.imageFile = 'Le visuel principal ne doit pas dépasser 5 Mo.'
      }
    }

    const invalidPhoto = (Array.isArray(formData.photoFiles) ? formData.photoFiles : []).find((file) => {
      if (!(file instanceof File)) return false
      return !String(file.type || '').startsWith('image/') || file.size > 5 * 1024 * 1024
    })

    if (invalidPhoto) {
      errors.photoFiles = 'Chaque photo doit être une image de moins de 5 Mo.'
    }

    return errors
  },

  validatePayload: (payload) => {
    if (!payload.id_evenement || Number.isNaN(payload.id_evenement)) {
      throw { message: 'Veuillez sélectionner un événement.' }
    }

    if (!payload.responsable_utilisateur_id || Number.isNaN(payload.responsable_utilisateur_id)) {
      throw { message: 'Utilisateur non identifié. Reconnectez-vous pour gérer les missions.' }
    }

    if (!payload.google_maps_url_mission) {
      throw { message: 'Veuillez renseigner un lien Google Maps pour la mission.' }
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

  getAll: async (filters = {}) => {
    try {
      const response = await api.get('/missions', { params: filters })
      const rows = extractRows(response.data)
      return rows.map(missionService.mapApiMission).filter(Boolean)
    } catch (error) {
      throw missionService.formatApiError(error, 'Erreur lors du chargement des missions')
    }
  },

  getPage: async (filters = {}) => {
    try {
      const response = await api.get('/missions', { params: filters })
      return {
        data: extractRows(response.data).map(missionService.mapApiMission).filter(Boolean),
        meta: extractMeta(response.data),
      }
    } catch (error) {
      throw missionService.formatApiError(error, 'Erreur lors du chargement des missions')
    }
  },

  getById: async (id) => {
    try {
      const response = await api.get(`/missions/${id}`)
      return missionService.mapApiMission(response.data)
    } catch (error) {
      throw missionService.formatApiError(error, 'Mission non trouvée')
    }
  },

  create: async (formData) => {
    try {
      const rawPayload = missionService.toPayload(formData)
      missionService.validatePayload(rawPayload)
      const payload = missionService.toMultipartPayload(formData)
      const response = await api.post('/missions', payload, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data
    } catch (error) {
      throw missionService.formatApiError(error, 'Erreur lors de la création de la mission')
    }
  },

  update: async (id, formData) => {
    try {
      const rawPayload = missionService.toPayload(formData)
      missionService.validatePayload(rawPayload)
      const payload = missionService.toMultipartPayload(formData)
      payload.append('_method', 'PUT')
      const response = await api.post(`/missions/${id}`, payload, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data
    } catch (error) {
      throw missionService.formatApiError(error, 'Erreur lors de la mise à jour de la mission')
    }
  },

  delete: async (id) => {
    try {
      const response = await api.delete(`/missions/${id}`)
      return response.data
    } catch (error) {
      throw missionService.formatApiError(error, 'Erreur lors de la suppression de la mission')
    }
  },
}

export default missionService
