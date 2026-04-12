import { users } from '@/data/mockData'

const DEMO_USERS = users.filter(user => user.email === 'admin@benerun.ch')

export const ROLE_HIERARCHY = {
  volunteer: 1,
  organizer: 2,
  mission_manager: 2,
  admin: 3,
  superadmin: 4,
}

export const ROLE_ACCOUNT_TYPES = {
  volunteer: 'Bénévole',
  organizer: 'Responsable de mission',
  mission_manager: 'Responsable de mission',
  admin: 'Admin',
  superadmin: 'Super-admin',
}

export const DEFAULT_PERMISSIONS = {
  volunteer: {
    manageAccount: true,
    auth: true,
    resetPassword: true,
    deactivateAccount: true,
    editProfile: true,
    viewEvents: true,
    viewMissions: true,
    viewMissionDetails: true,
    enrollMission: true,
    cancelMission: true,
    favoriteMission: true,
    missionHistory: true,
    manageSkills: true,
    viewBadges: true,
    manageCertificates: true,
    missionMessaging: true,
    emergencyMessaging: true,
  },
  mission_manager: {
    manageAccount: true,
    auth: true,
    resetPassword: true,
    deactivateAccount: true,
    editProfile: true,
    viewEvents: true,
    viewMissions: true,
    viewMissionDetails: true,
    enrollMission: true,
    cancelMission: true,
    favoriteMission: true,
    missionHistory: true,
    manageSkills: true,
    viewBadges: true,
    manageCertificates: true,
    missionMessaging: true,
    emergencyMessaging: true,
    createMission: true,
    editMission: true,
    deleteMission: true,
    contactMissionMembers: true,
  },
  admin: {
    manageAccount: true,
    auth: true,
    resetPassword: true,
    deactivateAccount: true,
    editProfile: true,
    viewEvents: true,
    viewMissions: true,
    viewMissionDetails: true,
    enrollMission: true,
    cancelMission: true,
    favoriteMission: true,
    missionHistory: true,
    manageSkills: true,
    viewBadges: true,
    manageCertificates: true,
    missionMessaging: true,
    emergencyMessaging: true,
    createMission: true,
    editMission: true,
    deleteMission: true,
    contactMissionMembers: true,
    createEvent: true,
    editEvent: true,
    deleteEvent: true,
    assignMission: true,
  },
  superadmin: {
    manageAccount: true,
    auth: true,
    resetPassword: true,
    deactivateAccount: true,
    editProfile: true,
    viewEvents: true,
    viewMissions: true,
    viewMissionDetails: true,
    enrollMission: true,
    cancelMission: true,
    favoriteMission: true,
    missionHistory: true,
    manageSkills: true,
    viewBadges: true,
    manageCertificates: true,
    missionMessaging: true,
    emergencyMessaging: true,
    createMission: true,
    editMission: true,
    deleteMission: true,
    contactMissionMembers: true,
    createEvent: true,
    editEvent: true,
    deleteEvent: true,
    assignMission: true,
    disableAccount: true,
    reactivateAccount: true,
    createAccount: true,
    assignPermissions: true,
    enrollVolunteer: true,
    issueCertificate: true,
    createSkills: true,
    createBadges: true,
    exportStatistics: true,
  },
}

const normalizeUser = (user) => {
  if (!user) return null
  const role = user.role || 'volunteer'
  const accountType = user.accountType || ROLE_ACCOUNT_TYPES[role] || 'Bénévole'
  const permissions = {
    ...DEFAULT_PERMISSIONS[role],
    ...(user.permissions || {}),
  }
  return {
    ...user,
    role,
    accountType,
    permissions,
  }
}

export const setCurrentUser = (user) => {
  const normalized = normalizeUser(user)
  if (!normalized) return null
  localStorage.setItem('currentUser', JSON.stringify(normalized))
  localStorage.setItem('userEmail', normalized.email)
  return normalized
}

export const getCurrentUser = () => {
  const userJSON = localStorage.getItem('currentUser')
  if (userJSON) {
    try {
      return normalizeUser(JSON.parse(userJSON))
    } catch (e) {
      console.error('Erreur parsing currentUser:', e)
      localStorage.removeItem('currentUser')
    }
  }

  const userEmail = localStorage.getItem('userEmail')
  if (userEmail) {
    const fallbackUser = DEMO_USERS.find(u => u.email === userEmail)
    return normalizeUser(fallbackUser)
  }

  const token = localStorage.getItem('token')
  if (!token) return null
  const fallbackById = DEMO_USERS.find(u => String(u.id) === String(token))
  return normalizeUser(fallbackById)
}

export const login = (email, password) => {
  const user = DEMO_USERS.find(u => u.email === email && u.password === password)
  if (user) {
    localStorage.setItem('isLoggedIn', 'true')
    localStorage.setItem('token', String(user.id))
    return setCurrentUser(user)
  }
  return null
}

export const logout = () => {
  localStorage.removeItem('isLoggedIn')
  localStorage.removeItem('userEmail')
  localStorage.removeItem('token')
  localStorage.removeItem('currentUser')
}

export const hasPermission = (permission) => {
  const user = getCurrentUser()
  if (!user) return false
  return !!user.permissions[permission]
}

export const isRole = (role) => {
  const user = getCurrentUser()
  if (!user) return false
  if (Array.isArray(role)) return role.includes(user.role)
  return user.role === role
}

export const hasMinRole = (minRole) => {
  const user = getCurrentUser()
  if (!user) return false
  return (ROLE_HIERARCHY[user.role] ?? 0) >= (ROLE_HIERARCHY[minRole] ?? 0)
}
