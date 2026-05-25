<template>
  <button
    class="tutorial-btn"
    :class="{ 'tutorial-btn--pulse': pulse }"
    @click="openFullGuide"
    aria-label="Ouvrir le guide"
    title="Guide d'utilisation"
  >
    <HelpCircle style="width:16px;height:16px" />
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { HelpCircle } from 'lucide-vue-next'
import { useTutorial } from '@/composables/useTutorial'

const { openFullGuide } = useTutorial()

// Pulse une seule fois au 1er montage pour attirer l'attention
const pulse = ref(true)
onMounted(() => {
  setTimeout(() => { pulse.value = false }, 3000)
})
</script>

<style scoped>
.tutorial-btn {
  display: flex;
  align-items: center;
  justify-content: center;
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
@keyframes tutorial-pulse {
  0%   { box-shadow: 0 0 0 0 rgba(197, 216, 46, 0.5); }
  70%  { box-shadow: 0 0 0 8px rgba(197, 216, 46, 0); }
  100% { box-shadow: 0 0 0 0 rgba(197, 216, 46, 0); }
}
</style>
