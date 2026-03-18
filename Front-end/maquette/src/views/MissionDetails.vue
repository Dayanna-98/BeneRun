<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Not found -->
    <div v-if="!mission" class="min-vh-100 d-flex align-items-center justify-content-center">
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
        <img :src="mission.imageUrl" :alt="mission.name" class="w-100 object-fit-cover" style="height:224px" />

        <div class="p-3 d-flex flex-column gap-3">

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

          <!-- Tabs (missions actives uniquement) -->
          <div v-if="isActiveMission && mission.volunteers" class="card">
            <div class="card-body p-0">
              <!-- Tab nav -->
              <ul class="nav nav-tabs nav-fill border-bottom">
                <li class="nav-item">
                  <button :class="['nav-link', activeTab === 'map' ? 'active' : '']" @click="activeTab = 'map'">
                    <Map style="width:16px;height:16px;margin-right:4px" />Carte
                  </button>
                </li>
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

              <!-- Carte -->
              <div v-if="activeTab === 'map'" class="p-3 d-flex flex-column gap-3">
                <div class="rounded position-relative overflow-hidden" style="height:256px;background:linear-gradient(135deg,#dbeafe,#dcfce7)">
                  <div v-for="(volunteer, index) in mission.volunteers" :key="volunteer.id"
                    class="position-absolute rounded-circle bg-primary border border-4 border-white shadow d-flex align-items-center justify-content-center text-white x-small fw-bold"
                    style="width:32px;height:32px"
                    :style="`left:${20 + index * 15}%;top:${30 + (index % 2) * 20}%`">
                    {{ volunteer.name.split(' ').map(n => n[0]).join('') }}
                  </div>
                  <div class="position-absolute rounded-circle bg-danger border border-4 border-white shadow d-flex align-items-center justify-content-center"
                    style="width:40px;height:40px;top:50%;left:50%;transform:translate(-50%,-50%)">
                    <MapPin style="width:24px;height:24px;color:white" />
                  </div>
                  <div class="position-absolute bg-white px-2 py-1 rounded shadow x-small" style="bottom:12px;left:12px">
                    📍 Point de rendez-vous
                  </div>
                </div>
                <div>
                  <div class="small fw-medium mb-2">Bénévoles présents ({{ mission.volunteers.length }})</div>
                  <div class="d-flex flex-column gap-2">
                    <div v-for="v in mission.volunteers" :key="v.id"
                      class="d-flex align-items-center gap-2 small bg-light p-2 rounded">
                      <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white x-small"
                        style="width:24px;height:24px;flex-shrink:0">
                        {{ v.name.split(' ').map(n => n[0]).join('') }}
                      </div>
                      {{ v.name }}
                    </div>
                  </div>
                </div>
              </div>

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
                    <p class="x-small mb-0">Sélectionnez une catégorie d'urgence et écrivez votre message. Il sera automatiquement envoyé à tous les participants de la mission.</p>
                  </div>
                </div>

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
                  Envoyer à tous les participants
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
        <button v-if="!isActiveMission" class="btn btn-primary btn-lg w-100">
          S'inscrire à cette mission
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
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import {
  ArrowLeft, MapPin, Calendar, Clock, Users, Phone, Mail,
  MessageCircle, AlertCircle, Send, Map, UserMinus, UserPlus
} from 'lucide-vue-next'
import { myActiveMissions, availableMissions, users } from '@/data/mockData'
import { getCurrentUser } from '@/utils/auth'

const router = useRouter()
const route  = useRoute()
const currentUser = getCurrentUser()

const mission = myActiveMissions.find(m => m.id === route.params.id)
             || availableMissions.find(m => m.id === route.params.id)

const isActiveMission = myActiveMissions.some(m => m.id === route.params.id)

const chatMessage       = ref('')
const emergencyMessage  = ref('')
const emergencyCategory = ref('')
const showUnregisterDialog = ref(false)
const selectedUserId    = ref('')
const activeTab         = ref('map')

const registeredUserIds = mission?.volunteers?.map(v => v.id) || []
const availableUsers    = users.filter(u => !registeredUserIds.includes(u.id))
const selectedUserInfo  = computed(() => users.find(u => u.id === selectedUserId.value))

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

const sendEmergencyMessage = () => {
  if (emergencyCategory.value && emergencyMessage.value.trim()) {
    alert(`🚨 Message d'urgence envoyé !\n\nCatégorie : ${emergencyCategory.value}\nMessage : ${emergencyMessage.value}`)
    emergencyMessage.value = ''
    emergencyCategory.value = ''
  } else {
    alert('Veuillez sélectionner une catégorie et écrire un message.')
  }
}

const handleUnregister = () => {
  alert('Vous avez été désinscrit de la mission. Les responsables en seront informés.')
  showUnregisterDialog.value = false
  router.push('/my-missions')
}

const handleRegisterUser = () => {
  const u = users.find(u => u.id === selectedUserId.value)
  if (u) {
    alert(`L'utilisateur ${u.firstName} ${u.lastName} a été inscrit à la mission "${mission.name}".`)
    selectedUserId.value = ''
  }
}
</script>