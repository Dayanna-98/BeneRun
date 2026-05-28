import api from './api'

const mapUser = (user) => ({
  id: String(user?.id_utilisateur ?? user?.id ?? ''),
  firstName: user?.prenom_utilisateur || '',
  lastName: user?.nom_utilisateur || '',
  email: user?.email || '',
})

const formatUserName = (user) => {
  const mapped = mapUser(user)
  const fullName = `${mapped.firstName} ${mapped.lastName}`.trim()
  return fullName || mapped.email || 'Utilisateur'
}

const normalizeCategory = (value) =>
  String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z]/g, '')

const toFrenchCategoryLabel = (value) => {
  const normalized = normalizeCategory(value)

  const labels = {
    medical: 'Médicale',
    medicale: 'Médicale',
    security: 'Sécurité',
    securite: 'Sécurité',
    logistics: 'Logistique',
    logistique: 'Logistique',
    other: 'Autre',
    autre: 'Autre',
    general: 'Générale',
    generale: 'Générale',
  }

  return labels[normalized] || 'Générale'
}

const mapEmergency = (raw) => {
  if (!raw) return null

  const category = raw.categorie_urgence || 'general'

  return {
    id: String(raw.id_mission_emergency_message),
    missionId: String(raw.id_mission || raw.mission?.id_mission || ''),
    eventId: String(raw.id_evenement || raw.evenement?.id_evenement || ''),
    category,
    categoryLabel: toFrenchCategoryLabel(category),
    missionStatus: raw.mission?.statut_mission || '',
    missionDate: raw.mission?.date_mission || null,
    missionEndTime: raw.mission?.heure_fin_mission || null,
    message: raw.message_urgence || '',
    sentAt: raw.created_at || null,
    sender: {
      ...mapUser(raw.emetteur),
      fullName: formatUserName(raw.emetteur),
    },
    missionName: raw.mission?.titre_mission || `Mission #${raw.id_mission}`,
    eventName: raw.evenement?.nom_evenement || `Événement #${raw.id_evenement}`,
    owner: raw.pris_en_charge_par
      ? {
        ...mapUser(raw.pris_en_charge_par),
        fullName: formatUserName(raw.pris_en_charge_par),
      }
      : null,
    ownershipAt: raw.pris_en_charge_le || null,
    views: Array.isArray(raw.consultations)
      ? raw.consultations.map((view) => ({
        id: String(view.id_mission_emergency_message_view || ''),
        viewedAt: view.consulte_le || null,
        viewer: {
          ...mapUser(view.utilisateur),
          fullName: formatUserName(view.utilisateur),
        },
      }))
      : [],
  }
}

const formatApiError = (error, fallbackMessage) => {
  if (!error?.response) {
    return { message: 'Serveur API inaccessible. Vérifiez que le service backend est bien démarré.' }
  }

  return error.response.data || { message: fallbackMessage }
}

export const emergencyService = {
  mapEmergencyPayload: (raw) => mapEmergency(raw),

  sendMissionEmergency: async ({ missionId, senderUserId, category, message }) => {
    try {
      const response = await api.post(`/missions/${missionId}/urgences`, {
        id_utilisateur: Number(senderUserId),
        categorie_urgence: category || null,
        message_urgence: message,
      })

      return {
        ...response.data,
        urgence: mapEmergency(response.data?.urgence),
      }
    } catch (error) {
      throw formatApiError(error, 'Impossible d\'envoyer le message d\'urgence.')
    }
  },

  listForSuperadmin: async () => {
    try {
      const response = await api.get('/urgences')
      const rows = Array.isArray(response.data)
        ? response.data
        : Array.isArray(response.data?.data)
          ? response.data.data
          : []

      return rows.map(mapEmergency).filter(Boolean)
    } catch (error) {
      throw formatApiError(error, 'Impossible de charger les messages d\'urgence.')
    }
  },

  markViewed: async ({ emergencyId, userId }) => {
    try {
      const response = await api.post(`/urgences/${emergencyId}/consultation`, {
        id_utilisateur: Number(userId),
      })

      return {
        ...response.data,
        urgence: mapEmergency(response.data?.urgence),
      }
    } catch (error) {
      throw formatApiError(error, 'Impossible d\'enregistrer la consultation.')
    }
  },

  takeOwnership: async ({ emergencyId, userId }) => {
    try {
      const response = await api.post(`/urgences/${emergencyId}/prise-en-charge`, {
        id_utilisateur: Number(userId),
      })

      return {
        ...response.data,
        urgence: mapEmergency(response.data?.urgence),
      }
    } catch (error) {
      throw formatApiError(error, 'Impossible de prendre en charge ce message.')
    }
  },
}

export default emergencyService
