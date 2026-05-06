<template>
  <div class="user-map-page">
    <div class="container-fluid py-4">
      <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
          <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">
              <MapPin style="width:28px;height:28px" class="me-2" />
              Carte des volontaires
            </h1>
            <router-link to="/dashboard" class="btn btn-outline-secondary">
              <ArrowLeft style="width:16px;height:16px" class="me-1" />
              Retour
            </router-link>
          </div>
        </div>

        <!-- Location Tracker -->
        <div class="col-lg-4">
          <UserLocation />
        </div>

        <!-- Map Container -->
        <div class="col-lg-8">
          <div class="card" style="height: 600px; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1)">
            <div id="map" style="width: 100%; height: 100%;"></div>
          </div>
        </div>
      </div>

      <!-- Volunteer List -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">
                <Users style="width:20px;height:20px" class="me-2" />
                Volontaires actifs
              </h5>
            </div>
            <div class="card-body">
              <div v-if="volunteers.length > 0" class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead>
                    <tr>
                      <th>Nom</th>
                      <th>Position</th>
                      <th>Distance</th>
                      <th>Mis à jour</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="volunteer in volunteers" :key="volunteer.id_utilisateur">
                      <td>
                        <strong>{{ volunteer.prenom_utilisateur }} {{ volunteer.nom_utilisateur }}</strong>
                      </td>
                      <td>
                        <code style="font-size:11px">{{ volunteer.latitude.toFixed(4) }}, {{ volunteer.longitude.toFixed(4) }}</code>
                      </td>
                      <td>
                        <span v-if="currentLocation" class="badge bg-info">
                          {{ calculateDistance(volunteer) }} km
                        </span>
                        <span v-else class="text-muted">-</span>
                      </td>
                      <td>
                        <small class="text-muted">{{ formatTime(volunteer.updated_at) }}</small>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" @click="centerOnVolunteer(volunteer)">
                          Centrer
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-4">
                <p class="text-muted">Aucun volontaire actif pour le moment</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { MapPin, ArrowLeft, Users } from 'lucide-vue-next'
import UserLocation from '@/components/UserLocation.vue'
import axios from 'axios'
import L from 'leaflet'

const volunteers = ref([])
const currentLocation = ref(null)
const map = ref(null)
const markers = ref(new Map())
const refreshInterval = ref(null)

// Initialiser la carte
const initMap = () => {
  map.value = L.map('map').setView([46.2044, 6.1432], 13) // Genève par défaut

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map.value)
}

// Récupérer les volontaires
const fetchVolunteers = async () => {
  try {
    const response = await axios.get('/api/locations')
    volunteers.value = response.data.locations || []

    // Récupérer ma position
    const meResponse = await axios.get('/api/location')
    if (meResponse.data.location) {
      currentLocation.value = meResponse.data.location
    }

    updateMarkers()
  } catch (err) {
    console.error('Erreur lors de la récupération des volontaires', err)
  }
}

// Mettre à jour les marqueurs sur la carte
const updateMarkers = () => {
  // Supprimer les anciens marqueurs
  markers.value.forEach((marker) => {
    map.value.removeLayer(marker)
  })
  markers.value.clear()

  // Ma position
  if (currentLocation.value) {
    const myMarker = L.circleMarker(
      [currentLocation.value.latitude, currentLocation.value.longitude],
      {
        radius: 8,
        fillColor: '#FF6B35',
        color: '#fff',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.8,
      }
    )
      .bindPopup(`<strong>Ma position</strong><br>Latitude: ${currentLocation.value.latitude.toFixed(5)}<br>Longitude: ${currentLocation.value.longitude.toFixed(5)}`)
      .addTo(map.value)

    markers.value.set('me', myMarker)

    // Centrer sur ma position
    map.value.setView([currentLocation.value.latitude, currentLocation.value.longitude], 14)
  }

  // Autres volontaires
  volunteers.value.forEach((volunteer) => {
    if (volunteer.id_utilisateur !== currentLocation.value?.id_utilisateur) {
      const marker = L.circleMarker(
        [volunteer.latitude, volunteer.longitude],
        {
          radius: 6,
          fillColor: '#2C3E50',
          color: '#fff',
          weight: 2,
          opacity: 1,
          fillOpacity: 0.7,
        }
      )
        .bindPopup(
          `<strong>${volunteer.prenom_utilisateur} ${volunteer.nom_utilisateur}</strong><br>Latitude: ${volunteer.latitude.toFixed(5)}<br>Longitude: ${volunteer.longitude.toFixed(5)}<br>Mis à jour: ${formatTime(volunteer.updated_at)}`
        )
        .addTo(map.value)

      markers.value.set(volunteer.id_utilisateur, marker)
    }
  })
}

// Centrer sur un volontaire
const centerOnVolunteer = (volunteer) => {
  map.value.setView([volunteer.latitude, volunteer.longitude], 15)
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

  const R = 6371
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

// Cycle de mise à jour
onMounted(() => {
  initMap()
  fetchVolunteers()

  refreshInterval.value = setInterval(fetchVolunteers, 5000)
})

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})
</script>

<style scoped>
.user-map-page {
  background: #f5f5f5;
  min-height: 100vh;
}

.page-title {
  font-size: 32px;
  font-weight: 600;
  color: #2c3e50;
  display: flex;
  align-items: center;
}

.card {
  border: none;
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-header {
  background: white;
  border-bottom: 1px solid #e9ecef;
  padding: 15px;
}

.card-header h5 {
  font-weight: 600;
  color: #2c3e50;
  display: flex;
  align-items: center;
}

.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}

.table thead th {
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  color: #2c3e50;
  font-size: 13px;
}

code {
  background: #f0f0f0;
  color: #333;
  padding: 2px 6px;
  border-radius: 3px;
}

@media (max-width: 992px) {
  .col-lg-4,
  .col-lg-8 {
    width: 100%;
  }
}
</style>
