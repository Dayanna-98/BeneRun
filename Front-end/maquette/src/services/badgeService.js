import api from './api'

const badgeService = {
	getAll: () => api.get('/badges').then(r => r.data),
	getById: (id) => api.get(`/badges/${id}`).then(r => r.data),
	create: (payload) => api.post('/badges', payload).then(r => r.data),
	update: (id, payload) => api.put(`/badges/${id}`, payload).then(r => r.data),
	delete: (id) => api.delete(`/badges/${id}`).then(r => r.data),
	getUserBadges: (userId) => api.get(`/users/${userId}/badges`).then(r => r.data),
	addToUser: (userId, badgeId) => api.post(`/users/${userId}/badges`, { id_badge: Number(badgeId) }).then(r => r.data),
	removeFromUser: (userId, badgeId) => api.delete(`/users/${userId}/badges/${badgeId}`).then(r => r.data),
}

export default badgeService
