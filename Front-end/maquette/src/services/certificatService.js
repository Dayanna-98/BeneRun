import api from './api'

const toIsoDate = (value) => {
  if (!value) return null
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return null
  return date.toISOString().slice(0, 10)
}

const mapStatusToFrontend = (status) => {
  if (!status) return 'pending'

  const normalized = String(status).toLowerCase().trim()
  if (normalized === 'pending' || normalized === 'en attente') return 'pending'
  if (normalized === 'approved' || normalized === 'approuvé' || normalized === 'approuve') return 'approved'
  if (normalized === 'rejected' || normalized === 'rejeté' || normalized === 'rejete') return 'rejected'
  return 'pending'
}

const mapStatusToApi = (status) => {
  if (!status) return 'en attente'

  const normalized = String(status).toLowerCase().trim()
  if (normalized === 'pending' || normalized === 'en attente') return 'en attente'
  if (normalized === 'approved' || normalized === 'approuvé' || normalized === 'approuve') return 'approuvé'
  if (normalized === 'rejected' || normalized === 'rejeté' || normalized === 'rejete') return 'rejeté'
  return 'en attente'
}

const mapApiCertificate = (cert) => ({
  id: String(cert.id_certificat ?? cert.id ?? ''),
  userId: String(cert.id_utilisateur ?? cert.id_benevole ?? ''),
  name: cert.titre_certificat || cert.name || 'Certificat',
  issuer: cert.emetteur_certificat || cert.issuer || 'BeneRun',
  issueDate: toIsoDate(cert.date_emission_certificat || cert.issueDate),
  expiryDate: toIsoDate(cert.date_expiration_certificat || cert.expiryDate),
  type: cert.type_certificat || cert.type || 'platform',
  status: mapStatusToFrontend(cert.statut_certificat || cert.status),
  filePath: cert.chemin_fichier_certificat || cert.filePath || null,
})

const toApiPayload = (cert = {}) => {
  const payload = {}

  if (cert.userId !== undefined || cert.id_utilisateur !== undefined) {
    payload.id_utilisateur = Number(cert.userId ?? cert.id_utilisateur)
  }
  if (cert.name !== undefined || cert.titre_certificat !== undefined) {
    payload.titre_certificat = cert.name ?? cert.titre_certificat
  }
  if (cert.issuer !== undefined || cert.emetteur_certificat !== undefined) {
    payload.emetteur_certificat = cert.issuer ?? cert.emetteur_certificat ?? null
  }
  if (cert.issueDate !== undefined || cert.date_emission_certificat !== undefined) {
    payload.date_emission_certificat = cert.issueDate ?? cert.date_emission_certificat ?? null
  }
  if (cert.expiryDate !== undefined || cert.date_expiration_certificat !== undefined) {
    payload.date_expiration_certificat = cert.expiryDate ?? cert.date_expiration_certificat ?? null
  }
  if (cert.type !== undefined || cert.type_certificat !== undefined) {
    payload.type_certificat = cert.type ?? cert.type_certificat ?? 'platform'
  }
  if (cert.status !== undefined || cert.statut_certificat !== undefined) {
    payload.statut_certificat = mapStatusToApi(cert.status ?? cert.statut_certificat)
  }
  if (cert.filePath !== undefined || cert.chemin_fichier_certificat !== undefined) {
    payload.chemin_fichier_certificat = cert.filePath ?? cert.chemin_fichier_certificat ?? null
  }

  return payload
}

const toFormDataPayload = (cert = {}) => {
  const payload = toApiPayload(cert)
  const formData = new FormData()

  Object.entries(payload).forEach(([key, value]) => {
    if (value === undefined) return
    formData.append(key, value ?? '')
  })

  if (cert.file instanceof File) {
    formData.append('file_certificat', cert.file)
  }

  return formData
}

const isMultipartPayload = (payload) => payload?.file instanceof File

const createRequest = (url, payload) => {
  if (isMultipartPayload(payload)) {
    return api.post(url, toFormDataPayload(payload), {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  }
  return api.post(url, toApiPayload(payload))
}

const updateRequest = (url, payload) => {
  if (isMultipartPayload(payload)) {
    return api.put(url, toFormDataPayload(payload), {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  }
  return api.put(url, toApiPayload(payload))
}

const certificatService = {
  getAll: () => api.get('/certificats').then(r => {
    if (!Array.isArray(r.data)) return []
    return r.data.map(mapApiCertificate)
  }),

  getByUserApi: (userId) => api.get('/certificats', {
    params: { id_utilisateur: Number(userId) },
  }).then(r => {
    if (!Array.isArray(r.data)) return []
    return r.data.map(mapApiCertificate)
  }),

  getByUser: async (userId) => certificatService.getByUserApi(userId),

  create: (payload) => createRequest('/certificats', payload).then(r => mapApiCertificate(r.data?.certificat ?? r.data)),

  update: (id, payload) => updateRequest(`/certificats/${id}`, payload).then(r => mapApiCertificate(r.data?.certificat ?? r.data)),

  delete: (id) => api.delete(`/certificats/${id}`).then(r => r.data),
}

export default certificatService