import api from './api'

export const notificationService = {
  async getFeed() {
    const response = await api.get('/notifications')

    return Array.isArray(response.data?.data) ? response.data.data : []
  },

  async markAsRead(ids = []) {
    const safeIds = Array.isArray(ids)
      ? ids.map((id) => String(id || '').trim()).filter(Boolean)
      : []

    if (!safeIds.length) {
      return { count: 0 }
    }

    const response = await api.post('/notifications/read', { ids: safeIds })
    return response.data || { count: safeIds.length }
  },
}

export default notificationService