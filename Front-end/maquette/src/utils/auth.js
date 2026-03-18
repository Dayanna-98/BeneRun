import { users } from '@/data/mockData'

export const getCurrentUser = () => {
  const userEmail = localStorage.getItem('userEmail')
  if (!userEmail) return null
  return users.find(u => u.email === userEmail) || null
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