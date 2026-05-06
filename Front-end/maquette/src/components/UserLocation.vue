<template>
  <div class="location-tracker">
    <!-- Header -->
    <div class="location-header d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">
        <MapPin style="width:20px;height:20px" class="me-2" />
        Localisation en temps réel
      </h5>
      <div class="location-status">
        <span v-if="isTracking" class="badge bg-success">
          <Dot style="width:8px;height:8px" class="me-1" />
          En direct
        </span>
        <span v-else class="badge bg-secondary">Arrêtée</span>
      </div>
    </div>

    <!-- Controls -->
    <div class="location-controls d-flex gap-2 mb-3">
      <button
        @click="startTracking"
        :disabled="isTracking || isLoading"
        class="btn btn-sm btn-outline-primary"
      >
        <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"></span>
        {{ isTracking ? 'En suivi' : 'Partager ma position' }}
      </button>
      <button
        @click="stopTracking"
        :disabled="!isTracking"
        class="btn btn-sm btn-outline-danger"
      >
        <X style="width:16px;height:16px" class="me-1" />
        Arrêter
      </button>
    </div>

    <!-- Status Messages -->
    <div v-if="error" class="alert alert-danger alert-sm" role="alert">
      {{ error }}
    </div>
    <div v-if="successMessage" class="alert alert-success alert-sm" role="alert">
      {{ successMessage }}
    </div>

    <!-- Current Location -->
    <div v-if="currentLocation" class="card mb-3">
      <div class="card-body">
        <h6 class="card-title">Ma position actuelle</h6>
        <div class="location-info">
          <div class="info-row">
            <span class="label">Latitude:</span>
            <span class="value">{{ currentLocation.latitude.toFixed(5) }}</span>
          </div>
          <div class="info-row">
            <span class="label">Longitude:</span>
            <span class="value">{{ currentLocation.longitude.toFixed(5) }}</span>
          </div>
          <div v-if="currentLocation.accuracy" class="info-row">
            <span class="label">Précision:</span>
            <span class="value">{{ Math.round(currentLocation.accuracy) }} m</span>
          </div>
          <div class="info-row">
            <span class="label">Mise à jour:</span>
            <span class="value">{{ formatTime(currentLocation.updated_at) }}</span>
          </div>
        </div>
        <small class="text-muted d-block mt-2">
          Expire dans {{ expirationTime }}
        </small>
      </div>
    </div>

    <!-- Map Container -->
    <div v-if="nearbyVolunteers.length > 0" class="card">
      <div class="card-body">
        <h6 class="card-title mb-3">Volontaires proches ({{ nearbyVolunteers.length }})</h6>
        <div class="volunteers-list">
          <div
            v-for="volunteer in nearbyVolunteers"
            :key="volunteer.id_utilisateur"
            class="volunteer-item"
          >
            <div class="volunteer-info">
              <strong>{{ volunteer.prenom_utilisateur }} {{ volunteer.nom_utilisateur }}</strong>
              <br />
              <small class="text-muted">
                {{ calculateDistance(volunteer) }} km · Mis à jour {{ formatTime(volunteer.updated_at) }}
              </small>
            </div>
            <div class="volunteer-badge">
              <span class="badge bg-info">À proximité</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="!currentLocation" class="alert alert-info">
      Activez le partage de position pour voir les volontaires proches.
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { MapPin, X, Dot } from 'lucide-vue-next'
import axios from 'axios'

const isTracking = ref(false)
const isLoading = ref(false)
const error = ref(null)
const successMessage = ref(null)
const currentLocation = ref(null)
const nearbyVolunteers = ref([])
const watchId = ref(null)
const updateInterval = ref(null)
const expirationTime = ref('5:00')

// Calculer le temps restant avant expiration
const updateExpirationTime = () => {
  if (!currentLocation.value) return

  const updated = new Date(currentLocation.value.updated_at)
  const now = new Date()
  const diffMs = 5 * 60 * 1000 - (now - updated)

  if (diffMs <= 0) {
    currentLocation.value = null
    isTracking.value = false
    return
  }

  const minutes = Math.floor(diffMs / 60000)
  const seconds = Math.floor((diffMs % 60000) / 1000)
  expirationTime.value = `${minutes}:${seconds.toString().padStart(2, '0')}`
}

// Obtenir la position actuelle
const getPosition = () => {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Géolocalisation non disponible'))
      return
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        resolve({
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          accuracy: position.coords.accuracy,
        })
      },
      (error) => {
        reject(error)
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
      }
    )
  })
}

// Envoyer la localisation au serveur
const sendLocationToServer = async (location) => {
  try {
    const response = await axios.post('/api/location', location)
    currentLocation.value = response.data.location
    successMessage.value = 'Position mise à jour'
    setTimeout(() => {
      successMessage.value = null
    }, 3000)
  } catch (err) {
    error.value = 'Impossible de mettre à jour la position'
    console.error(err)
  }
}

// Récupérer les volontaires proches
const fetchNearbyVolunteers = async () => {
  try {
    const response = await axios.get('/api/locations')
    nearbyVolunteers.value = response.data.locations || []
  } catch (err) {
    console.error('Erreur lors de la récupération des volontaires', err)
  }
}

// Démarrer le suivi
const startTracking = async () => {
  error.value = null
  isLoading.value = true

  try {
    const location = await getPosition()
    await sendLocationToServer(location)
    isTracking.value = true

    // Mettre à jour la localisation toutes les 10 secondes
    updateInterval.value = setInterval(async () => {
      try {
        const newLocation = await getPosition()
        await sendLocationToServer(newLocation)
        await fetchNearbyVolunteers()
      } catch (err) {
        console.error('Erreur lors de la mise à jour automatique', err)
      }
    }, 10000)

    // Mettre à jour les volontaires proches toutes les 5 secondes
    setInterval(() => {
      fetchNearbyVolunteers()
    }, 5000)

    // Mettre à jour le temps d'expiration
    updateInterval.value = setInterval(updateExpirationTime, 1000)
  } catch (err) {
    error.value = 'Impossible d\'accéder à votre localisation. Vérifiez les permissions.'
    isTracking.value = false
  } finally {
    isLoading.value = false
  }
}

// Arrêter le suivi
const stopTracking = async () => {
  isTracking.value = false
  currentLocation.value = null
  nearbyVolunteers.value = []

  if (updateInterval.value) {
    clearInterval(updateInterval.value)
  }

  try {
    await axios.delete('/api/location')
  } catch (err) {
    console.error('Erreur lors de la suppression de la localisation', err)
  }
}

// Formater l'heure
const formatTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMin = Math.floor(diffMs / 60000)

  if (diffMin < 1) return 'À l\'instant'
  if (diffMin === 1) return 'Il y a 1 minute'
  if (diffMin < 60) return `Il y a ${diffMin} minutes`

  const diffHours = Math.floor(diffMin / 60)
  if (diffHours === 1) return 'Il y a 1 heure'
  return `Il y a ${diffHours} heures`
}

// Calculer la distance
const calculateDistance = (volunteer) => {
  if (!currentLocation.value) return '?'

  const R = 6371 // Rayon de la Terre en km
  const dLat = ((volunteer.latitude - currentLocation.value.latitude) * Math.PI) / 180
  const dLon = ((volunteer.longitude - currentLocation.value.longitude) * Math.PI) / 180
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos((currentLocation.value.latitude * Math.PI) / 180) *
      Math.cos((volunteer.latitude * Math.PI) / 180) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2)
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  const distance = R * c

  return distance.toFixed(2)
}

// Nettoyer au démontage
onUnmounted(() => {
  if (updateInterval.value) {
    clearInterval(updateInterval.value)
  }
  if (watchId.value) {
    navigator.geolocation.clearWatch(watchId.value)
  }
})
</script>

<style scoped>
.location-tracker {
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.location-header {
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 10px;
}

.location-header h5 {
  font-weight: 600;
  color: #2c3e50;
  display: flex;
  align-items: center;
}

.location-status .badge {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  padding: 4px 8px;
}

.location-controls {
  margin-bottom: 15px;
}

.location-controls .btn {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  font-weight: 500;
}

.alert-sm {
  padding: 8px 12px;
  font-size: 13px;
  border-radius: 6px;
}

.card {
  border: none;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-body {
  padding: 15px;
}

.card-title {
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
  margin-bottom: 10px;
}

.location-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 13px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 0;
}

.info-row .label {
  color: #666;
  font-weight: 500;
}

.info-row .value {
  color: #333;
  font-family: 'Courier New', monospace;
  font-weight: 600;
}

.volunteers-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.volunteer-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: white;
  border-radius: 6px;
  border-left: 3px solid #ff6b35;
}

.volunteer-info {
  flex: 1;
  font-size: 13px;
}

.volunteer-info strong {
  color: #2c3e50;
  display: block;
  margin-bottom: 2px;
}

.volunteer-badge {
  margin-left: 10px;
}

.volunteer-badge .badge {
  font-size: 11px;
  padding: 4px 8px;
}
</style>
