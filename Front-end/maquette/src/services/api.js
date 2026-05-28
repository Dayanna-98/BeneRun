import axios from 'axios'

const isLocalHost = (hostname) => ['localhost', '127.0.0.1', '[::1]'].includes(hostname)

const resolveDefaultApiBaseUrl = () => {
  if (typeof window === 'undefined') return 'http://localhost:8000/api'

  const { protocol, hostname, origin } = window.location
  if (isLocalHost(hostname)) {
    return `${protocol}//${hostname}:8000/api`
  }

  return `${origin}/api`
}

const defaultApiBaseUrl = resolveDefaultApiBaseUrl()
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || defaultApiBaseUrl
const loginUrl = `${import.meta.env.BASE_URL}login`

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
    const requestUrl = String(error?.config?.url || '')
    const skipAutoRedirect = [
      '/login',
      '/password-reset/request',
      '/password-reset/verify',
      '/password-reset/reset',
    ].some((path) => requestUrl.includes(path))

    if (error.response?.status === 401 && !skipAutoRedirect) {
      localStorage.removeItem('isLoggedIn')
      localStorage.removeItem('userEmail')
      localStorage.removeItem('token')
      localStorage.removeItem('currentUser')
      window.location.href = loginUrl
    }
    return Promise.reject(error)
  }
)

export default api