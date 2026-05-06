const COORDINATE_PATTERNS = [
  /[?&](?:q|query)=(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)/i,
  /@(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)(?:,|$)/,
  /!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/,
  /(?:^|\s)(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)(?:\s|$)/,
]

export const extractGoogleMapsCoordinates = (value) => {
  const input = String(value || '').trim()
  if (!input) return null

  for (const pattern of COORDINATE_PATTERNS) {
    const matches = input.match(pattern)
    if (!matches) continue

    const latitude = Number(matches[1])
    const longitude = Number(matches[2])

    if (
      Number.isNaN(latitude)
      || Number.isNaN(longitude)
      || latitude < -90
      || latitude > 90
      || longitude < -180
      || longitude > 180
    ) {
      continue
    }

    return { latitude, longitude }
  }

  return null
}

export const buildGoogleMapsLink = (mapsUrl, fallbackCoordinates = null) => {
  const input = String(mapsUrl || '').trim()
  if (input) return input

  if (!fallbackCoordinates) return ''

  return `https://www.google.com/maps?q=${fallbackCoordinates.latitude},${fallbackCoordinates.longitude}`
}

export const getMapZoomForRadius = (radiusMeters) => {
  const radius = Number(radiusMeters || 0)

  if (!radius || Number.isNaN(radius)) return 16
  if (radius <= 100) return 18
  if (radius <= 250) return 17
  if (radius <= 500) return 16
  if (radius <= 1000) return 15
  if (radius <= 2000) return 14
  if (radius <= 5000) return 13

  return 12
}

export const buildGoogleMapsEmbedUrl = (mapsUrl, { radiusMeters = null, fallbackCoordinates = null } = {}) => {
  const coordinates = extractGoogleMapsCoordinates(mapsUrl) || fallbackCoordinates
  if (!coordinates) return ''

  const zoom = getMapZoomForRadius(radiusMeters)
  return `https://www.google.com/maps?q=${coordinates.latitude},${coordinates.longitude}&z=${zoom}&output=embed`
}

export const formatRadius = (radiusMeters) => {
  const radius = Number(radiusMeters || 0)
  if (!radius || Number.isNaN(radius)) return ''
  if (radius >= 1000) return `${(radius / 1000).toFixed(radius % 1000 === 0 ? 0 : 1)} km`
  return `${radius} m`
}

export const distanceInMeters = (origin, target) => {
  if (!origin || !target) return null

  const earthRadius = 6371000
  const deltaLatitude = ((target.latitude - origin.latitude) * Math.PI) / 180
  const deltaLongitude = ((target.longitude - origin.longitude) * Math.PI) / 180
  const originLatitude = (origin.latitude * Math.PI) / 180
  const targetLatitude = (target.latitude * Math.PI) / 180

  const a = Math.sin(deltaLatitude / 2) ** 2
    + Math.cos(originLatitude) * Math.cos(targetLatitude) * Math.sin(deltaLongitude / 2) ** 2

  return earthRadius * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)))
}