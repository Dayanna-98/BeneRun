<template>
  <!-- Backdrop -->
  <Teleport to="body">
    <Transition name="tutorial-fade">
      <div
        v-if="isGuideOpen"
        class="tutorial-backdrop"
        @click.self="closeGuide"
      >
        <div class="tutorial-modal" role="dialog" aria-modal="true" :aria-label="guideTitle">
          <!-- Header -->
          <div class="tutorial-header" :style="{ '--accent': guideColor }">
            <div class="tutorial-header__inner">
              <span class="tutorial-badge" :style="{ background: guideColor }">
                <component :is="currentStep.iconComponent" class="tutorial-badge__icon" />
              </span>
              <div>
                <p class="tutorial-header__label">{{ guideTitle }}</p>
                <p class="tutorial-header__sub">Étape {{ currentIndex + 1 }} / {{ guideSteps.length }}</p>
              </div>
            </div>
            <button class="tutorial-close" @click="closeGuide" aria-label="Fermer le guide">
              <X style="width:18px;height:18px" />
            </button>
          </div>

          <!-- Step progress bar -->
          <div class="tutorial-progress">
            <div
              class="tutorial-progress__fill"
              :style="{ width: progressPct + '%', background: guideColor }"
            />
          </div>

          <!-- Step dots -->
          <div class="tutorial-dots">
            <button
              v-for="(_, i) in guideSteps"
              :key="i"
              class="tutorial-dot"
              :class="{ 'tutorial-dot--active': i === currentIndex }"
              :style="i === currentIndex ? { background: guideColor } : {}"
              @click="goTo(i)"
              :aria-label="`Étape ${i + 1}`"
            />
          </div>

          <!-- Body -->
          <div class="tutorial-body">
            <Transition :name="slideDirection === 'forward' ? 'slide-left' : 'slide-right'" mode="out-in">
              <div :key="currentIndex" class="tutorial-step">
                <div v-if="currentStep.isNew" class="tutorial-new-badge">
                  ✨ Nouvelle fonctionnalité
                </div>
                <h2 class="tutorial-step__title">{{ currentStep.title }}</h2>
                <p class="tutorial-step__desc">{{ currentStep.description }}</p>

                <button
                  v-if="currentStep.route && currentStep.cta"
                  class="tutorial-cta"
                  :style="{ background: guideColor }"
                  @click="navigateTo(currentStep.route)"
                >
                  {{ currentStep.cta }}
                  <ArrowRight style="width:14px;height:14px;margin-left:6px" />
                </button>
              </div>
            </Transition>
          </div>

          <!-- Footer navigation -->
          <div class="tutorial-footer">
            <button
              class="tutorial-nav tutorial-nav--prev"
              :disabled="currentIndex === 0"
              @click="prev"
            >
              <ChevronLeft style="width:16px;height:16px" />
              Précédent
            </button>

            <button
              v-if="!isLast"
              class="tutorial-nav tutorial-nav--next"
              :style="{ background: guideColor }"
              @click="next"
            >
              Suivant
              <ChevronRight style="width:16px;height:16px" />
            </button>

            <button
              v-else
              class="tutorial-nav tutorial-nav--done"
              :style="{ background: guideColor }"
              @click="closeGuide"
            >
              Terminer
              <Check style="width:16px;height:16px" />
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, shallowRef, watch } from 'vue'
import { useRouter } from 'vue-router'
import {
  X, ChevronLeft, ChevronRight, ArrowRight, Check,
  Heart, ListChecks, ClipboardCheck, CalendarDays, MessageCircle,
  Award, Bookmark, ShieldCheck, PlusCircle, Users, Send, AlertTriangle,
  Star, CalendarPlus, UserCog, Tag, Medal, BarChart3,
  Crown, UserPlus, KeyRound, FileText, Download,
} from 'lucide-vue-next'
import { useTutorial } from '@/composables/useTutorial'

const ICONS = {
  Heart, ListChecks, ClipboardCheck, CalendarDays, MessageCircle,
  Award, Bookmark, ShieldCheck, PlusCircle, Users, Send, AlertTriangle,
  Star, CalendarPlus, UserCog, Tag, Medal, BarChart3,
  Crown, UserPlus, KeyRound, FileText, Download,
}

const router = useRouter()
const { isGuideOpen, guideSteps, guideTitle, guideColor, isNewRoleGuide, closeGuide } = useTutorial()

const currentIndex = ref(0)
const slideDirection = ref('forward')

// Reset index quand le guide s'ouvre
watch(isGuideOpen, (val) => {
  if (val) currentIndex.value = 0
})

const currentStep = computed(() => {
  const step = guideSteps.value[currentIndex.value] || {}
  return {
    ...step,
    iconComponent: ICONS[step.icon] || Heart,
  }
})

const isLast = computed(() => currentIndex.value === guideSteps.value.length - 1)
const progressPct = computed(() =>
  guideSteps.value.length > 1
    ? (currentIndex.value / (guideSteps.value.length - 1)) * 100
    : 100
)

function next() {
  if (!isLast.value) {
    slideDirection.value = 'forward'
    currentIndex.value++
  }
}

function prev() {
  if (currentIndex.value > 0) {
    slideDirection.value = 'backward'
    currentIndex.value--
  }
}

function goTo(i) {
  slideDirection.value = i > currentIndex.value ? 'forward' : 'backward'
  currentIndex.value = i
}

function navigateTo(route) {
  closeGuide()
  router.push(route)
}
</script>

<style scoped>
/* Backdrop */
.tutorial-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

/* Modal */
.tutorial-modal {
  background: #fff;
  border-radius: 1.25rem;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Header */
.tutorial-header {
  padding: 1.25rem 1.25rem 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f0f0f0;
}
.tutorial-header__inner {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.tutorial-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 0.75rem;
  flex-shrink: 0;
}
.tutorial-badge__icon {
  width: 20px;
  height: 20px;
  color: #fff;
}
.tutorial-header__label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
}
.tutorial-header__sub {
  font-size: 0.7rem;
  color: #9ca3af;
  margin: 0;
}
.tutorial-close {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  transition: color 0.15s;
}
.tutorial-close:hover {
  color: #374151;
}

/* Progress */
.tutorial-progress {
  height: 3px;
  background: #f3f4f6;
}
.tutorial-progress__fill {
  height: 100%;
  transition: width 0.35s ease;
  border-radius: 0 2px 2px 0;
}

/* Dots */
.tutorial-dots {
  display: flex;
  gap: 6px;
  justify-content: center;
  padding: 0.75rem 0 0;
}
.tutorial-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #e5e7eb;
  border: none;
  cursor: pointer;
  padding: 0;
  transition: background 0.2s, transform 0.2s;
}
.tutorial-dot--active {
  transform: scale(1.35);
}

/* Body */
.tutorial-body {
  padding: 1.25rem 1.5rem 1rem;
  min-height: 180px;
  display: flex;
  align-items: flex-start;
}
.tutorial-step {
  width: 100%;
}
.tutorial-new-badge {
  display: inline-block;
  font-size: 0.7rem;
  font-weight: 600;
  color: #7c3aed;
  background: #ede9fe;
  border-radius: 999px;
  padding: 2px 10px;
  margin-bottom: 0.6rem;
}
.tutorial-step__title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
}
.tutorial-step__desc {
  font-size: 0.875rem;
  color: #6b7280;
  line-height: 1.55;
  margin-bottom: 1rem;
}
.tutorial-cta {
  display: inline-flex;
  align-items: center;
  border: none;
  border-radius: 999px;
  padding: 0.45rem 1rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  transition: opacity 0.15s;
}
.tutorial-cta:hover {
  opacity: 0.88;
}

/* Footer */
.tutorial-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem 1.5rem 1.25rem;
  border-top: 1px solid #f0f0f0;
  gap: 0.5rem;
}
.tutorial-nav {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  border: none;
  border-radius: 999px;
  padding: 0.45rem 1rem;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.15s;
}
.tutorial-nav--prev {
  background: #f3f4f6;
  color: #6b7280;
}
.tutorial-nav--prev:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.tutorial-nav--next,
.tutorial-nav--done {
  color: #fff;
  margin-left: auto;
}
.tutorial-nav--next:hover,
.tutorial-nav--done:hover {
  opacity: 0.88;
}

/* Animations */
.tutorial-fade-enter-active,
.tutorial-fade-leave-active {
  transition: opacity 0.25s, transform 0.25s;
}
.tutorial-fade-enter-from {
  opacity: 0;
  transform: scale(0.95);
}
.tutorial-fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition: opacity 0.2s, transform 0.2s;
  position: absolute;
  width: calc(100% - 3rem);
}
.slide-left-enter-from { opacity: 0; transform: translateX(24px); }
.slide-left-leave-to  { opacity: 0; transform: translateX(-24px); }
.slide-right-enter-from { opacity: 0; transform: translateX(-24px); }
.slide-right-leave-to  { opacity: 0; transform: translateX(24px); }
.tutorial-body { position: relative; overflow: hidden; }
</style>
