<template>
  <div class="min-vh-100 pb-5" style="background:#f4f5f0">
    <!-- Header -->
    <header class="position-relative overflow-hidden text-white"
      style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%);padding:2.5rem 1.5rem 4rem">
      <div class="position-absolute top-0 end-0 opacity-25"
        style="width:320px;height:320px;background:radial-gradient(circle,#d4e645 0%,transparent 70%);transform:translate(30%,-30%)">
      </div>
      <div class="mx-auto position-relative" style="max-width:920px;z-index:1">
        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="bg-white rounded-3 p-2 shadow-sm">
            <img src="@/assets/logo.png" alt="Béné'Run" style="height:38px;width:auto" />
          </div>
          <div>
            <div class="small fw-semibold mb-1" style="color:#d4e645;letter-spacing:.06em;text-transform:uppercase;font-size:.7rem">
              Béné'Run
            </div>
            <h1 class="fw-bold mb-0" style="font-size:1.5rem;letter-spacing:-.02em">
              Conditions générales d'utilisation
            </h1>
          </div>
        </div>
        <p class="mb-3" style="color:rgba(255,255,255,.6);max-width:640px;line-height:1.7">
          Ce document explique les règles d'utilisation de la plateforme et la façon dont les données personnelles des bénévoles sont traitées, conformément au RGPD.
        </p>
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="badge rounded-pill px-3 py-2 fw-medium" style="background:#d4e645;color:#1a2230;font-size:.75rem">
            Version 2.1
          </span>
          <span class="badge rounded-pill px-3 py-2 fw-medium" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);font-size:.75rem">
            En vigueur depuis le 1er avril 2026
          </span>
        </div>
      </div>
    </header>

    <main class="px-3 mx-auto" style="max-width:920px;margin-top:-2rem">
      <div class="row g-4">
        <!-- Colonne principale -->
        <div class="col-12 col-lg-8 d-flex flex-column gap-3">

          <!-- Barre de progression lecture -->
          <div class="card border-0 shadow-sm" style="border-radius:14px">
            <div class="card-body py-3 px-4 d-flex align-items-center gap-3">
              <BookOpen style="width:18px;height:18px;color:#1a2230;flex-shrink:0" />
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between mb-1">
                  <small class="text-muted fw-medium">Progression de lecture</small>
                  <small class="fw-semibold" style="color:#1a2230">{{ readPercent }}%</small>
                </div>
                <div class="rounded-pill overflow-hidden" style="height:5px;background:#e9ecef">
                  <div class="rounded-pill h-100 transition"
                    style="background:linear-gradient(90deg,#d4e645,#a8be00);transition:width .3s"
                    :style="{ width: readPercent + '%' }"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sections -->
          <section v-for="section in sections" :key="section.id" :id="section.id"
            class="card border-0 shadow-sm" style="border-radius:16px;scroll-margin-top:1.5rem">
            <div class="card-body p-4">
              <div class="d-flex align-items-start gap-3 mb-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                  :style="{ background: section.color + '18', width: '40px', height: '40px' }">
                  <component :is="section.icon" :style="{ width: '20px', height: '20px', color: section.color }" />
                </div>
                <div>
                  <span class="badge rounded-pill mb-1 px-2 py-1"
                    :style="{ background: section.color + '18', color: section.color, fontSize: '.68rem', fontWeight: 600 }">
                    Article {{ section.num }}
                  </span>
                  <h2 class="h5 fw-bold mb-0" style="letter-spacing:-.01em">{{ section.title }}</h2>
                </div>
              </div>
              <component :is="section.component" />
            </div>
          </section>

        </div>

        <!-- Sidebar -->
        <aside class="col-12 col-lg-4">
          <div class="sticky-lg-top d-flex flex-column gap-3" style="top:1.5rem">

            <!-- Navigation -->
            <div class="card border-0 shadow-sm" style="border-radius:16px">
              <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <List style="width:16px;height:16px;color:#1a2230" />
                  <h3 class="h6 fw-bold mb-0" style="letter-spacing:-.01em">Sommaire</h3>
                </div>
                <nav class="d-flex flex-column gap-1">
                  <a v-for="section in sections" :key="section.id"
                    :href="'#' + section.id"
                    class="d-flex align-items-center gap-2 py-2 px-3 rounded-3 text-decoration-none small fw-medium"
                    :class="activeSection === section.id ? 'text-dark' : 'text-muted'"
                    :style="activeSection === section.id
                      ? { background: '#d4e64522', color: '#1a2230 !important' }
                      : { transition: 'background .15s' }"
                    @mouseover="e => e.currentTarget.style.background='#f8f9fa'"
                    @mouseleave="e => e.currentTarget.style.background = activeSection===section.id ? '#d4e64522' : ''"
                    @click.prevent="scrollTo(section.id)">
                    <span class="rounded-circle d-inline-block flex-shrink-0"
                      :style="{ width: '6px', height: '6px',
                        background: activeSection === section.id ? '#d4e645' : '#dee2e6' }">
                    </span>
                    {{ section.title }}
                  </a>
                </nav>
              </div>
            </div>

            <!-- Points clés -->
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius:16px">
              <div class="p-4" style="background:linear-gradient(135deg,#1a2230,#2d3a4a)">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <Lightbulb style="width:16px;height:16px;color:#d4e645" />
                  <strong class="text-white" style="font-size:.875rem">En résumé</strong>
                </div>
                <ul class="mb-0 ps-3 d-flex flex-column gap-2" style="color:rgba(255,255,255,.6);font-size:.8rem;line-height:1.6">
                  <li>Vos données servent uniquement à organiser le bénévolat.</li>
                  <li>Elles ne sont jamais vendues ni cédées à des tiers commerciaux.</li>
                  <li>La durée de conservation est étendue après la suspension ou la suppression du compte. Pour des raisons légales il nous ait nécessaires de conserver une trace de chaque participant et sur quels missions.</li>
                </ul>
              </div>
            </div>

            <!-- Contact -->
            <div class="card border-0 shadow-sm p-4" style="border-radius:16px">
              <div class="d-flex align-items-center gap-2 mb-2">
                <Mail style="width:16px;height:16px;color:#1a2230" />
                <strong style="font-size:.875rem">Exercer vos droits</strong>
              </div>
              <p class="text-muted small mb-3" style="line-height:1.6">
                Pour toute demande concernant vos données (accès, rectification), contactez le responsable via le canal officiel indiqué dans les mentions légales.
              </p>
              <button class="btn btn-sm w-100 fw-semibold" style="background:#d4e645;color:#1a2230;border:none;border-radius:10px;padding:.5rem 1rem">
                Nous contacter
              </button>
            </div>

            <button class="btn btn-outline-secondary w-100 fw-medium" style="border-radius:10px" @click="router.back()">
              ← Retour
            </button>
          </div>
        </aside>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, h } from 'vue'
import { useRouter } from 'vue-router'
import {
  FileText, Users, Database, ShieldCheck, Lock, Clock,
  BookOpen, List, Lightbulb, Mail, Globe, Share2, Cookie, RefreshCw, Building
} from 'lucide-vue-next'

const router = useRouter()
const readPercent = ref(0)
const activeSection = ref('')

// Composants de contenu des sections
const S1 = () => h('p', { class: 'text-muted mb-0', style: 'line-height:1.75' },
  'BeneRun est une plateforme dédiée à la gestion de l\'activité bénévole d\'associations sportives. Elle permet d\'administrer les comptes utilisateurs, les événements, les missions de bénévolat, les candidatures, les affectations, les compétences et les badges. L\'utilisation de la plateforme implique l\'acceptation pleine et entière des présentes CGU.')

const S2 = () => h('ul', { class: 'text-muted mb-0 ps-3 d-flex flex-column gap-2', style: 'line-height:1.75' }, [
  h('li', {}, 'L\'utilisateur s\'engage à fournir des informations exactes, complètes et à jour lors de son inscription et tout au long de son utilisation.'),
  h('li', {}, 'Le compte est strictement personnel et ne peut être partagé ou cédé à un tiers.'),
  h('li', {}, 'L\'utilisation est réservée à la participation aux activités bénévoles de l\'association.'),
  h('li', {}, 'Tout comportement abusif, frauduleux ou contraire au respect d\'autrui peut entraîner la suspension ou la suppression du compte sans préavis.'),
])

const S3 = () => h('div', {}, [
  h('p', { class: 'text-muted mb-3', style: 'line-height:1.75' },
    'Le responsable du traitement est l\'association gestionnaire de la plateforme BeneRun. Les données sont collectées sur la base légale du contrat (art. 6.1.b RGPD) et, le cas échéant, du consentement ou de l\'intérêt légitime de l\'association.'),
  h('div', { class: 'row g-2' }, [
    ['Identité et coordonnées', '#1a2230'],
    ['Adresse e-mail et authentification', '#2d5a27'],
    ['Compétences, badges et certificats', '#1a3a5a'],
    ['Candidatures, affectations et historique', '#5a2d1a'],
    ['Données de connexion (logs, sessions)', '#3a1a5a'],
    ['Photo de profil (si fournie)', '#1a4a4a'],
  ].map(([label, color]) =>
    h('div', { class: 'col-12 col-md-6' },
      h('div', {
        class: 'rounded-3 p-3 small fw-medium d-flex align-items-center gap-2',
        style: `background:${color}12;border:1px solid ${color}25;color:${color}`
      }, [
        h('span', { class: 'rounded-circle flex-shrink-0', style: `width:6px;height:6px;background:${color};display:inline-block` }),
        label
      ])
    )
  ))
])

const S4 = () => h('ul', { class: 'text-muted mb-0 ps-3 d-flex flex-column gap-2', style: 'line-height:1.75' }, [
  h('li', {}, 'Créer et administrer les comptes utilisateurs et leurs profils.'),
  h('li', {}, 'Gérer les missions, les événements et les affectations des bénévoles.'),
  h('li', {}, 'Permettre la communication entre l\'association, les bénévoles et les responsables.'),
  h('li', {}, 'Assurer le suivi interne des participations, compétences et validations.'),
  h('li', {}, 'Respecter les obligations légales, comptables et organisationnelles applicables.'),
  h('li', {}, 'Améliorer les fonctionnalités de la plateforme sur la base de statistiques d\'usage anonymisées.'),
])

const S5 = () => h('div', { class: 'd-flex flex-column gap-3' }, [
  h('p', { class: 'text-muted mb-0', style: 'line-height:1.75' },
    'Les données personnelles ne sont pas transmises à des tiers à des fins commerciales. Elles peuvent être partagées avec des prestataires techniques (hébergement, messagerie) dans le cadre strict de sous-traitance, liés à l\'association par des clauses de confidentialité conformes au RGPD.'),
  h('p', { class: 'text-muted mb-0', style: 'line-height:1.75' },
    'En cas de transfert hors Union européenne, les garanties appropriées sont mises en place (clauses contractuelles types ou équivalent).'),
])

const S6 = () => h('div', { class: 'd-flex flex-column gap-3 text-muted', style: 'line-height:1.75' }, [
  h('p', { class: 'mb-0' }, 'La plateforme peut utiliser le stockage local du navigateur (localStorage) pour maintenir la session de l\'utilisateur et mémoriser ses préférences d\'affichage. Ces données restent sur l\'appareil et ne sont pas transmises à des serveurs externes.'),
  h('p', { class: 'mb-0' }, 'Aucun cookie publicitaire ou tracker tiers n\'est utilisé sur la plateforme.'),
])

const S7 = () => h('div', { class: 'd-flex flex-column gap-3 text-muted', style: 'line-height:1.75' }, [
  h('p', { class: 'mb-0' }, 'Les données sont conservées pendant la durée nécessaire à la gestion du service et aux obligations légales applicables.'),
  h('p', { class: 'mb-0' }, 'Des mesures de sécurité techniques et organisationnelles appropriées sont mises en place pour protéger les données contre tout accès non autorisé, perte ou altération.'),
  h('p', { class: 'mb-0' }, 'L\'utilisateur dispose des droits d\'accès, de rectification, d\'effacement, de limitation, de portabilité et d\'opposition, qu\'il peut exercer auprès de l\'association.'),
])

const S8 = () => h('p', { class: 'text-muted mb-0', style: 'line-height:1.75' },
  'Les présentes CGU peuvent être modifiées pour refléter les évolutions légales ou fonctionnelles de la plateforme. Les utilisateurs sont informés de toute modification substantielle par notification sur la plateforme ou par e-mail. L\'utilisation du service après notification vaut acceptation des nouvelles conditions.')

const sections = [
  { id: 's1', num: 1, title: 'Objet de la plateforme',      icon: FileText,   color: '#1a2230', component: S1 },
  { id: 's2', num: 2, title: 'Utilisation du service',       icon: Users,      color: '#2d8a3e', component: S2 },
  { id: 's3', num: 3, title: 'Données collectées',           icon: Database,   color: '#1a5fa0', component: S3 },
  { id: 's4', num: 4, title: 'Finalités du traitement',      icon: ShieldCheck,color: '#6b3fa0', component: S4 },
  { id: 's5', num: 5, title: 'Partage des données',          icon: Share2,     color: '#c07020', component: S5 },
  { id: 's6', num: 6, title: 'Cookies et stockage local',    icon: Cookie,     color: '#1a7a7a', component: S6 },
  { id: 's7', num: 7, title: 'Sécurité, conservation et droits', icon: Lock,  color: '#a02d2d', component: S7 },
  { id: 's8', num: 8, title: 'Modifications des CGU',        icon: RefreshCw,  color: '#5a5a1a', component: S8 },
]

function scrollTo(id) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function onScroll() {
  const docH = document.documentElement.scrollHeight - window.innerHeight
  readPercent.value = docH > 0 ? Math.round((window.scrollY / docH) * 100) : 0

  for (const s of [...sections].reverse()) {
    const el = document.getElementById(s.id)
    if (el && el.getBoundingClientRect().top <= 120) {
      activeSection.value = s.id
      return
    }
  }
  activeSection.value = sections[0].id
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>