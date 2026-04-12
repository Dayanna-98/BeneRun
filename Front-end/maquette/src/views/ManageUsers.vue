<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Gestion des Utilisateurs</h1>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
          @click="router.push('/manage-users/create')">
          <Plus style="width:16px;height:16px" /> Créer
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:1024px">

      <!-- Stats -->
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary">{{ usersList.length }}</div>
              <div class="x-small text-muted">Total utilisateurs</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold" style="color:#2563eb">{{ volunteerCount }}</div>
              <div class="x-small text-muted">Bénévoles</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-success">{{ organizerCount }}</div>
              <div class="x-small text-muted">Organisateurs</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-info">{{ adminCount }}</div>
              <div class="x-small text-muted">Admins</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Liste -->
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Tous les utilisateurs</h5></div>
        <div class="card-body d-flex flex-column gap-3">
          <div v-for="u in usersList" :key="u.id"
            :class="['border rounded p-3', u.suspended ? 'border-danger bg-danger-subtle' : 'border-light-subtle']">

            <div class="d-flex align-items-start justify-content-between mb-3">
              <div class="d-flex align-items-center gap-3 flex-fill">

                <!-- Avatar -->
                <div class="position-relative flex-shrink-0">
                  <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                    style="width:48px;height:48px;background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230!important">
                    {{ u.firstName[0] }}{{ u.lastName[0] }}
                  </div>
                  <div v-if="u.suspended"
                    class="position-absolute rounded-circle bg-danger d-flex align-items-center justify-content-center"
                    style="width:20px;height:20px;top:-4px;right:-4px">
                    <UserX style="width:12px;height:12px;color:white" />
                  </div>
                  <div v-else-if="u.anonymous"
                    class="position-absolute rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                    style="width:20px;height:20px;top:-4px;right:-4px">
                    <EyeOff style="width:12px;height:12px;color:white" />
                  </div>
                </div>

                <div class="flex-fill">
                  <h6 class="fw-semibold text-primary mb-1">
                    {{ u.anonymous ? 'Utilisateur Anonyme' : `${u.firstName} ${u.lastName}` }}
                  </h6>
                  <div class="d-flex flex-wrap gap-1">
                    <span :class="`badge border ${roleBadgeClass(u.role)}`">{{ u.accountType }}</span>
                    <span v-if="u.suspended" class="badge bg-danger-subtle text-danger border border-danger">🚫 Suspendu</span>
                    <span v-if="u.anonymous" class="badge bg-secondary-subtle text-secondary border border-secondary d-inline-flex align-items-center gap-1">
                      <EyeOff style="width:12px;height:12px" /> Anonyme
                    </span>
                  </div>
                </div>
              </div>

              <button class="btn btn-link p-1 text-secondary"
                @click="router.push(`/manage-users/edit/${u.id}`)">
                <Edit style="width:16px;height:16px" />
              </button>
            </div>

            <div class="row g-2 small text-muted">
              <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                <Mail style="width:16px;height:16px" />
                <span class="x-small text-truncate">{{ u.anonymous ? '***@***.***' : u.email }}</span>
              </div>
              <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                <Phone style="width:16px;height:16px" />
                <span class="x-small">{{ u.anonymous ? '***' : u.phone }}</span>
              </div>
            </div>

            <!-- Permissions -->
            <div v-if="!u.suspended && u.permissions" class="mt-3 pt-3 border-top">
              <div class="x-small text-muted mb-2">Permissions :</div>
              <div class="d-flex flex-wrap gap-1">
                <span v-for="(val, key) in u.permissions" :key="key"
                  v-show="val === true"
                  class="badge bg-light text-dark border x-small">
                  {{ key.replace(/([A-Z])/g, ' $1').trim() }}
                </span>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Plus, Edit, UserX, EyeOff, Mail, Phone } from 'lucide-vue-next'
import { users } from '@/data/mockData'
import { getCurrentUser, isRole } from '@/utils/auth'
import userService from '@/services/userService'

const demoUsers = users.filter(userData => userData.email === 'admin@benerun.ch')

const router = useRouter()
const user = getCurrentUser()
if (!user || !isRole('superadmin')) router.push('/')

const usersList = ref([])

onMounted(async () => {
  try {
    const dbUsers = await userService.getAll()
    usersList.value = userService.mergeUsers(demoUsers, dbUsers)
  } catch (error) {
    console.error('Erreur chargement utilisateurs:', error)
    usersList.value = [...demoUsers]
  }
})

const volunteerCount  = computed(() => usersList.value.filter(u => u.role === 'volunteer').length)
const organizerCount  = computed(() => usersList.value.filter(u => u.role === 'organizer' || u.role === 'mission_manager').length)
const adminCount      = computed(() => usersList.value.filter(u => u.role === 'admin' || u.role === 'superadmin').length)

const roleBadgeClass = (role) => ({
  volunteer:  'bg-primary-subtle text-primary border-primary',
  organizer:  'bg-success-subtle text-success border-success',
  mission_manager: 'bg-success-subtle text-success border-success',
  admin:      'bg-info-subtle text-info border-info',
  superadmin: 'bg-danger-subtle text-danger border-danger',
}[role] || 'bg-secondary-subtle text-secondary border-secondary')
</script>