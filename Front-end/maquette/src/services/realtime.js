import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

let echoInstance = null
const defaultApiBaseUrl = `${window.location.protocol}//${window.location.hostname}:8000/api`

const isLocalHost = (value) => {
  const host = String(value || '').trim().toLowerCase()
  return host === 'localhost' || host === '127.0.0.1' || host === '::1'
}

const resolveReverbScheme = () => {
  const envScheme = String(import.meta.env.VITE_REVERB_SCHEME || '').trim().toLowerCase()
  if (envScheme === 'http' || envScheme === 'https') {
    return envScheme
  }

  return window.location.protocol === 'https:' ? 'https' : 'http'
}

const resolveReverbHost = () => {
  const envHost = String(import.meta.env.VITE_REVERB_HOST || '').trim()
  if (!envHost) {
    return window.location.hostname
  }

  if (import.meta.env.PROD && isLocalHost(envHost)) {
    return window.location.hostname
  }

  return envHost
}

const resolveReverbPort = (scheme) => {
  const envPort = Number(import.meta.env.VITE_REVERB_PORT)
  if (Number.isFinite(envPort) && envPort > 0) {
    return envPort
  }

  const locationPort = Number(window.location.port)
  if (Number.isFinite(locationPort) && locationPort > 0) {
    return locationPort
  }

  return scheme === 'https' ? 443 : 80
}

const getApiRootUrl = () => {
  const apiUrl = import.meta.env.VITE_API_BASE_URL || defaultApiBaseUrl
  return apiUrl.replace(/\/api\/?$/, '')
}

const getAuthHeaders = () => {
  const token = localStorage.getItem('token')
  return {
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  }
}

export const getEcho = () => {
  const key = String(import.meta.env.VITE_REVERB_APP_KEY || '').trim()
  if (!key) return null

  const scheme = resolveReverbScheme()
  const wsHost = resolveReverbHost()
  const wsPort = resolveReverbPort(scheme)

  if (echoInstance) {
    return echoInstance
  }

  echoInstance = new Echo({
    broadcaster: 'reverb',
    key,
    wsHost,
    wsPort,
    wssPort: wsPort,
    forceTLS: scheme === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${getApiRootUrl()}/broadcasting/auth`,
    auth: {
      headers: getAuthHeaders(),
    },
  })

  return echoInstance
}

export const leaveEchoChannel = (channelName) => {
  if (!echoInstance || !channelName) return

  try {
    const state = echoInstance.connector?.pusher?.connection?.state
    if (state === 'closing' || state === 'closed' || state === 'failed' || !state) {
      return
    }

    echoInstance.leave(channelName)
  } catch {
    // Ignore leave errors during disconnect races on navigation/unmount.
  }
}

export const destroyEcho = () => {
  if (!echoInstance) return

  try {
    echoInstance.disconnect()
  } catch {
    // Ignore disconnect errors when socket already closed.
  }

  echoInstance = null
}

export default getEcho
