<template>
  <Teleport to="body">
    <div v-if="open" class="modal d-block" tabindex="-1"
      style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

          <div class="modal-header border-0 pb-0">
            <div class="d-flex align-items-center gap-2">
              <AlertTriangle style="width:24px;height:24px;color:#d97706" />
              <h5 class="modal-title mb-0">Confirmer la désinscription</h5>
            </div>
          </div>

          <div class="modal-body d-flex flex-column gap-3">
            <p class="mb-0">
              Vous êtes sur le point de vous désinscrire de la mission
              <strong>{{ missionName }}</strong>.
            </p>

            <div class="rounded-3 p-3" style="background:#fffbeb;border:1px solid #fcd34d">
              <p class="fw-medium mb-2" style="color:#92400e">⚠️ Informations importantes</p>
              <ul class="mb-0 ps-3" style="color:#b45309">
                <li>Votre place sera libérée pour un autre bénévole</li>
                <li>Les responsables de la mission seront informés</li>
                <li>Cette action ne peut pas être annulée</li>
              </ul>
            </div>

            <p class="text-muted small mb-0">Êtes-vous sûr de vouloir continuer ?</p>
          </div>

          <div class="modal-footer border-0 pt-0">
            <button class="btn btn-outline-secondary" @click="$emit('update:open', false)">
              Annuler
            </button>
            <button class="btn text-white" style="background:#d97706"
              @click="confirm">
              Confirmer la désinscription
            </button>
          </div>

        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
  open:        { type: Boolean, required: true },
  missionName: { type: String,  required: true },
})

const emit = defineEmits(['update:open', 'confirm'])

const confirm = () => {
  emit('confirm')
  emit('update:open', false)
}
</script>