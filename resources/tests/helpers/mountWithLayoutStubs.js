import { vi } from 'vitest';

export const layoutStubs = {
  AuthenticatedLayout: { template: '<div><slot /></div>' },
  TopCard: { template: '<div><slot /><slot name="actions" /></div>' },
  NeonButton: { template: '<button><slot /></button>' },
};

export const defaultRouterMock = {
  push: vi.fn(),
  replace: vi.fn(),
  back: vi.fn(),
};

export const defaultRouteMock = {
  path: '/',
  params: {},
  query: {},
  name: 'home',
};

/**
 * Opções globais reutilizáveis para montar componentes que dependem de layout e router.
 *
 * @param {object} [overrides]
 * @returns {object}
 */
export function mountOptionsWithLayoutStubs(overrides = {}) {
  const { global: globalOverrides = {}, ...rest } = overrides;

  return {
    ...rest,
    global: {
      ...globalOverrides,
      stubs: {
        ...layoutStubs,
        ...(globalOverrides.stubs || {}),
      },
      mocks: {
        $route: defaultRouteMock,
        $router: defaultRouterMock,
        ...(globalOverrides.mocks || {}),
      },
    },
  };
}
