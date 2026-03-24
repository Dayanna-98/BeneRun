import { users } from '@/data/mockData'

const rolePermissions = {
  volunteer: { location: true, messaging: true },
  organizer: { location: true, messaging: true, manageMissions: true },
  mission_manager: { location: true, messaging: true, manageMissions: true },
  admin: { location: true, messaging: true, manageMissions: true, manageEvents: true, assignMissions: true },
  superadmin: {
    location: true,
    messaging: true,
    manageMissions: true,
    manageEvents: true,
    assignMissions: true,
    manageAccounts: true,
    manageReferentials: true,
    exportStatistics: true,
  },
}

const roleAccountType = {
  volunteer: 'Benevole',
  organizer: 'Organisateur',
  mission_manager: 'Responsable de mission',
  admin: 'Admin',
  superadmin: 'Super-admin',
}

const buildStoredUser = (email) => {
  const role = localStorage.getItem('userRole') || 'volunteer'
  const firstName = localStorage.getItem('userFirstName') || 'Utilisateur'
  const lastName = localStorage.getItem('userLastName') || ''

  return {
    id: localStorage.getItem('token') || email,
    firstName,
    lastName,
    email,
    role,
    accountType: localStorage.getItem('userAccountType') || roleAccountType[role] || 'Benevole',
    permissions: rolePermissions[role] || rolePermissions.volunteer,
  }
}

export const saveBackendUserSession = (user) => {
  if (!user) return
  if (user.firstName) localStorage.setItem('userFirstName', user.firstName)
  if (user.lastName) localStorage.setItem('userLastName', user.lastName)
  if (user.role) localStorage.setItem('userRole', user.role)
  if (user.accountType) localStorage.setItem('userAccountType', user.accountType)
}

export const getCurrentUser = () => {
  const userEmail = localStorage.getItem('userEmail')
  if (!userEmail) return null

  const mockUser = users.find(u => u.email === userEmail)
  if (mockUser) return mockUser

  // Fallback backend user profile stored in localStorage.
  return buildStoredUser(userEmail)
}

export const login = (email, password) => {
  const user = users.find(u => u.email === email && u.password === password)
  if (user) {
    localStorage.setItem('isLoggedIn', 'true')
    localStorage.setItem('userEmail', user.email)
    localStorage.setItem('token', user.id)  // ✅ ajout du token pour le router guard
    return user
  }
  return null
}

export const logout = () => {
  localStorage.removeItem('isLoggedIn')
  localStorage.removeItem('userEmail')
  localStorage.removeItem('token')  // ✅ supprime aussi le token
  localStorage.removeItem('userRole')
  localStorage.removeItem('userFirstName')
  localStorage.removeItem('userLastName')
  localStorage.removeItem('userAccountType')
}

export const hasPermission = (permission) => {
  const user = getCurrentUser()
  if (!user) return false
  return user.permissions[permission] === true
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
  const roleHierarchy = {
    volunteer: 1,
    organizer: 2,
    mission_manager: 2,
    admin: 3,
    superadmin: 4,
  }
  return (roleHierarchy[user.role] ?? 0) >= (roleHierarchy[minRole] ?? 0)
}