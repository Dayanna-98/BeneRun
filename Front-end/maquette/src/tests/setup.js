/**
 * Global Vitest setup:
 * - Provide a localStorage stub (jsdom already has one, but we reset it between tests)
 * - Stub window.matchMedia (used by some components)
 */
import { vi, beforeEach } from 'vitest'

// Reset localStorage before every test so tests don't bleed into each other
beforeEach(() => {
  localStorage.clear()
})

// Stub matchMedia (not implemented in jsdom)
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation((query) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
})
