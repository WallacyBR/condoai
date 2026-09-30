import { describe, it, expect, beforeEach, vi } from 'vitest';

vi.mock('../js/services/auth', () => ({
  default: {
    getToken: vi.fn(() => localStorage.getItem('token')),
    refreshToken: vi.fn().mockResolvedValue(false),
    logout: vi.fn().mockResolvedValue(undefined),
  },
}));

const handleTenantHttpErrorMock = vi.fn().mockResolvedValue(true);

vi.mock('../js/utils/tenant', async (importOriginal) => {
  const actual = await importOriginal();
  return {
    ...actual,
    handleTenantHttpError: (...args) => handleTenantHttpErrorMock(...args),
  };
});

import { api, apiRequest } from '../js/utils/api';
import { TENANT_HEADER, STORAGE_KEY_ID, resetTenantErrorHandlingForTests } from '../js/utils/tenant';

describe('api tenant integration', () => {
  beforeEach(() => {
    localStorage.clear();
    resetTenantErrorHandlingForTests();
    handleTenantHttpErrorMock.mockClear();
    vi.restoreAllMocks();
  });

  it('interceptor axios injeta X-Condominium-Id em requests autenticadas', () => {
    localStorage.setItem('token', 'fake-token');
    localStorage.setItem(STORAGE_KEY_ID, '42');

    const handlers = api.interceptors.request.handlers;
    const requestHandler = handlers[handlers.length - 1].fulfilled;
    const config = requestHandler({
      url: '/residents',
      method: 'get',
      headers: {},
    });

    expect(config.headers.Authorization).toBe('Bearer fake-token');
    expect(config.headers[TENANT_HEADER]).toBe('42');
  });

  it('interceptor axios não injeta header em rotas isentas', () => {
    localStorage.setItem('token', 'fake-token');
    localStorage.setItem(STORAGE_KEY_ID, '42');

    const handlers = api.interceptors.request.handlers;
    const requestHandler = handlers[handlers.length - 1].fulfilled;
    const config = requestHandler({
      url: '/auth/me',
      method: 'get',
      headers: {},
    });

    expect(config.headers[TENANT_HEADER]).toBeUndefined();
  });

  it('apiRequest aplica header de tenant nas requests autenticadas', async () => {
    localStorage.setItem('token', 'fake-token');
    localStorage.setItem(STORAGE_KEY_ID, '10');

    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({ success: true }),
    });
    global.fetch = fetchMock;

    await apiRequest('/residents');

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

  it('interceptor de resposta delega 403 de tenant ao handler centralizado', async () => {
    const handlers = api.interceptors.response.handlers;
    const responseHandler = handlers[handlers.length - 1].rejected;

    await expect(responseHandler({
      response: {
        status: 403,
        data: { message: 'Acesso negado ao condomínio informado.' },
      },
      config: {
        headers: { [TENANT_HEADER]: '5' },
      },
    })).rejects.toBeDefined();

    expect(handleTenantHttpErrorMock).toHaveBeenCalledWith({
      status: 403,
      message: 'Acesso negado ao condomínio informado.',
      hadTenantHeader: true,
    });
  });
});
