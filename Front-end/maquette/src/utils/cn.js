/**
 * Combine des classes CSS conditionnellement.
 * Remplace cn() de Tailwind dans un contexte Bootstrap.
 *
 * Usage : cn('btn', isActive && 'btn-primary', size === 'sm' && 'btn-sm')
 */
export function cn(...classes) {
  return classes.filter(Boolean).join(' ')
}