/**
 * Global Vitest setup:
 * - Provide a localStorage stub (jsdom already has one, but we reset it between tests)
 * - Stub window.matchMedia (used by some components)
 */
// Reset localStorage before every test so tests don't bleed into each other
beforeEach(() => {
  localStorage.clear()
})

// Stub matchMedia (not implemented in jsdom)
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: (query) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: () => {},
    removeListener: () => {},
    addEventListener: () => {},
    removeEventListener: () => {},
    dispatchEvent: () => false,
  }),
})
