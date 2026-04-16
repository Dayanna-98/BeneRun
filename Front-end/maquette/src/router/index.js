import { createRouter, createWebHistory } from 'vue-router'
import { getCurrentUser, isRole } from '@/utils/auth'

// Pages publiques
import Login from '@/views/Login.vue'
import Register from '@/views/Register.vue'
import ResetPassword from '@/views/ResetPassword.vue'

// Pages protégées
import Dashboard from '@/views/Dashboard.vue'
import Missions from '@/views/Missions.vue'
import Events from '@/views/Events.vue'
import EventsList from '@/views/EventsList.vue'
import EventDetails from '@/views/EventDetails.vue'
import MyMissions from '@/views/MyMissions.vue'
import Profile from '@/views/Profile.vue'
import EditProfile from '@/views/EditProfile.vue'
import MissionDetails from '@/views/MissionDetails.vue'
import Favorites from '@/views/Favorites.vue'
import ManageMissions from '@/views/ManageMissions.vue'
import CreateMission from '@/views/CreateMission.vue'
import EditMission from '@/views/EditMission.vue'
import ManageEvents from '@/views/ManageEvents.vue'
import CreateEvent from '@/views/CreateEvent.vue'
import EditEvent from '@/views/EditEvent.vue'
import ManageUsers from '@/views/ManageUsers.vue'
import CreateUser from '@/views/CreateUser.vue'
import EditUser from '@/views/EditUser.vue'
import Statistics from '@/views/Statistics.vue'
import ManageCompetences from '@/views/ManageCompetences.vue'
import ManageBadges from '@/views/ManageBadges.vue'
import ManageCertificates from '@/views/ManageCertificates.vue'

const routes = [
  // Routes publiques
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { path: '/reset-password', component: ResetPassword },

  // Routes protégées
  { path: '/', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/missions', component: Missions, meta: { requiresAuth: true } },
  { path: '/events', component: Events, meta: { requiresAuth: true } },
  { path: '/events-list', component: EventsList, meta: { requiresAuth: true } },
  { path: '/event/:id', component: EventDetails, meta: { requiresAuth: true } },
  { path: '/my-missions', component: MyMissions, meta: { requiresAuth: true } },
  { path: '/profile', component: Profile, meta: { requiresAuth: true } },
  { path: '/profile/edit', component: EditProfile, meta: { requiresAuth: true } },
  { path: '/mission/:id', component: MissionDetails, meta: { requiresAuth: true } },
  { path: '/favorites', component: Favorites, meta: { requiresAuth: true } },
  { path: '/manage-missions', component: ManageMissions, meta: { requiresAuth: true } },
  { path: '/manage-missions/create', component: CreateMission, meta: { requiresAuth: true } },
  { path: '/manage-missions/edit/:id', component: EditMission, meta: { requiresAuth: true } },
  { path: '/manage-events', component: ManageEvents, meta: { requiresAuth: true } },
  { path: '/manage-events/create', component: CreateEvent, meta: { requiresAuth: true } },
  { path: '/manage-events/edit/:id', component: EditEvent, meta: { requiresAuth: true } },
  { path: '/manage-users', component: ManageUsers, meta: { requiresAuth: true, requiredRole: 'superadmin' } },
  { path: '/manage-users/create', component: CreateUser, meta: { requiresAuth: true, requiredRole: 'superadmin' } },
  { path: '/manage-users/:id', component: EditUser, meta: { requiresAuth: true, requiredRole: 'superadmin' } },
  { path: '/manage-users/edit/:id', component: EditUser, meta: { requiresAuth: true, requiredRole: 'superadmin' } },
  { path: '/statistics', component: Statistics, meta: { requiresAuth: true } },
  { path: '/manage-competences', component: ManageCompetences, meta: { requiresAuth: true } },
  { path: '/manage-badges', component: ManageBadges, meta: { requiresAuth: true } },
  { path: '/manage-certificates', component: ManageCertificates, meta: { requiresAuth: true, requiredRole: 'superadmin' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

const cleanupBootstrapBackdrops = () => {
  if (typeof document === 'undefined') return

  document
    .querySelectorAll('.modal-backdrop, .offcanvas-backdrop')
    .forEach((element) => element.remove())

  document.body.classList.remove('modal-open')
  document.body.style.removeProperty('overflow')
  document.body.style.removeProperty('padding-right')
}

// Navigation guards
router.beforeEach((to, from) => {
  const token = localStorage.getItem('token')
  const user = getCurrentUser()
  const isLoggedIn = !!token && !!user

  // Si route protégée et pas connecté → login
  if (to.meta.requiresAuth && !isLoggedIn) {
    return '/login'
  }

  // Si route requiert un rôle spécifique et que l'utilisateur n'est pas autorisé → accueil
  if (to.meta.requiredRole && !isRole(to.meta.requiredRole)) {
    return '/'
  }

  // Si déjà sur login/register et connecté → dashboard
  if ((to.path === '/login' || to.path === '/register') && isLoggedIn) {
    return '/'
  }

  return true
})

router.afterEach(() => {
  cleanupBootstrapBackdrops()
})

export default router