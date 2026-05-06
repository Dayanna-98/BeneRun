<template>
  <div class="min-vh-100 bg-light pb-5">

    <div v-if="isLoading" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center text-muted">Chargement de la mission...</div>
    </div>

    <div v-else-if="loadError" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-danger mb-3">{{ loadError }}</p>
        <button class="btn btn-primary" @click="loadMissionDetails">Réessayer</button>
      </div>
    </div>

    <!-- Not found -->
    <div v-else-if="!mission" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-muted mb-3">Mission non trouvée</p>
        <button class="btn btn-primary" @click="router.push('/')">Retour à l'accueil</button>
      </div>
    </div>

    <template v-else>
      <!-- Header -->
      <header class="bg-white border-bottom sticky-top">
        <div class="p-3 d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Détails de la mission</h1>
        </div>
      </header>

      <div class="mx-auto" style="max-width:576px">

        <!-- Image -->
        <img
          :src="mission.imageUrl || 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop'"
          :alt="mission.name"
          class="w-100 object-fit-cover"
          style="height:224px"
        />

        <div class="p-3 d-flex flex-column gap-3">

          <div v-if="actionSuccess" class="alert alert-success mb-0">{{ actionSuccess }}</div>

          <!-- Titre -->
          <div>
            <div class="small text-muted mb-1">{{ mission.eventName }}</div>
            <h2 class="fs-4 fw-semibold mb-0">{{ mission.name }}</h2>
          </div>

          <!-- Infos principales -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex align-items-start gap-3">
                <Calendar class="text-muted mt-1" style="width:20px;height:20px;flex-shrink:0" />
                <div>
                  <div class="small fw-medium">Date</div>
                  <div class="small text-muted">{{ formatDate(mission.date) }}</div>
                </div>
              </div>
              <div class="d-flex align-items-start gap-3">
                <Clock class="text-muted mt-1" style="width:20px;height:20px;flex-shrink:0" />
                <div>
                  <div class="small fw-medium">Horaires</div>
                  <div class="small text-muted">{{ mission.startTime }} - {{ mission.endTime }}</div>
                </div>
              </div>
              <div class="d-flex align-items-start gap-3">
                <MapPin class="text-muted mt-1" style="width:20px;height:20px;flex-shrink:0" />
                <div>
                  <div class="small fw-medium">Lieu</div>
                  <div class="small text-muted">{{ mission.location }}</div>
                </div>
              </div>
              <div class="d-flex align-items-start gap-3">
                <Users class="text-muted mt-1" style="width:20px;height:20px;flex-shrink:0" />
                <div>
                  <div class="small fw-medium">Bénévoles</div>
                  <div class="small text-muted">{{ mission.currentVolunteers }}/{{ mission.maxVolunteers }} inscrits</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="card">
            <div class="card-header"><h5 class="mb-0">Description</h5></div>
            <div class="card-body">
              <p class="small text-muted mb-0">{{ mission.description }}</p>
            </div>
          </div>

          <!-- Carte unifiée (toujours visible quand coordonnées disponibles) -->
          <div class="card">
            <div class="card-header"><h5 class="mb-0">Carte</h5></div>
            <div class="card-body">
              <MissionLiveMap
                :mission-id="mission.id"
                :mission-point="mission.missionPoint"
                :event-center="mission.eventCenter"
                :radius-meters="mission.eventRadiusMeters"
                :current-user-id="currentUser?.id || ''"
                :is-active-mission="isActiveMission"
                :google-maps-url="mission.googleMapsUrl || mission.eventGoogleMapsUrl"
              />
            </div>
          </div>

          <!-- Compétences -->
          <div class="card">
            <div class="card-header"><h5 class="mb-0">Compétences requises</h5></div>
            <div class="card-body d-flex flex-wrap gap-2">
              <span v-for="skill in mission.requiredSkills" :key="skill"
                class="badge bg-primary-subtle text-primary border border-primary">
                {{ skill }}
              </span>
            </div>
          </div>

          <!-- Organisation -->
          <div class="card">
            <div class="card-header"><h5 class="mb-0">Organisation</h5></div>
            <div class="card-body d-flex flex-column gap-3">
              <div>
                <div class="x-small text-muted mb-1">Organisateur</div>
                <div class="small fw-medium">{{ mission.organizer }}</div>
              </div>
              <div class="border-top pt-3">
                <div class="x-small text-muted mb-2">Responsables de la mission</div>
                <div v-for="(manager, index) in mission.missionManagers" :key="manager.id"
                  :class="index > 0 ? 'mt-3 pt-3 border-top' : ''">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="small fw-medium">{{ manager.name }}</div>
                    <span v-if="manager.isDayContact" class="badge text-dark x-small" style="background:#d4e645">Contact du jour</span>
                    <span v-if="manager.isDefault" class="badge bg-secondary-subtle text-secondary border x-small">Par défaut</span>
                  </div>
                  <div class="d-flex flex-column gap-2">
                    <a :href="`tel:${manager.phone}`" class="d-flex align-items-center gap-2 small text-primary text-decoration-none">
                      <Phone style="width:16px;height:16px" /> {{ manager.phone }}
                    </a>
                    <a :href="`mailto:${manager.email}`" class="d-flex align-items-center gap-2 small text-primary text-decoration-none">
                      <Mail style="width:16px;height:16px" /> {{ manager.email }}
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Inscrire un utilisateur (superadmin) -->
          <div v-if="currentUser?.role === 'superadmin'" class="card">
            <div class="card-header d-flex align-items-center gap-2">
              <UserPlus style="width:20px;height:20px" />
              <h5 class="mb-0">Inscrire un utilisateur</h5>
            </div>
            <div class="card-body d-flex flex-column gap-3">
              <div class="alert alert-info small mb-0">
                En tant que super admin, vous pouvez inscrire directement des utilisateurs à cette mission.
              </div>
              <div>
                <label class="form-label small fw-medium">Sélectionner un utilisateur</label>
                <select v-model="selectedUserId" class="form-select">
                  <option value="">Choisir un utilisateur...</option>
                  <option v-for="u in availableUsers" :key="u.id" :value="u.id">
                    {{ u.firstName }} {{ u.lastName }} - {{ u.accountType }}
                  </option>
                </select>
              </div>
              <div v-if="selectedUserId" class="bg-light rounded p-3">
                <div class="x-small text-muted mb-1">Utilisateur sélectionné</div>
                <div class="small fw-medium">{{ selectedUserInfo?.firstName }} {{ selectedUserInfo?.lastName }}</div>
                <div class="x-small text-muted">{{ selectedUserInfo?.email }} • {{ selectedUserInfo?.accountType }}</div>
              </div>
              <button class="btn btn-primary d-flex align-items-center justify-content-center gap-2"
                :disabled="!selectedUserId"
                @click="handleRegisterUser">
                <UserPlus style="width:16px;height:16px" />
                Inscrire à la mission
              </button>
            </div>
          </div>

          <!-- Onglets Chat + Urgence (missions actives uniquement) -->
          <div v-if="isActiveMission" class="card">
            <div class="card-body p-0">
              <!-- Tab nav -->
              <ul class="nav nav-tabs nav-fill border-bottom">
                <li class="nav-item">
                  <button :class="['nav-link', activeTab === 'chat' ? 'active' : '']" @click="activeTab = 'chat'">
                    <MessageCircle style="width:16px;height:16px;margin-right:4px" />Chat
                  </button>
                </li>
                <li class="nav-item">
                  <button :class="['nav-link', activeTab === 'emergency' ? 'active' : '']" @click="activeTab = 'emergency'">
                    <AlertCircle style="width:16px;height:16px;margin-right:4px" />Urgence
                  </button>
                </li>
              </ul>

              <!-- Chat -->
              <div v-if="activeTab === 'chat'" class="p-3 d-flex flex-column gap-3">
                <div class="bg-light rounded p-3 d-flex flex-column gap-2 overflow-y-auto" style="height:256px">
                  <div v-for="msg in mission.messages" :key="msg.id" class="bg-white rounded p-3">
                    <div class="x-small fw-medium mb-1">{{ msg.senderName }}</div>
                    <div class="small text-muted mb-1">{{ msg.message }}</div>
                    <div class="x-small text-muted">
                      {{ new Date(msg.timestamp).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) }}
                    </div>
                  </div>
                </div>
                <div class="d-flex gap-2">
                  <textarea v-model="chatMessage" class="form-control flex-fill" rows="2"
                    placeholder="Votre message..." style="resize:none"></textarea>
                  <button class="btn btn-primary" @click="sendChatMessage">
                    <Send style="width:16px;height:16px" />
                  </button>
                </div>
              </div>

              <!-- Urgence -->
              <div v-if="activeTab === 'emergency'" class="p-3 d-flex flex-column gap-3">
                <div class="alert alert-danger d-flex align-items-start gap-2 mb-0">
                  <AlertCircle style="width:20px;height:20px;flex-shrink:0;margin-top:2px" />
                  <div>
                    <div class="small fw-medium mb-1">Messagerie d'urgence</div>
                    <p class="x-small mb-0">Sélectionnez une catégorie d'urgence et écrivez votre message. Il sera automatiquement envoyé à tous les superadmins.</p>
                  </div>
                </div>

                <div v-if="emergencySendFeedback" class="alert alert-success small mb-0">{{ emergencySendFeedback }}</div>

                <div>
                  <label class="form-label small fw-medium">Catégorie d'urgence</label>
                  <div class="d-flex flex-column gap-2">
                    <button v-for="cat in emergencyCategories" :key="cat.value"
                      :class="['btn text-start p-3 border-2', emergencyCategory === cat.value ? cat.btnClass : 'btn-outline-secondary']"
                      @click="emergencyCategory = cat.value">
                      <span class="small fw-medium">{{ cat.label }}</span>
                    </button>
                  </div>
                </div>

                <div>
                  <label class="form-label small fw-medium">Message d'urgence</label>
                  <textarea v-model="emergencyMessage" class="form-control" rows="4"
                    placeholder="Décrivez la situation..."
                    :disabled="!emergencyCategory" style="resize:none"></textarea>
                  <p v-if="!emergencyCategory" class="x-small text-muted mt-1">Veuillez d'abord sélectionner une catégorie</p>
                </div>

                <button class="btn btn-danger d-flex align-items-center justify-content-center gap-2"
                  :disabled="!emergencyCategory || !emergencyMessage.trim()"
                  @click="sendEmergencyMessage">
                  <AlertCircle style="width:16px;height:16px" />
                  Envoyer aux superadmins
                </button>

                <div v-if="mission.emergencyMessages?.length" class="d-flex flex-column gap-2">
                  <div class="small fw-medium">Messages d'urgence récents</div>
                  <div v-for="msg in mission.emergencyMessages" :key="msg.id"
                    class="alert alert-danger mb-0">
                    <div class="x-small fw-medium mb-1">{{ msg.senderName }}</div>
                    <div class="small">{{ msg.message }}</div>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>

      <!-- Sticky footer -->
      <div class="position-sticky bottom-0 bg-white border-top p-3">
        <div v-if="actionError" class="alert alert-danger mb-2 py-2 small">{{ actionError }}</div>
        <button
          v-if="!isActiveMission"
          class="btn btn-primary btn-lg w-100"
          :disabled="isSubmittingRegistration || !canCurrentUserRegister"
          @click="handleMissionRegistration">
          {{ registrationButtonLabel }}
        </button>
        <button v-else class="btn btn-danger btn-lg w-100 d-flex align-items-center justify-content-center gap-2"
          @click="showUnregisterDialog = true">
          <UserMinus style="width:16px;height:16px" />
          Se désinscrire de cette mission
        </button>
      </div>

    </template>

    <!-- Modal désinscription -->
    <Teleport to="body">
      <div v-if="showUnregisterDialog" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Se désinscrire ?</h5>
            </div>
            <div class="modal-body small">
              Êtes-vous sûr de vouloir vous désinscrire de la mission <strong>{{ mission?.name }}</strong> ?
              Les responsables en seront informés.
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="showUnregisterDialog = false">Annuler</button>
              <button class="btn btn-danger" @click="handleUnregister">Se désinscrire</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import {
  ArrowLeft, MapPin, Calendar, Clock, Users, Phone, Mail,
  MessageCircle, AlertCircle, Send, UserMinus, UserPlus
} from 'lucide-vue-next'
import api from '@/services/api'
import chatService from '@/services/chatService'
import { getCurrentUser, setCurrentUser } from '@/utils/auth'
import MissionLiveMap from '@/components/maps/MissionLiveMap.vue'
import { extractGoogleMapsCoordinates } from '@/utils/googleMaps'
import userService from '@/services/userService'
import emergencyService from '@/services/emergencyService'

const router = useRouter()
const route  = useRoute()
const currentUser = ref(getCurrentUser())

const mission = ref(null)
const isLoading = ref(false)
const loadError = ref('')
const actionSuccess = ref('')
const actionError = ref('')
const isSubmittingRegistration = ref(false)
const availableUsers = ref([])

const toTime = (value) => {
  if (!value || typeof value !== 'string') return ''
  return value.slice(0, 5)
}

const formatUserName = (user) => {
  if (!user) return 'Responsable mission'
  return `${user.prenom_utilisateur ?? ''} ${user.nom_utilisateur ?? ''}`.trim() || user.email
}

const mapMissionFromApi = (rawMission, eventName, manager, currentVolunteersCount) => ({
  id: String(rawMission.id_mission),
  eventId: String(rawMission.id_evenement || ''),
  eventName: eventName || `Événement #${rawMission.id_evenement}`,
  name: rawMission.titre_mission,
  imageUrl: null,
  date: rawMission.date_mission,
  startTime: toTime(rawMission.heure_debut_mission),
  endTime: toTime(rawMission.heure_fin_mission),
  location: rawMission.lieu_mission,
  googleMapsUrl: rawMission.google_maps_url_mission || '',
  eventGoogleMapsUrl: rawMission.evenement?.google_maps_url_evenement || '',
  eventRadiusMeters: Number(rawMission.evenement?.rayon_localisation_evenement || 0),
  missionPoint: extractGoogleMapsCoordinates(rawMission.google_maps_url_mission),
  eventCenter: extractGoogleMapsCoordinates(rawMission.evenement?.google_maps_url_evenement),
  status: rawMission.statut_mission || 'À venir',
  currentVolunteers: currentVolunteersCount,
  maxVolunteers: Number(rawMission.nombre_benevoles_max) || 0,
  description: rawMission.description_mission,
  requiredSkills: Array.isArray(rawMission.competences)
    ? rawMission.competences
      .map((competence) => competence?.nom_competence)
      .filter(Boolean)
    : [],
  organizer: 'BeneRun',
  missionManagers: [
    {
      id: manager?.id_utilisateur ?? String(rawMission.responsable_utilisateur_id),
      name: formatUserName(manager),
      phone: manager?.telephone_utilisateur || 'Non renseigné',
      email: manager?.email || 'Non renseigné',
      isDayContact: true,
      isDefault: true,
    },
  ],
  volunteers: [],
  messages: [],
  emergencyMessages: [],
})

const loadMissionDetails = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const missionId = route.params.id

    const [missionResponse, usersResponse, affectationsResponse, postulationsResponse] = await Promise.all([
      api.get(`/missions/${missionId}`),
      api.get('/users'),
      api.get('/affectations'),
      api.get('/postulations'),
    ])

    const rawMission = missionResponse.data
    if (!rawMission || !rawMission.id_mission) {
      mission.value = null
      return
    }

    const users = usersResponse.data ?? []
    const affectations = affectationsResponse.data ?? []
    const postulations = Array.isArray(postulationsResponse.data) ? postulationsResponse.data : []
    const missionAffectations = affectations.filter((affectation) =>
      String(affectation.id_mission) === String(rawMission.id_mission)
      && ['assigne', 'confirme', 'present'].includes(affectation.statut_affectation)
    )
    const currentUserPostulation = postulations.find((postulation) =>
      String(postulation.id_mission) === String(rawMission.id_mission)
      && String(postulation.id_utilisateur) === String(currentUser.value?.id || '')
      && !['annule', 'refuse'].includes(String(postulation.statut_postulation || '').toLowerCase())
    )

    const managerUser = rawMission.responsable
      || users.find(u => u.id_utilisateur === rawMission.responsable_utilisateur_id)

    const currentVolunteersCount = Number(rawMission.current_volunteers_count ?? missionAffectations.length)

    const participants = missionAffectations.map((affectation) => {
      const participant = affectation.utilisateur || users.find((user) => String(user.id_utilisateur) === String(affectation.id_utilisateur))

      return {
        id: String(participant?.id_utilisateur ?? affectation.id_utilisateur),
        name: `${participant?.prenom_utilisateur || ''} ${participant?.nom_utilisateur || ''}`.trim() || 'Participant',
        latitude: participant?.partage_localisation_directe_utilisateur ? Number(participant.latitude_localisation_directe_utilisateur) : null,
        longitude: participant?.partage_localisation_directe_utilisateur ? Number(participant.longitude_localisation_directe_utilisateur) : null,
        updatedAt: participant?.date_localisation_directe_utilisateur || null,
      }
    })

    mission.value = mapMissionFromApi(
      rawMission,
      rawMission.evenement?.nom_evenement,
      managerUser,
      currentVolunteersCount
    )
    mission.value.volunteers = participants
    mission.value.isParticipant = missionAffectations.some((affectation) => String(affectation.id_utilisateur) === String(currentUser.value?.id || ''))
    mission.value.registrationStatus = currentUserPostulation?.statut_postulation || null
    mission.value.currentUserPostulationId = currentUserPostulation?.id_postulation ? String(currentUserPostulation.id_postulation) : null
    availableUsers.value = users.map((user) => ({
      id: String(user.id_utilisateur),
      firstName: user.prenom_utilisateur,
      lastName: user.nom_utilisateur,
      email: user.email,
      accountType: 'Utilisateur',
    }))
  } catch (error) {
    console.error('Erreur lors du chargement de la mission:', error)
    loadError.value = 'Impossible de charger les détails de la mission.'
  } finally {
    isLoading.value = false
  }
}

const isActiveMission = computed(() =>
  !!mission.value
  && mission.value.status === 'En cours'
  && !!mission.value.isParticipant
)

const canCurrentUserRegister = computed(() => {
  if (!mission.value || !currentUser.value?.id) return false
  return !mission.value.isParticipant && !mission.value.registrationStatus
})

const registrationButtonLabel = computed(() => {
  if (isSubmittingRegistration.value) return 'Inscription en cours...'
  if (mission.value?.isParticipant) return 'Vous participez déjà à cette mission'
  if (mission.value?.registrationStatus === 'en_attente') return 'Inscription déjà envoyée'
  if (mission.value?.registrationStatus === 'accepte') return 'Vous êtes déjà inscrit à cette mission'
  if (mission.value && Number(mission.value.currentVolunteers || 0) >= Number(mission.value.maxVolunteers || 0)) {
    return 'Mission complète'
  }
  return "S'inscrire à cette mission"
})

const buildCurrentUserUpdatePayload = (extra = {}) => ({
  firstName: extra.firstName ?? currentUser.value.firstName,
  lastName: extra.lastName ?? currentUser.value.lastName,
  email: extra.email ?? currentUser.value.email,
  phone: extra.phone ?? (currentUser.value.phone || ''),
  address: extra.address ?? (currentUser.value.address || ''),
  dateOfBirth: extra.dateOfBirth ?? (currentUser.value.dateOfBirth || ''),
  role: extra.role ?? currentUser.value.role,
  allergies: extra.allergies ?? (currentUser.value.allergies || []),
  healthIssues: extra.healthIssues ?? (currentUser.value.healthIssues || []),
  hasLicense: extra.hasLicense ?? !!currentUser.value.hasLicense,
  isMotorized: extra.isMotorized ?? !!currentUser.value.isMotorized,
  hasVehicle: extra.hasVehicle ?? !!currentUser.value.hasVehicle,
  bibSize: extra.bibSize ?? (currentUser.value.bibSize || ''),
  isAnonymous: extra.isAnonymous ?? !!currentUser.value.anonymous,
  isSuspended: extra.isSuspended ?? !!currentUser.value.suspended,
  suspensionReason: extra.suspensionReason ?? (currentUser.value.suspensionReason || null),
  missionCount: extra.missionCount ?? (currentUser.value.missionCount || 0),
  liveLocationSharingEnabled: extra.liveLocationSharingEnabled ?? !!currentUser.value.liveLocationSharingEnabled,
  liveLocationLatitude: Object.prototype.hasOwnProperty.call(extra, 'liveLocationLatitude') ? extra.liveLocationLatitude : currentUser.value.liveLocationLatitude ?? null,
  liveLocationLongitude: Object.prototype.hasOwnProperty.call(extra, 'liveLocationLongitude') ? extra.liveLocationLongitude : currentUser.value.liveLocationLongitude ?? null,
  liveLocationUpdatedAt: Object.prototype.hasOwnProperty.call(extra, 'liveLocationUpdatedAt') ? extra.liveLocationUpdatedAt : currentUser.value.liveLocationUpdatedAt || null,
})

const chatMessage       = ref('')
const emergencyMessage  = ref('')
const emergencyCategory = ref('')
const emergencySendFeedback = ref('')
const showUnregisterDialog = ref(false)
const selectedUserId    = ref('')
const activeTab         = ref('chat')

const selectedUserInfo = computed(() =>
  availableUsers.value.find(u => u.id === selectedUserId.value)
)

const emergencyCategories = [
  { value: 'medical',       label: '🏥 Urgence Médicale',         btnClass: 'btn-danger' },
  { value: 'security',      label: '🚨 Problème de Sécurité',      btnClass: 'btn-warning' },
  { value: 'logistic',      label: '📦 Problème Logistique',       btnClass: 'btn-warning' },
  { value: 'coordination',  label: '📢 Besoin de Coordination',    btnClass: 'btn-info' },
  { value: 'other',         label: '⚠️ Autre Urgence',             btnClass: 'btn-secondary' },
]

const formatDate = (d) =>
  new Date(d).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })

const sendChatMessage = () => {
  if (chatMessage.value.trim()) {
    alert(`Message envoyé : ${chatMessage.value}`)
    chatMessage.value = ''
  }
}

const sendEmergencyMessage = async () => {
  emergencySendFeedback.value = ''

  if (!emergencyCategory.value || !emergencyMessage.value.trim() || !mission.value?.id || !currentUser.value?.id) {
    alert('Veuillez sélectionner une catégorie et écrire un message.')
    return
  }

  try {
    const response = await emergencyService.sendMissionEmergency({
      missionId: mission.value.id,
      senderUserId: currentUser.value.id,
      category: emergencyCategory.value,
      message: emergencyMessage.value.trim(),
    })

    if (response.urgence) {
      mission.value.emergencyMessages = [
        {
          id: response.urgence.id,
          senderName: response.urgence.sender.fullName,
          message: response.urgence.message,
        },
        ...(mission.value.emergencyMessages || []),
      ].slice(0, 5)
    }

    emergencySendFeedback.value = 'Message d\'urgence envoyé aux superadmins.'
    emergencyMessage.value = ''
    emergencyCategory.value = ''
  } catch (error) {
    alert(error.message || 'Envoi impossible pour le moment.')
  }
}

const registerUserToMission = async (userId, successMessage) => {
  if (!mission.value?.id || !userId) return false

  actionSuccess.value = ''
  actionError.value = ''
  isSubmittingRegistration.value = true

  try {
    const response = await api.post(`/missions/${mission.value.id}/inscriptions`, {
      id_utilisateur: Number(userId),
    })

    const postulation = response?.data?.postulation || null
    chatService.addUserToMissionGroup({
      missionId: mission.value.id,
      eventId: mission.value.eventId || null,
      missionName: mission.value.name,
      eventName: mission.value.eventName,
      userId,
    })

    if (String(userId) === String(currentUser.value?.id || '')) {
      mission.value.registrationStatus = postulation?.statut_postulation || 'en_attente'
      mission.value.currentUserPostulationId = postulation?.id_postulation ? String(postulation.id_postulation) : mission.value.currentUserPostulationId
    }

    actionSuccess.value = successMessage
    return true
  } catch (error) {
    actionError.value = error.response?.data?.message || error.message || 'Inscription impossible pour le moment.'
    return false
  } finally {
    isSubmittingRegistration.value = false
  }
}

const handleMissionRegistration = async () => {
  if (!currentUser.value?.id || !canCurrentUserRegister.value) return

  if (mission.value && Number(mission.value.currentVolunteers || 0) >= Number(mission.value.maxVolunteers || 0)) {
    alert('Cette mission a atteint son quota. Si vous souhaitez tout de même participer, inscrivez-vous à la liste d\'attente de l\'événement associé.')

    if (mission.value.eventId) {
      router.push(`/event/${mission.value.eventId}`)
    }
    return
  }

  const success = await registerUserToMission(
    currentUser.value.id,
    'Votre inscription à la mission a bien été prise en compte.'
  )

  if (success) {
    alert('Inscription validée')
  }
}

const handleUnregister = () => {
  alert('Vous avez été désinscrit de la mission. Les responsables en seront informés.')
  showUnregisterDialog.value = false
  router.push('/my-missions')
}

const handleRegisterUser = async () => {
  const u = availableUsers.value.find(u => u.id === selectedUserId.value)
  if (u && mission.value) {
    const success = await registerUserToMission(
      u.id,
      `L'utilisateur ${u.firstName} ${u.lastName} a été inscrit à la mission "${mission.value.name}".`
    )

    if (success) {
      selectedUserId.value = ''
    }
  }
}

onMounted(loadMissionDetails)
</script>