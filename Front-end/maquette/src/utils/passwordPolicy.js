const PASSWORD_MIN_LENGTH = 10

const hasLetter = (value) => /[A-Za-z]/.test(value)
const hasNumber = (value) => /\d/.test(value)

export const PASSWORD_HINT = 'Minimum 10 caracteres, avec au moins une lettre et un chiffre.'

export const validatePasswordStrength = (password) => {
  const value = String(password || '')

  if (value.length < PASSWORD_MIN_LENGTH) {
    return 'Le mot de passe doit contenir au moins 10 caracteres.'
  }

  if (!hasLetter(value)) {
    return 'Le mot de passe doit contenir au moins une lettre.'
  }

  if (!hasNumber(value)) {
    return 'Le mot de passe doit contenir au moins un chiffre.'
  }

  return ''
}
