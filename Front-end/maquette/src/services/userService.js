import api from './api'

// Service pour les opérations utilisateurs
export const userService = {
  mapRoleToFrontend: (roleUtilisateur) => {
    const roleMap = {
      bénévole: 'volunteer',
      responsable: 'mission_manager',
      admin: 'admin',
      superadmin: 'superadmin',
    }
    return roleMap[roleUtilisateur] || 'volunteer'
  },

  mapRoleToApi: (role) => {
    const roleMap = {
      volunteer: 'bénévole',
      organizer: 'responsable',
      mission_manager: 'responsable',
      admin: 'admin',
      superadmin: 'superadmin',
      'bénévole': 'bénévole',
      responsable: 'responsable',
    }
    return roleMap[role] || 'bénévole'
  },

  toCsv: (value) => {
    if (Array.isArray(value)) {
      return value.filter(Boolean).map(v => String(v).trim()).filter(Boolean).join(', ')
    }
    if (value && typeof value === 'object') {
      const enabledPermissions = Object.entries(value)
        .filter(([, enabled]) => !!enabled)
        .map(([permission]) => permission)
      return enabledPermissions.length ? enabledPermissions.join(', ') : null
    }
    return value || null
  },

  fromCsv: (value) => {
    if (!value) return []
    return String(value)
      .split(',')
      .map(v => v.trim())
      .filter(Boolean)
  },

  mapApiUser: (apiUser) => {
    if (!apiUser) return null
    const role = userService.mapRoleToFrontend(apiUser.role_utilisateur)

    return {
      id: String(apiUser.id_utilisateur ?? apiUser.id ?? ''),
      firstName: apiUser.prenom_utilisateur || '',
      lastName: apiUser.nom_utilisateur || '',
      email: apiUser.email || '',
      phone: apiUser.telephone_utilisateur || '',
      address: apiUser.adresse_utilisateur || '',
      dateOfBirth: apiUser.date_naissance_utilisateur || '',
      role,
      accountType: role === 'superadmin'
        ? 'Super-admin'
        : role === 'admin'
          ? 'Admin'
          : role === 'mission_manager'
            ? 'Responsable de mission'
            : 'Bénévole',
      allergies: userService.fromCsv(apiUser.allergies_utilisateur),
      healthIssues: userService.fromCsv(apiUser.problemes_sante_utilisateur),
      hasLicense: !!apiUser.possede_permis_utilisateur,
      isMotorized: !!apiUser.est_motorise_utilisateur,
      hasVehicle: !!apiUser.possede_vehicule_utilisateur,
      bibSize: apiUser.taille_tshirt_utilisateur || '',
      anonymous: !!apiUser.est_anonyme_utilisateur,
      suspended: !!apiUser.est_suspendu_utilisateur,
      suspensionReason: apiUser.raison_suspension_utilisateur || '',
      missionCount: Number(apiUser.nombre_missions_utilisateur || 0),
      permissions: {},
    }
  },

  mergeUsers: (mockUsers = [], apiUsers = []) => {
    const normalizedMockUsers = mockUsers.map(user => ({ ...user, source: 'mock' }))
    const normalizedApiUsers = apiUsers.map(user => ({ ...user, source: 'database' }))
    const merged = [...normalizedApiUsers]

    normalizedMockUsers.forEach((mockUser) => {
      const exists = normalizedApiUsers.some(apiUser => apiUser.email === mockUser.email)
      if (!exists) {
        merged.push(mockUser)
      }
    })

    return merged
  },

  toPayload: (userData) => {
    const payload = {
      nom_utilisateur: userData.lastName,
      prenom_utilisateur: userData.firstName,
      email: userData.email,
      role_utilisateur: userService.mapRoleToApi(userData.role),
      telephone_utilisateur: userData.phone || null,
      adresse_utilisateur: userData.address || null,
      date_naissance_utilisateur: userData.dateOfBirth || null,
      allergies_utilisateur: userService.toCsv(userData.allergies),
      problemes_sante_utilisateur: userService.toCsv(userData.healthIssues),
      possede_permis_utilisateur: !!userData.hasLicense,
      est_motorise_utilisateur: !!userData.isMotorized,
      possede_vehicule_utilisateur: !!userData.hasVehicle,
      taille_tshirt_utilisateur: userData.bibSize || null,
      est_anonyme_utilisateur: !!userData.isAnonymous,
      est_suspendu_utilisateur: !!userData.isSuspended,
      raison_suspension_utilisateur: userData.suspensionReason || null,
      permissions_utilisateur: userService.toCsv(userData.permissions),
      nombre_missions_utilisateur: Number(userData.missionCount || 0),
    }

    if (userData.password) {
      payload.password = userData.password
    }

    return payload
  },

  formatApiError: (error, fallbackMessage) => {
    if (!error.response) {
      return {
        message: 'Serveur API inaccessible. Vérifiez que Laravel tourne sur le bon port (ex: http://localhost:8000).',
      }
    }
    return error.response.data || { message: fallbackMessage }
  },

  // Créer un nouvel utilisateur (register)
  register: async (userData) => {
    try {
      const response = await api.post('/users', userService.toPayload(userData))
      return {
        ...response.data,
        user: userService.mapApiUser(response.data?.user),
      }
    } catch (error) {
      throw userService.formatApiError(error, 'Erreur lors de l\'inscription')
    }
  },

  login: async (email, password) => {
    try {
      const response = await api.post('/login', { email, password })
      return {
        ...response.data,
        user: userService.mapApiUser(response.data?.user),
      }
    } catch (error) {
      throw userService.formatApiError(error, 'Email ou mot de passe incorrect')
    }
  },

  logout: async () => {
    try {
      const response = await api.post('/logout')
      return response.data
    } catch (error) {
      throw userService.formatApiError(error, 'Erreur lors de la deconnexion')
    }
  },

  // Créer un utilisateur (admin - CreateUser)
  create: async (userData) => {
    try {
      const response = await api.post('/users', userService.toPayload(userData))
      return {
        ...response.data,
        user: userService.mapApiUser(response.data?.user),
      }
    } catch (error) {
      throw userService.formatApiError(error, 'Erreur lors de la création de l\'utilisateur')
    }
  },

  // Récupérer tous les utilisateurs
  getAll: async () => {
    try {
      const response = await api.get('/users')
      if (Array.isArray(response.data)) {
        return response.data.map(userService.mapApiUser)
      }
      return response.data
    } catch (error) {
      throw userService.formatApiError(error, 'Erreur lors de la récupération des utilisateurs')
    }
  },

  // Récupérer un utilisateur par ID
  getById: async (id) => {
    try {
      const response = await api.get(`/users/${id}`)
      return userService.mapApiUser(response.data)
    } catch (error) {
      throw userService.formatApiError(error, 'Utilisateur non trouvé')
    }
  },

  // Mettre à jour un utilisateur
  update: async (id, userData) => {
    try {
      const response = await api.put(`/users/${id}`, userService.toPayload(userData))
      return {
        ...response.data,
        user: userService.mapApiUser(response.data?.user),
      }
    } catch (error) {
      throw userService.formatApiError(error, 'Erreur lors de la mise à jour')
    }
  },

  // Supprimer un utilisateur
  delete: async (id) => {
    try {
      const response = await api.delete(`/users/${id}`)
      return response.data
    } catch (error) {
      throw userService.formatApiError(error, 'Erreur lors de la suppression')
    }
  }
}

export default userService
