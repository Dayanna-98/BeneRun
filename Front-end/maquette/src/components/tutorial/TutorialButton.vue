<template>
  <button
    class="tutorial-btn"
    :class="{ 'tutorial-btn--pulse': pulse }"
    @click="openFullGuide"
    aria-label="Ouvrir le guide"
    title="Guide d'utilisation"
  >
    <HelpCircle style="width:16px;height:16px" />
    <span v-if="hasPendingProgress" class="tutorial-btn__badge" aria-hidden="true">●</span>
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { HelpCircle } from 'lucide-vue-next'
import { getCurrentUser } from '@/utils/auth'
import { useTutorial } from '@/composables/useTutorial'

const { openFullGuide } = useTutorial()

// Pulse une seule fois au 1er montage pour attirer l'attention
const pulse = ref(true)
const hasPendingProgress = ref(false)

const refreshPendingProgress = () => {
  const user = getCurrentUser()
  if (!user?.role) {
    hasPendingProgress.value = false
    return
  }

  const key = `benerun_tutorial_progress_${user.role}`
  const raw = localStorage.getItem(key)
  if (!raw) {
    hasPendingProgress.value = false
    return
  }

  try {
    const parsed = JSON.parse(raw)
    const completedIds = Array.isArray(parsed?.completedIds) ? parsed.completedIds : []
    hasPendingProgress.value = completedIds.length > 0
  } catch {
    hasPendingProgress.value = false
  }
}

onMounted(() => {
  setTimeout(() => { pulse.value = false }, 3000)
  refreshPendingProgress()
})
</script>

<style scoped>
.tutorial-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  background: rgba(197, 216, 46, 0.15);
  color: #7b8c10;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  flex-shrink: 0;
}
.tutorial-btn:hover {
  background: rgba(197, 216, 46, 0.3);
  color: #4a550a;
}
.tutorial-btn--pulse {
  animation: tutorial-pulse 1s ease-in-out 3;
}

.tutorial-btn__badge {
  position: absolute;
  top: -2px;
  right: -1px;
  color: #ef4444;
  font-size: 12px;
  line-height: 1;
}
@keyframes tutorial-pulse {
  0%   { box-shadow: 0 0 0 0 rgba(197, 216, 46, 0.5); }
  70%  { box-shadow: 0 0 0 8px rgba(197, 216, 46, 0); }
  100% { box-shadow: 0 0 0 0 rgba(197, 216, 46, 0); }
}
</style>
