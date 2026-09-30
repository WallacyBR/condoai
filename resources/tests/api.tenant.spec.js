import { describe, it, expect, beforeEach, vi } from 'vitest';

vi.mock('../js/services/auth', () => ({
  default: {
    getToken: vi.fn(() => localStorage.getItem('token')),
    refreshToken: vi.fn().mockResolvedValue(false),
    logout: vi.fn().mockResolvedValue(undefined),
  },
}));

const handleTenantHttpErrorMock = vi.fn();

vi.mock('../js/utils/tenant', async (importOriginal) => {
  const actual = await importOriginal();
  return {
    ...actual,
    handleTenantHttpError: async (...args) => {
      handleTenantHttpErrorMock(...args);
      return actual.handleTenantHttpError(...args);
    },
  };
});

import { api, apiRequest } from '../js/utils/api';
import {
  TENANT_HEADER,
  STORAGE_KEY_ID,
  resetTenantErrorHandlingForTests,
  setCachedMembershipIds,
  markTenantHydrationPending,
  resolveTenantHydration,
} from '../js/utils/tenant';

describe('api tenant integration', () => {
  beforeEach(() => {
    localStorage.clear();
    resetTenantErrorHandlingForTests();
    handleTenantHttpErrorMock.mockClear();
    vi.restoreAllMocks();
  });

  const getRequestHandler = () => {
    const handlers = api.interceptors.request.handlers;
    return handlers[handlers.length - 1].fulfilled;
  };

  it('interceptor axios injeta X-Condominium-Id apenas para membership válida', async () => {
    localStorage.setItem('token', 'fake-token');
    localStorage.setItem(STORAGE_KEY_ID, '42');
    setCachedMembershipIds(['42']);

    const config = await getRequestHandler()({
      url: '/residents',
      method: 'get',
      headers: {},
    });

    expect(config.headers.Authorization).toBe('Bearer fake-token');
    expect(config.headers[TENANT_HEADER]).toBe('42');
  });

  it('interceptor axios não injeta header para id inválido após hydrate', async () => {
    localStorage.setItem('token', 'fake-token');
    localStorage.setItem(STORAGE_KEY_ID, '99');
    setCachedMembershipIds(['42']);

    const config = await getRequestHandler()({
      url: '/residents',
      method: 'get',
      headers: {},
    });

    expect(config.headers[TENANT_HEADER]).toBeUndefined();
  });

  it('interceptor axios envia header provisório do storage antes do hydrate', async () => {
    localStorage.setItem('token', 'fake-token');
    localStorage.setItem(STORAGE_KEY_ID, '42');

    const config = await getRequestHandler()({
      url: '/residents',
      method: 'get',
      headers: {},
    });

    expect(config.headers[TENANT_HEADER]).toBe('42');
  });

  it('interceptor axios aguarda hydrate antes de requests tenant no reload', async () => {
    localStorage.setItem('token', 'fake-token');
    localStorage.setItem(STORAGE_KEY_ID, '2');
    markTenantHydrationPending();

    const requestHandler = getRequestHandler();
    let resolved = false;

    const requestPromise = requestHandler({
      url: '/condominiums/2',
      method: 'get',
      headers: {},
    }).then((config) => {
      resolved = true;
      return config;
    });

    await Promise.race([
      requestPromise,
      new Promise((resolve) => setTimeout(resolve, 20)),
    ]);

    expect(resolved).toBe(false);

    setCachedMembershipIds(['1', '2']);

    const config = await requestPromise;

    expect(resolved).toBe(true);
    expect(config.headers[TENANT_HEADER]).toBe('2');
    expect(localStorage.getItem(STORAGE_KEY_ID)).toBe('2');
  });

  it('interceptor axios não injeta header em rotas isentas', async () => {
    localStorage.setItem('token', 'fake-token');
    localStorage.setItem(STORAGE_KEY_ID, '42');
    setCachedMembershipIds(['42']);

    const config = await getRequestHandler()({
      url: '/auth/me',
      method: 'get',
      headers: {},
    });

    expect(config.headers[TENANT_HEADER]).toBeUndefined();
  });

  it('apiRequest aplica header de tenant e ignora override inseguro', async () => {
    localStorage.setItem('token', 'fake-token');
    localStorage.setItem(STORAGE_KEY_ID, '10');
    setCachedMembershipIds(['10']);

    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({ success: true }),
    });
    global.fetch = fetchMock;

    await apiRequest('/residents', {
      headers: {
        [TENANT_HEADER]: '999',
      },
    });

    expect(fetchMock).toHaveBeenCalledWith(
      '/api/residents',
      expect.objectContaining({
        headers: expect.objectContaining({
          Authorization: 'Bearer fake-token',
          [TENANT_HEADER]: '10',
        }),
      }),
    );
  });

  it('400 pre-hydrate não limpa seleção legítima (race reload dashboard)', async () => {
    localStorage.setItem(STORAGE_KEY_ID, '2');
    markTenantHydrationPending();

    const handlers = api.interceptors.response.handlers;
    const responseHandler = handlers[handlers.length - 1].rejected;

    await expect(responseHandler({
      response: {
        status: 400,
        data: { message: 'Header X-Condominium-Id é obrigatório.' },
      },
      config: {
        headers: {},
      },
    })).rejects.toBeDefined();

    expect(handleTenantHttpErrorMock).toHaveBeenCalledWith({
      status: 400,
      message: 'Header X-Condominium-Id é obrigatório.',
      hadTenantHeader: false,
    });
    expect(localStorage.getItem(STORAGE_KEY_ID)).toBe('2');

    resolveTenantHydration();
  });

  it('interceptor de resposta delega 403 de tenant ao handler centralizado após hydrate', async () => {
    setCachedMembershipIds(['5']);

    const toastError = vi.fn();
    const clear = vi.fn();
    const push = vi.fn().mockResolvedValue(undefined);

    vi.doMock('../js/composables/useToast', () => ({
      useToast: () => ({ error: toastError }),
    }));

    vi.doMock('../js/stores/tenant', () => ({
      useTenantStore: () => ({ clear }),
    }));

    vi.doMock('../js/router/index.js', () => ({
      default: {
        currentRoute: { value: { path: '/dashboard' } },
        push,
      },
    }));

    const handlers = api.interceptors.response.handlers;
    const responseHandler = handlers[handlers.length - 1].rejected;

    await expect(responseHandler({
      response: {
        status: 403,
        data: { message: 'Qualquer mensagem' },
      },
      config: {
        headers: { [TENANT_HEADER]: '5' },
      },
    })).rejects.toBeDefined();

    expect(handleTenantHttpErrorMock).toHaveBeenCalledWith({
      status: 403,
      message: 'Qualquer mensagem',
      hadTenantHeader: true,
    });
  });
});
