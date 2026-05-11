import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

let echoInstance = null

const getApiRootUrl = () => {
  const apiUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
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
  const key = import.meta.env.VITE_REVERB_APP_KEY || 'benerun-local-key'
  if (!key) return null

  if (echoInstance) {
    return echoInstance
  }

  echoInstance = new Echo({
    broadcaster: 'reverb',
    key,
    wsHost: import.meta.env.VITE_REVERB_HOST || '127.0.0.1',
    wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
    forceTLS: String(import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
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
  echoInstance.leave(channelName)
}

export const destroyEcho = () => {
  if (!echoInstance) return
  echoInstance.disconnect()
  echoInstance = null
}

export default getEcho
