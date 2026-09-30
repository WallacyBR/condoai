export const TENANT_HEADER = 'X-Condominium-Id';
export const STORAGE_KEY_ID = 'selectedCondominium';
export const STORAGE_KEY_NAME = 'selectedCondominiumName';
export const CONDOMINIUM_SELECTED_EVENT = 'condominiumSelected';

export const TENANT_ERROR_MESSAGES = [
  'Header X-Condominium-Id é obrigatório.',
  'Header X-Condominium-Id inválido.',
  'Condomínio não encontrado.',
  'Acesso negado ao condomínio informado.',
];

let handlingTenantError = false;

export function getSelectedCondominiumId() {
  return localStorage.getItem(STORAGE_KEY_ID);
}

export function getSelectedCondominiumName() {
  return localStorage.getItem(STORAGE_KEY_NAME);
}

function normalizeRequestPath(url = '') {
  if (/^https?:\/\//.test(url)) {
    const parsed = new URL(url);
    return parsed.pathname.replace(/^\/api/, '') || '/';
  }

  const sanitizedUrl = url.startsWith('/') ? url : `/${url}`;
  return sanitizedUrl.startsWith('/api/') ? sanitizedUrl.slice(4) : sanitizedUrl;
}

export function isExemptRoute(url, method = 'GET') {
  const path = normalizeRequestPath(url || '');
  const upperMethod = (method || 'GET').toUpperCase();

  if (upperMethod === 'POST' && /^\/auth\/(login|register|refresh|logout)$/.test(path)) {
    return true;
  }

  if (upperMethod === 'GET' && (path === '/auth/me' || path === '/health' || /^\/qr\//.test(path))) {
    return true;
  }

  return false;
}

export function applyTenantHeader(headers = {}, url, method = 'GET') {
  if (isExemptRoute(url, method)) {
    return headers;
  }

  const selectedId = getSelectedCondominiumId();
  if (!selectedId) {
    return headers;
  }

  return {
    ...headers,
    [TENANT_HEADER]: String(selectedId),
  };
}

export function requestHadTenantHeader(headers = {}) {
  const headerValue = headers[TENANT_HEADER] ?? headers['x-condominium-id'];
  return headerValue !== undefined && headerValue !== null && headerValue !== '';
}

export function isTenantErrorMessage(message) {
  if (!message || typeof message !== 'string') {
    return false;
  }

  return TENANT_ERROR_MESSAGES.some((tenantMessage) => message.includes(tenantMessage));
}

export function isTenantErrorResponse(status, message, hadTenantHeader = false) {
  if (!isTenantErrorMessage(message)) {
    return false;
  }

  if (status === 400 || status === 403) {
    return true;
  }

  if (status === 404 && hadTenantHeader) {
    return true;
  }

  return false;
}

export async function handleTenantHttpError({ status, message, hadTenantHeader = false }) {
  if (!isTenantErrorResponse(status, message, hadTenantHeader)) {
    return false;
  }

  if (handlingTenantError) {
    return true;
  }

  handlingTenantError = true;

  try {
    const { useToast } = await import('../composables/useToast');
    const { useTenantStore } = await import('../stores/tenant');
    const routerModule = await import('../router/index.js');

    const toast = useToast();
    toast.error(message || 'Erro de acesso ao condomínio.');

    const tenantStore = useTenantStore();
    const shouldClear = hadTenantHeader || !!getSelectedCondominiumId();

    if (shouldClear) {
      tenantStore.clear();
    }

    const router = routerModule.default;
    if (router.currentRoute.value.path !== '/condominium-select') {
      await router.push('/condominium-select');
    }

    return true;
  } finally {
    handlingTenantError = false;
  }
}

export function resetTenantErrorHandlingForTests() {
  handlingTenantError = false;
}
