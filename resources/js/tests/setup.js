import { vi } from 'vitest'

// Mock global do fs para evitar erros
global.fs = {
  readFileSync: vi.fn(() => 'mock content'),
}

// Configurar canvas para jsdom
import { createCanvas } from 'canvas'

// Mock do HTMLCanvasElement
Object.defineProperty(global, 'HTMLCanvasElement', {
  value: class HTMLCanvasElement {
    constructor() {
      this.canvas = createCanvas(400, 200)
    }
    
    getContext(type) {
      if (type === '2d') {
        return this.canvas.getContext('2d')
      }
      return null
    }
    
    get width() { return this.canvas.width }
    set width(value) { this.canvas.width = value }
    
    get height() { return this.canvas.height }
    set height(value) { this.canvas.height = value }
    
    getBoundingClientRect() {
      return {
        width: this.canvas.width,
        height: this.canvas.height,
        top: 0,
        left: 0,
        right: this.canvas.width,
        bottom: this.canvas.height
      }
    }
  }
})

// Mock do window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(), // deprecated
    removeListener: vi.fn(), // deprecated
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
})

// Mock do ResizeObserver
global.ResizeObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}))

// Mock do IntersectionObserver
global.IntersectionObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}))

// Mock do console para reduzir ruído nos testes
global.console = {
  ...console,
  warn: vi.fn(),
  error: vi.fn(),
} 