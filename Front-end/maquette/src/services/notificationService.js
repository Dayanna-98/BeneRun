import api from './api'
import { getCurrentUser } from '@/utils/auth'

export const notificationService = {
  async getFeed() {
    const user = getCurrentUser()
    const response = await api.get('/notifications', {
      params: {
        user_id: user?.id || '',
        role: user?.role || '',
      },
    })

    return Array.isArray(response.data?.data) ? response.data.data : []
  },
}

export default notificationService