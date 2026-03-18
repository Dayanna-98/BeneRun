<template>
  <div class="min-vh-100 bg-light pb-5">

    <!-- Header -->
    <header class="bg-white border-bottom sticky-top">
      <div class="p-3 d-flex align-items-center gap-3">
        <button class="btn btn-link p-0 text-dark" @click="router.go(-1)">
          <ArrowLeft style="width:24px;height:24px" />
        </button>
        <h1 class="fs-5 fw-semibold mb-0">Créer un Utilisateur</h1>
      </div>
    </header>

    <div class="p-3 mx-auto" style="max-width:768px">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Informations de l'utilisateur</h5>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleSubmit" class="d-flex flex-column gap-3">

            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small fw-medium">Prénom *</label>
                <input v-model="formData.firstName" class="form-control" placeholder="Ex: Marie" required />
              </div>
              <div class="col-6">
                <label class="form-label small fw-medium">Nom *</label>
                <input v-model="formData.lastName" class="form-control" placeholder="Ex: Dubois" required />
              </div>
            </div>

            <div>
              <label class="form-label small fw-medium">Email *</label>
              <input v-model="formData.email" type="email" class="form-control" placeholder="exemple@email.com" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Mot de passe *</label>
              <input v-model="formData.password" type="password" class="form-control" placeholder="••••••••" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Téléphone *</label>
              <input v-model="formData.phone" type="tel" class="form-control" placeholder="+41 22 123 45 67" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Adresse *</label>
              <input v-model="formData.address" class="form-control" placeholder="Ex: 15 rue du Rhône, 1204 Genève" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Date de naissance *</label>
              <input v-model="formData.dateOfBirth" type="date" class="form-control" required />
            </div>

            <div>
              <label class="form-label small fw-medium">Rôle *</label>
              <select v-model="formData.role" class="form-select" required>
                <option value="volunteer">Bénévole</option>
                <option value="organizer">Organisateur</option>
                <option value="admin">Admin</option>
                <option value="superadmin">Super-admin</option>
              </select>
            </div>

            <!-- Allergies -->
            <div class="bg-light border rounded p-3 d-flex flex-column gap-2">
              <h6 class="fw-medium mb-0">Allergies</h6>
              <div class="d-flex gap-2">
                <input
                  v-model="newAllergy"
                  class="form-control"
                  placeholder="Ajouter une allergie"
                  @keydown.enter.prevent="addAllergy"
                />
                <button type="button" class="btn btn-primary btn-sm px-3" @click="addAllergy">
                  <Plus style="width:16px;height:16px" />
                </button>
              </div>
              <div v-if="allergies.length > 0" class="d-flex flex-wrap gap-2">
                <span
                  v-for="(allergy, i) in allergies" :key="i"
                  class="badge rounded-pill d-flex align-items-center gap-1 px-3 py-2"
                  style="background-color:#fee2e2;color:#b91c1c;font-weight:normal"
                >
                  {{ allergy }}
                  <button type="button" class="btn p-0 border-0 bg-transparent" style="color:#b91c1c" @click="removeAllergy(i)">
                    <X style="width:12px;height:12px" />
                  </button>
                </span>
              </div>
            </div>

            <!-- Problèmes de santé -->
            <div class="bg-light border rounded p-3 d-flex flex-column gap-2">
              <h6 class="fw-medium mb-0">Problèmes de santé</h6>
              <div class="d-flex gap-2">
                <input
                  v-model="newHealthIssue"
                  class="form-control"
                  placeholder="Ajouter un problème de santé"
                  @keydown.enter.prevent="addHealthIssue"
                />
                <button type="button" class="btn btn-primary btn-sm px-3" @click="addHealthIssue">
                  <Plus style="width:16px;height:16px" />
                </button>
              </div>
              <div v-if="healthIssues.length > 0" class="d-flex flex-wrap gap-2">
                <span
                  v-for="(issue, i) in healthIssues" :key="i"
                  class="badge rounded-pill d-flex align-items-center gap-1 px-3 py-2"
                  style="background-color:#ffedd5;color:#c2410c;font-weight:normal"
                >
                  {{ issue }}
                  <button type="button" class="btn p-0 border-0 bg-transparent" style="color:#c2410c" @click="removeHealthIssue(i)">
                    <X style="width:12px;height:12px" />
                  </button>
                </span>
              </div>
            </div>

            <div class="d-flex gap-3 pt-2">
              <button type="button" class="btn btn-outline-secondary flex-fill" @click="router.go(-1)">Annuler</button>
              <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2">
                <Save style="width:16px;height:16px" />
                Créer l'utilisateur
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Save, Plus, X } from 'lucide-vue-next'
import { getCurrentUser, isRole } from '@/utils/auth'

const router = useRouter()
const user = getCurrentUser()
if (!user || !isRole('superadmin')) router.push('/')

const formData = reactive({
  firstName: '', lastName: '', email: '', password: '',
  phone: '', address: '', dateOfBirth: '', role: 'volunteer',
})

const allergies = ref([])
const healthIssues = ref([])
const newAllergy = ref('')
const newHealthIssue = ref('')

const addAllergy = () => {
  if (newAllergy.value.trim()) { allergies.value.push(newAllergy.value.trim()); newAllergy.value = '' }
}
const removeAllergy = (i) => allergies.value.splice(i, 1)

const addHealthIssue = () => {
  if (newHealthIssue.value.trim()) { healthIssues.value.push(newHealthIssue.value.trim()); newHealthIssue.value = '' }
}
const removeHealthIssue = (i) => healthIssues.value.splice(i, 1)

const handleSubmit = () => {
  alert('Utilisateur créé avec succès !')
  router.push('/manage-users')
}
</script>