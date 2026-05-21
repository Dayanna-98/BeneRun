const DATE_RE = /^(\d{4})-(\d{2})-(\d{2})$/
const TIME_RE = /^(\d{2}):(\d{2})/

export const normalizeDateInput = (value) => {
  const input = String(value || '').trim().slice(0, 10)
  return DATE_RE.test(input) ? input : ''
}

export const normalizeTimeInput = (value, fallback = '00:00') => {
  const input = String(value || '').trim()
  const match = input.match(TIME_RE)
  if (!match) return fallback

  const hours = Number(match[1])
  const minutes = Number(match[2])
  if (Number.isNaN(hours) || Number.isNaN(minutes)) return fallback
  if (hours < 0 || hours > 23 || minutes < 0 || minutes > 59) return fallback

  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`
}

export const parseLocalDateTime = (dateValue, timeValue = '00:00') => {
  const datePart = normalizeDateInput(dateValue)
  if (datePart) {
    const [year, month, day] = datePart.split('-').map(Number)
    const [hours, minutes] = normalizeTimeInput(timeValue, '00:00').split(':').map(Number)
    const parsed = new Date(year, month - 1, day, hours, minutes, 0, 0)
    return Number.isNaN(parsed.getTime()) ? null : parsed
  }

  const fallback = new Date(String(dateValue || '').trim())
  return Number.isNaN(fallback.getTime()) ? null : fallback
}
