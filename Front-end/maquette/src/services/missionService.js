import api from './api'
import { getCurrentUser } from '@/utils/auth'

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
      latitude: apiMission.latitude_mission == null ? '' : String(apiMission.latitude_mission),
      longitude: apiMission.longitude_mission == null ? '' : String(apiMission.longitude_mission),
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
      latitude_mission: formData.latitude ? Number(formData.latitude) : null,
      longitude_mission: formData.longitude ? Number(formData.longitude) : null,
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

  validatePayload: (payload) => {
    if (!payload.id_evenement || Number.isNaN(payload.id_evenement)) {
      throw { message: 'Veuillez sélectionner un événement.' }
    }

    if (!payload.responsable_utilisateur_id || Number.isNaN(payload.responsable_utilisateur_id)) {
      throw { message: 'Utilisateur non identifié. Reconnectez-vous pour gérer les missions.' }
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
      const payload = response.data
      const rows = Array.isArray(payload)
        ? payload
        : Array.isArray(payload?.data)
          ? payload.data
          : []

      return rows.map(missionService.mapApiMission).filter(Boolean)
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
      const payload = missionService.toPayload(formData)
      missionService.validatePayload(payload)
      const response = await api.post('/missions', payload)
      return response.data
    } catch (error) {
      throw missionService.formatApiError(error, 'Erreur lors de la création de la mission')
    }
  },

  update: async (id, formData) => {
    try {
      const payload = missionService.toPayload(formData)
      missionService.validatePayload(payload)
      const response = await api.put(`/missions/${id}`, payload)
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
