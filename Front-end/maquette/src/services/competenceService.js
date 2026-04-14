import api from './api'

const competenceService = {
  getAll: () => api.get('/competences').then(r => r.data),

  getById: (id) => api.get(`/competences/${id}`).then(r => r.data),

  getUserCompetences: (userId) => api.get(`/users/${userId}/competences`).then(r => r.data),

  addToUser: (userId, competenceId) => api.post(`/users/${userId}/competences`, {
    id_competence: Number(competenceId),
  }).then(r => r.data),

  removeFromUser: (userId, competenceId) => api.delete(`/users/${userId}/competences/${competenceId}`).then(r => r.data),

  create: (nom) => api.post('/competences', { nom_competence: nom }).then(r => r.data),

  update: (id, nom) => api.put(`/competences/${id}`, { nom_competence: nom }).then(r => r.data),

  delete: (id) => api.delete(`/competences/${id}`).then(r => r.data),
}

export default competenceService
