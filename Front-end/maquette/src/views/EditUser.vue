<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Not found -->
    <div v-if="!targetUser" class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="text-center">
        <p class="text-muted mb-3">Utilisateur non trouvé</p>
        <button class="btn btn-primary" @click="router.push('/manage-users')">Retour à la gestion</button>
      </div>
    </div>

    <template v-else>
      <!-- Header -->
      <header class="bg-white border-bottom sticky-top">
        <div class="p-3 d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <h1 class="fs-5 fw-semibold mb-0">Modifier les Droits</h1>
        </div>
      </header>

      <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:768px">

        <!-- Alertes statut -->
        <div v-if="targetUser.suspended" class="alert alert-danger d-flex align-items-center gap-2 mb-0">
          <UserX style="width:18px;height:18px;flex-shrink:0" />
          <span><strong>Compte suspendu.</strong> Cet utilisateur ne peut plus accéder à la plateforme.</span>
        </div>

        <div v-if="targetUser.anonymous" class="alert alert-secondary d-flex align-items-center gap-2 mb-0">
          <EyeOff style="width:18px;height:18px;flex-shrink:0" />
          <span><strong>Compte anonyme.</strong> Les informations personnelles sont masquées.</span>
        </div>

        <div v-if="targetUser.warnings >= 3 && !targetUser.suspended" class="alert alert-warning d-flex align-items-center gap-2 mb-0">
          <AlertTriangle style="width:18px;height:18px;flex-shrink:0" />
          <span><strong>Attention :</strong> Cet utilisateur a atteint {{ targetUser.warnings }} avertissements. Une suspension est recommandée.</span>
        </div>

        <!-- Infos utilisateur -->
        <div class="card">
          <div class="card-header"><h5 class="mb-0">Informations de l'utilisateur</h5></div>
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 text-white flex-shrink-0"
                style="width:64px;height:64px;background:linear-gradient(135deg,#d4e645,#a3c200)">
                {{ targetUser.firstName[0] }}{{ targetUser.lastName[0] }}
              </div>
              <div>
                <h5 class="fw-semibold mb-1">
                  {{ targetUser.anonymous ? 'Utilisateur Anonyme' : `${targetUser.firstName} ${targetUser.lastName}` }}
                </h5>
                <p class="small text-muted mb-0">{{ targetUser.anonymous ? '***@***.***' : targetUser.email }}</p>
              </div>
            </div>

            <div class="row g-3 small">
              <div class="col-6">
                <div class="text-muted">Téléphone</div>
                <div class="fw-medium">{{ targetUser.anonymous ? '***' : targetUser.phone }}</div>
              </div>
              <div class="col-6">
                <div class="text-muted">Rôle actuel</div>
                <span :class="`badge border ${roleBadgeClass(targetUser.role)}`">{{ targetUser.accountType }}</span>
              </div>
              <div class="col-6">
                <div class="text-muted">Avertissements</div>
                <div class="fw-medium" :class="warningColor(targetUser.warnings)">
                  {{ targetUser.warnings }}/3
                </div>
              </div>
              <div class="col-6">
                <div class="text-muted">Statut</div>
                <div class="fw-medium">
                  <span v-if="targetUser.suspended" class="text-danger">Suspendu</span>
                  <span v-else-if="targetUser.anonymous" class="text-secondary">Anonyme</span>
                  <span v-else class="text-success">Actif</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Avertissements -->
        <div class="card">
          <div class="card-header d-flex align-items-center gap-2">
            <AlertTriangle style="width:20px;height:20px;color:#ca8a04" />
            <h5 class="mb-0">Gestion des avertissements</h5>
          </div>
          <div class="card-body d-flex flex-column gap-3">
            <div>
              <label class="form-label small fw-medium">Nombre d'avertissements</label>
              <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary btn-sm px-3" :disabled="warnings === 0" @click="warnings = Math.max(0, warnings - 1)">−</button>
                <div class="flex-fill text-center">
                  <span class="fs-2 fw-bold" :class="warningColor(warnings)">{{ warnings }}</span>
                  <span class="text-muted"> / 3</span>
                </div>
                <button class="btn btn-outline-secondary btn-sm px-3" :disabled="warnings >= 5" @click="warnings = Math.min(5, warnings + 1)">+</button>
              </div>
            </div>

            <div v-if="warnings >= 3" class="alert alert-danger d-flex align-items-center gap-2 mb-0">
              <AlertTriangle style="width:16px;height:16px;flex-shrink:0" />
              <span class="small"><strong>Attention :</strong> À partir de 3 avertissements, le compte devrait être suspendu et le badge "Non fiable" sera visible.</span>
            </div>

            <div class="bg-light rounded p-3 small text-muted">
              <p class="fw-medium mb-2">ℹ️ Informations</p>
              <ul class="mb-0 ps-3">
                <li>1 avertissement = désinscription d'une mission</li>
                <li>3 avertissements = suspension automatique recommandée</li>
                <li>Badge "Non fiable" visible à partir de 3 avertissements</li>
              </ul>
            </div>

            <button class="btn btn-primary d-flex align-items-center justify-content-center gap-2"
              @click="alert(`Nombre d'avertissements modifié : ${warnings}`)">
              <Save style="width:16px;height:16px" />
              Enregistrer les avertissements
            </button>
          </div>
        </div>

        <!-- Modifier le rôle -->
        <div v-if="!targetUser.suspended" class="card">
          <div class="card-header"><h5 class="mb-0">Modifier le rôle et les permissions</h5></div>
          <div class="card-body">
            <form @submit.prevent="handleSubmit" class="d-flex flex-column gap-3">
              <div>
                <label class="form-label small fw-medium">Sélectionner un nouveau rôle</label>
                <div class="d-flex flex-column gap-2">
                  <button
                    v-for="role in roleOptions" :key="role.value"
                    type="button"
                    :class="['btn text-start p-3 border-2 rounded', selectedRole === role.value ? `${roleBadgeClass(role.value)} border-current` : 'btn-outline-secondary']"
                    @click="selectedRole = role.value"
                  >
                    <div class="fw-medium">{{ role.label }}</div>
                    <div class="small opacity-75">{{ role.desc }}</div>
                  </button>
                </div>
              </div>

              <div class="alert alert-info small mb-0">
                <div class="fw-medium mb-1">Hiérarchie des rôles</div>
                <ul class="mb-0 ps-3">
                  <li>Bénévole : Utilisateur de base</li>
                  <li>Organisateur : Bénévole + gestion opérationnelle des missions</li>
                  <li>Admin : Organisateur + gestion stratégique des événements</li>
                  <li>Super-admin : Détient l'intégralité des droits de la plateforme</li>
                </ul>
              </div>

              <div class="d-flex gap-3 pt-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" @click="router.go(-1)">Annuler</button>
                <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2">
                  <Save style="width:16px;height:16px" />
                  Enregistrer
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Gestion du compte -->
        <div class="card">
          <div class="card-header"><h5 class="mb-0">Gestion du compte</h5></div>
          <div class="card-body d-flex flex-column gap-2">
            <template v-if="targetUser.suspended">
              <button class="btn btn-success d-flex align-items-center justify-content-center gap-2"
                @click="showReactivateDialog = true">
                <Eye style="width:16px;height:16px" />
                Réactiver le compte
              </button>
            </template>
            <template v-else>
              <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2"
                @click="showAnonymizeDialog = true">
                <EyeOff style="width:16px;height:16px" />
                {{ targetUser.anonymous ? "Retirer l'anonymat" : 'Rendre anonyme' }}
              </button>
              <button class="btn btn-danger d-flex align-items-center justify-content-center gap-2"
                @click="showSuspendDialog = true">
                <UserX style="width:16px;height:16px" />
                Suspendre le compte
              </button>
            </template>
          </div>
        </div>

      </div>
    </template>

    <!-- Modal Suspension -->
    <Teleport to="body">
      <div v-if="showSuspendDialog" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Suspendre le compte ?</h5>
            </div>
            <div class="modal-body">
              Êtes-vous sûr de vouloir suspendre le compte de
              <strong>{{ targetUser?.firstName }} {{ targetUser?.lastName }}</strong> ?
              L'utilisateur ne pourra plus accéder à la plateforme tant que le compte n'est pas réactivé.
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="showSuspendDialog = false">Annuler</button>
              <button class="btn btn-danger" @click="handleSuspend">Suspendre</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Anonymisation -->
    <Teleport to="body">
      <div v-if="showAnonymizeDialog" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ targetUser?.anonymous ? "Retirer l'anonymat ?" : 'Rendre le compte anonyme ?' }}</h5>
            </div>
            <div class="modal-body">
              <template v-if="targetUser?.anonymous">
                Les informations personnelles de <strong>{{ targetUser?.firstName }} {{ targetUser?.lastName }}</strong> redeviendront visibles.
              </template>
              <template v-else>
                Les informations personnelles de <strong>{{ targetUser?.firstName }} {{ targetUser?.lastName }}</strong> seront masquées et remplacées par des données anonymes.
              </template>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="showAnonymizeDialog = false">Annuler</button>
              <button class="btn btn-primary" @click="handleAnonymize">Confirmer</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Réactivation -->
    <Teleport to="body">
      <div v-if="showReactivateDialog" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Réactiver le compte ?</h5>
            </div>
            <div class="modal-body">
              Êtes-vous sûr de vouloir réactiver le compte de
              <strong>{{ targetUser?.firstName }} {{ targetUser?.lastName }}</strong> ?
              L'utilisateur pourra à nouveau accéder à la plateforme.
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="showReactivateDialog = false">Annuler</button>
              <button class="btn btn-success" @click="handleReactivate">Réactiver</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, Save, AlertTriangle, UserX, Eye, EyeOff } from 'lucide-vue-next'
import { getCurrentUser, isRole } from '@/utils/auth'
import { users } from '@/data/mockData'

const router = useRouter()
const route = useRoute()
const currentUser = getCurrentUser()
if (!currentUser || !isRole('superadmin')) router.push('/')

const targetUser = users.find(u => u.id === route.params.id)

const selectedRole = ref(targetUser?.role || 'volunteer')
const warnings = ref(targetUser?.warnings || 0)
const showSuspendDialog = ref(false)
const showAnonymizeDialog = ref(false)
const showReactivateDialog = ref(false)

const roleOptions = [
  { value: 'volunteer',   label: 'Bénévole',    desc: 'Utilisateur de base' },
  { value: 'organizer',   label: 'Organisateur', desc: 'Bénévole + gestion des missions' },
  { value: 'admin',       label: 'Admin',        desc: 'Organisateur + gestion des événements' },
  { value: 'superadmin',  label: 'Super-admin',  desc: 'Tous les droits de la plateforme' },
]

const roleBadgeClass = (role) => ({
  volunteer:  'bg-primary-subtle text-primary border-primary',
  organizer:  'bg-success-subtle text-success border-success',
  admin:      'bg-info-subtle text-info border-info',
  superadmin: 'bg-danger-subtle text-danger border-danger',
}[role] || 'bg-secondary-subtle text-secondary border-secondary')

const warningColor = (w) =>
  w >= 3 ? 'text-danger' : w > 0 ? 'text-warning' : 'text-success'

const handleSubmit = () => {
  alert(`Rôle de ${targetUser.firstName} ${targetUser.lastName} mis à jour avec succès !`)
  router.push('/manage-users')
}
const handleSuspend = () => {
  alert(`Utilisateur ${targetUser.firstName} ${targetUser.lastName} suspendu.`)
  showSuspendDialog.value = false
  router.push('/manage-users')
}
const handleAnonymize = () => {
  alert(`Utilisateur ${targetUser.firstName} ${targetUser.lastName} rendu anonyme.`)
  showAnonymizeDialog.value = false
  router.push('/manage-users')
}
const handleReactivate = () => {
  alert(`Utilisateur ${targetUser.firstName} ${targetUser.lastName} réactivé.`)
  showReactivateDialog.value = false
}
</script>