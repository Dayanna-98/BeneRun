import api from './api'

export const notificationService = {
  async getFeed() {
    const response = await api.get('/notifications')
    const payload = response.data || {}
    const items = Array.isArray(payload.data) ? payload.data : []

    return {
      items,
      count: Number(payload.count || items.length || 0),
      unreadCount: Number(payload.unread_count || 0),
    }
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