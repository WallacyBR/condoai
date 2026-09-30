import { describe, it, expect, beforeEach, vi } from 'vitest';
import {
  TENANT_HEADER,
  STORAGE_KEY_ID,
  applyTenantHeader,
  isExemptRoute,
  isTenantErrorResponse,
  handleTenantHttpError,
  resetTenantErrorHandlingForTests,
} from '../js/utils/tenant';

describe('tenant utils', () => {
  beforeEach(() => {
    localStorage.clear();
    resetTenantErrorHandlingForTests();
  });

  it('identifica rotas isentas de header de tenant', () => {
    expect(isExemptRoute('/auth/login', 'POST')).toBe(true);
    expect(isExemptRoute('/auth/register', 'POST')).toBe(true);
    expect(isExemptRoute('/auth/refresh', 'POST')).toBe(true);
    expect(isExemptRoute('/auth/logout', 'POST')).toBe(true);
    expect(isExemptRoute('/auth/me', 'GET')).toBe(true);
    expect(isExemptRoute('/health', 'GET')).toBe(true);
    expect(isExemptRoute('/qr/abc123', 'GET')).toBe(true);
    expect(isExemptRoute('/residents', 'GET')).toBe(false);
  });

  it('aplica X-Condominium-Id quando há condomínio selecionado', () => {
    localStorage.setItem(STORAGE_KEY_ID, '15');

    const headers = applyTenantHeader({}, '/residents', 'GET');

    expect(headers[TENANT_HEADER]).toBe('15');
  });

  it('não aplica header em rotas isentas', () => {
    localStorage.setItem(STORAGE_KEY_ID, '15');

    const headers = applyTenantHeader({}, '/auth/me', 'GET');

    expect(headers[TENANT_HEADER]).toBeUndefined();
  });

  it('não inventa header quando não há seleção', () => {
    const headers = applyTenantHeader({}, '/residents', 'GET');

    expect(headers[TENANT_HEADER]).toBeUndefined();
  });

  it('detecta erros de tenant por status e mensagem', () => {
    expect(isTenantErrorResponse(403, 'Acesso negado ao condomínio informado.', true)).toBe(true);
    expect(isTenantErrorResponse(400, 'Header X-Condominium-Id inválido.', true)).toBe(true);
    expect(isTenantErrorResponse(404, 'Condomínio não encontrado.', true)).toBe(true);
    expect(isTenantErrorResponse(404, 'Condomínio não encontrado.', false)).toBe(false);
    expect(isTenantErrorResponse(404, 'Recurso não encontrado.', true)).toBe(false);
  });

  it('trata erro de tenant com toast, clear e redirecionamento', async () => {
    localStorage.setItem(STORAGE_KEY_ID, '99');
    localStorage.setItem('selectedCondominiumName', 'Condomínio Teste');

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

    const { handleTenantHttpError: handleError } = await import('../js/utils/tenant');

    const handled = await handleError({
      status: 403,
      message: 'Acesso negado ao condomínio informado.',
      hadTenantHeader: true,
    });

    expect(handled).toBe(true);
    expect(toastError).toHaveBeenCalledWith('Acesso negado ao condomínio informado.');
    expect(clear).toHaveBeenCalled();
    expect(push).toHaveBeenCalledWith('/condominium-select');
  });
});
