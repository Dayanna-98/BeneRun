import axios from 'axios'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

const api = axios.create({
  baseURL: apiBaseUrl,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})

// Intercepteur : ajoute le token auth à chaque requête
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  const currentUserRaw = localStorage.getItem('currentUser')
  if (currentUserRaw) {
    try {
      const currentUser = JSON.parse(currentUserRaw)
      if (currentUser?.role) {
        config.headers['X-User-Role'] = currentUser.role
      }
    } catch {
      // Ignore malformed local user cache and continue without role header.
    }
  }

  return config
})

// Intercepteur : redirige vers /login si token expiré
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api