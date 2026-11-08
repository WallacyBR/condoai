import { vi } from 'vitest'

/** Spies compartilhados para asserções nos specs de integração. */
export const mockToastFns = {
  success: vi.fn(),
  error: vi.fn(),
  successToast: vi.fn(),
  errorToast: vi.fn(),
  warning: vi.fn(),
  info: vi.fn(),
  addToast: vi.fn(),
  removeToast: vi.fn(),
  clearAllToasts: vi.fn(),
  getToastClasses: vi.fn(),
  getToastIcon: vi.fn(),
}

export function useToast() {
  return {
    ...mockToastFns,
    toasts: { value: [] },
  }
}

export function resetMockToast() {
  Object.values(mockToastFns).forEach((fn) => {
    if (typeof fn?.mockClear === 'function') {
      fn.mockClear()
    }
  })
}
