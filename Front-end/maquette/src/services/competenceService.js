import api from './api'

const competenceService = {
  getAll: () => api.get('/competences').then(r => r.data),

  getById: (id) => api.get(`/competences/${id}`).then(r => r.data),

  getUserCompetences: (userId) => api.get(`/users/${userId}/competences`).then(r => r.data),

  addToUser: (userId, competenceId) => api.post(`/users/${userId}/competences`, {
    id_competence: Number(competenceId),
  }).then(r => r.data),

  removeFromUser: (userId, competenceId) => api.delete(`/users/${userId}/competences/${competenceId}`).then(r => r.data),

  create: (payload) => {
    const body = typeof payload === 'string'
      ? { nom_competence: payload, types_mission_suggeres: [] }
      : {
        nom_competence: payload?.nom_competence,
        types_mission_suggeres: Array.isArray(payload?.types_mission_suggeres)
          ? payload.types_mission_suggeres
          : [],
      }

    return api.post('/competences', body).then(r => r.data)
  },

  update: (id, payload) => {
    const body = typeof payload === 'string'
      ? { nom_competence: payload, types_mission_suggeres: [] }
      : {
        nom_competence: payload?.nom_competence,
        types_mission_suggeres: Array.isArray(payload?.types_mission_suggeres)
          ? payload.types_mission_suggeres
          : [],
      }

    return api.put(`/competences/${id}`, body).then(r => r.data)
  },

  delete: (id) => api.delete(`/competences/${id}`).then(r => r.data),
}

export default competenceService
