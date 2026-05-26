<template>
  <Teleport to="body">
    <Transition name="tutorial-fade">
      <div
        v-if="isGuideOpen"
        class="tutorial-backdrop"
        @click.self="closeGuide"
      >
        <div class="tutorial-modal" role="dialog" aria-modal="true" :aria-label="guideTitle">
          <div class="tutorial-header" :style="{ '--accent': guideColor }">
            <div class="tutorial-header__inner">
              <span class="tutorial-badge" :style="{ background: guideColor }">
                <component :is="currentStep.iconComponent" class="tutorial-badge__icon" />
              </span>
              <div>
                <p class="tutorial-header__label">{{ guideTitle }}</p>
                <p class="tutorial-header__sub">Étape {{ currentIndex + 1 }} / {{ guideSteps.length }} · {{ completedCount }}/{{ guideSteps.length }} validée(s)</p>
              </div>
            </div>
            <button class="tutorial-close" @click="closeGuide" aria-label="Fermer le guide">
              <X style="width:18px;height:18px" />
            </button>
          </div>

          <div class="tutorial-progress">
            <div
              class="tutorial-progress__fill"
              :style="{ width: progressPct + '%', background: guideColor }"
            />
          </div>

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

          <div class="tutorial-body">
            <Transition :name="slideDirection === 'forward' ? 'slide-left' : 'slide-right'" mode="out-in">
              <div :key="currentIndex" class="tutorial-step">
                <div v-if="currentStep.isNew" class="tutorial-new-badge">
                  Nouvelle fonctionnalité
                </div>
                <h2 class="tutorial-step__title">{{ currentStep.title }}</h2>
                <p class="tutorial-step__desc">{{ currentStep.description }}</p>

                <div class="tutorial-interactive">
                  <button
                    type="button"
                    class="tutorial-check"
                    :class="{ 'tutorial-check--done': isCurrentStepDone }"
                    :style="isCurrentStepDone ? { borderColor: guideColor, color: guideColor } : {}"
                    @click="toggleCurrentStepDone"
                  >
                    <CheckCircle2 style="width:15px;height:15px" />
                    {{ isCurrentStepDone ? 'Étape validée' : 'Valider cette étape' }}
                  </button>

                  <div class="tutorial-understanding">
                    <span class="tutorial-understanding__label">Ce point est-il clair ?</span>
                    <div class="tutorial-understanding__actions">
                      <button
                        type="button"
                        class="tutorial-understanding__btn"
                        :class="{ 'tutorial-understanding__btn--active': currentUnderstanding === 'clear' }"
                        @click="setUnderstanding('clear')"
                      >
                        Compris
                      </button>
                      <button
                        type="button"
                        class="tutorial-understanding__btn"
                        :class="{ 'tutorial-understanding__btn--active': currentUnderstanding === 'unclear' }"
                        @click="setUnderstanding('unclear')"
                      >
                        Pas clair
                      </button>
                    </div>
                  </div>

                  <div v-if="currentUnderstanding === 'unclear'" class="tutorial-help">
                    <p class="tutorial-help__title">Astuce pratique</p>
                    <p class="tutorial-help__text">{{ interactiveHint }}</p>
                  </div>
                </div>

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
              :disabled="completedCount < guideSteps.length"
              :style="{ background: guideColor }"
              @click="closeGuide"
            >
              {{ completedCount < guideSteps.length ? 'Validez les étapes restantes' : 'Terminer' }}
              <Check style="width:16px;height:16px" />
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import {
  X, ChevronLeft, ChevronRight, ArrowRight, Check, CheckCircle2,
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
const { isGuideOpen, guideSteps, guideTitle, guideColor, guideRole, closeGuide } = useTutorial()

const currentIndex = ref(0)
const slideDirection = ref('forward')
const completedStepIds = ref(new Set())
const understandingByStepId = ref({})

const progressStorageKey = computed(() => `benerun_tutorial_progress_${guideRole.value || 'volunteer'}`)

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

const completedCount = computed(() => {
  const ids = guideSteps.value.map((step) => step?.id).filter(Boolean)
  return ids.filter((id) => completedStepIds.value.has(id)).length
})

const isCurrentStepDone = computed(() => {
  const stepId = currentStep.value?.id
  return !!stepId && completedStepIds.value.has(stepId)
})

const currentUnderstanding = computed(() => understandingByStepId.value[currentStep.value?.id] || null)

const interactiveHint = computed(() => {
  if (currentStep.value?.route) {
    return `Clique sur « ${currentStep.value.cta || 'Ouvrir'} » puis teste directement la fonctionnalité pour te familiariser.`
  }

  return 'Prends 30 secondes pour reformuler cette étape avec tes mots avant de continuer. Cela aide à la mémorisation.'
})

function persistInteractiveProgress() {
  const payload = {
    completedIds: Array.from(completedStepIds.value),
    understandingByStepId: understandingByStepId.value,
    lastIndex: currentIndex.value,
    updatedAt: new Date().toISOString(),
  }

  localStorage.setItem(progressStorageKey.value, JSON.stringify(payload))
}

function loadInteractiveProgress() {
  const raw = localStorage.getItem(progressStorageKey.value)
  if (!raw) return

  try {
    const parsed = JSON.parse(raw)
    const savedIds = Array.isArray(parsed?.completedIds) ? parsed.completedIds.filter(Boolean) : []
    completedStepIds.value = new Set(savedIds)
    understandingByStepId.value = typeof parsed?.understandingByStepId === 'object' && parsed.understandingByStepId
      ? parsed.understandingByStepId
      : {}

    const savedIndex = Number(parsed?.lastIndex ?? 0)
    if (!Number.isNaN(savedIndex) && savedIndex >= 0 && savedIndex < guideSteps.value.length) {
      currentIndex.value = savedIndex
    }
  } catch {
    completedStepIds.value = new Set()
    understandingByStepId.value = {}
  }
}

function markStepDone(stepId) {
  if (!stepId) return
  const nextSet = new Set(completedStepIds.value)
  nextSet.add(stepId)
  completedStepIds.value = nextSet
  persistInteractiveProgress()
}

function toggleCurrentStepDone() {
  const stepId = currentStep.value?.id
  if (!stepId) return

  const nextSet = new Set(completedStepIds.value)
  if (nextSet.has(stepId)) {
    nextSet.delete(stepId)
  } else {
    nextSet.add(stepId)
  }

  completedStepIds.value = nextSet
  persistInteractiveProgress()
}

function setUnderstanding(level) {
  const stepId = currentStep.value?.id
  if (!stepId) return

  understandingByStepId.value = {
    ...understandingByStepId.value,
    [stepId]: level,
  }

  if (level === 'clear') {
    markStepDone(stepId)
  } else {
    persistInteractiveProgress()
  }
}

function next() {
  if (!isLast.value) {
    markStepDone(currentStep.value?.id)
    slideDirection.value = 'forward'
    currentIndex.value += 1
  }
}

function prev() {
  if (currentIndex.value > 0) {
    slideDirection.value = 'backward'
    currentIndex.value -= 1
  }
}

function goTo(index) {
  slideDirection.value = index > currentIndex.value ? 'forward' : 'backward'
  currentIndex.value = index
}

function onKeydown(event) {
  if (!isGuideOpen.value) return

  if (event.key === 'ArrowRight') next()
  if (event.key === 'ArrowLeft') prev()
  if (event.key === 'Escape') closeGuide()
}

function navigateTo(route) {
  markStepDone(currentStep.value?.id)
  closeGuide()
  router.push(route)
}

watch(isGuideOpen, (isOpen) => {
  if (isOpen) {
    currentIndex.value = 0
    loadInteractiveProgress()
    window.addEventListener('keydown', onKeydown)
    return
  }

  window.removeEventListener('keydown', onKeydown)
})

watch(currentIndex, () => {
  if (isGuideOpen.value) {
    persistInteractiveProgress()
  }
})

watch(() => guideRole.value, () => {
  completedStepIds.value = new Set()
  understandingByStepId.value = {}
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
})
</script>

<style scoped>
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

.tutorial-modal {
  background: #fff;
  border-radius: 1.25rem;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

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

.tutorial-progress {
  height: 3px;
  background: #f3f4f6;
}

.tutorial-progress__fill {
  height: 100%;
  transition: width 0.35s ease;
  border-radius: 0 2px 2px 0;
}

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

.tutorial-body {
  padding: 1.25rem 1.5rem 1rem;
  min-height: 245px;
  display: flex;
  align-items: flex-start;
  position: relative;
  overflow: hidden;
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

.tutorial-interactive {
  background: #f9fafb;
  border: 1px solid #eef0f3;
  border-radius: 12px;
  padding: 0.75rem;
  margin-bottom: 0.9rem;
}

.tutorial-check {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #d1d5db;
  background: #fff;
  color: #4b5563;
  border-radius: 999px;
  padding: 0.3rem 0.7rem;
  font-size: 0.74rem;
  font-weight: 600;
  margin-bottom: 0.6rem;
}

.tutorial-check--done {
  background: #f0fdf4;
  border-color: #86efac;
  color: #166534;
}

.tutorial-understanding {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  flex-wrap: wrap;
}

.tutorial-understanding__label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #4b5563;
}

.tutorial-understanding__actions {
  display: flex;
  gap: 6px;
}

.tutorial-understanding__btn {
  border: 1px solid #d1d5db;
  background: #fff;
  color: #4b5563;
  border-radius: 999px;
  padding: 0.25rem 0.6rem;
  font-size: 0.72rem;
  font-weight: 600;
}

.tutorial-understanding__btn--active {
  border-color: #c5d82e;
  background: rgba(197, 216, 46, 0.18);
  color: #4a550a;
}

.tutorial-help {
  margin-top: 0.6rem;
  padding: 0.55rem 0.6rem;
  border-left: 3px solid #f59e0b;
  background: #fff7ed;
  border-radius: 8px;
}

.tutorial-help__title {
  margin: 0 0 0.2rem;
  font-size: 0.72rem;
  font-weight: 700;
  color: #92400e;
}

.tutorial-help__text {
  margin: 0;
  font-size: 0.74rem;
  line-height: 1.35;
  color: #7c2d12;
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

.tutorial-nav--done:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

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
.slide-left-leave-to { opacity: 0; transform: translateX(-24px); }
.slide-right-enter-from { opacity: 0; transform: translateX(-24px); }
.slide-right-leave-to { opacity: 0; transform: translateX(24px); }
</style>
