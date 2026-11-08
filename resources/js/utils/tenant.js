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
let invalidSelectionRecoveryScheduled = false;
let cachedMembershipIds = null;
let membershipsKnown = false;
let tenantHydrationPromise = null;
let tenantHydrationResolve = null;

export function getSelectedCondominiumId() {
  return localStorage.getItem(STORAGE_KEY_ID);
}

export function getSelectedCondominiumName() {
  return localStorage.getItem(STORAGE_KEY_NAME);
}

export function areMembershipsKnown() {
  return membershipsKnown;
}

export function setCachedMembershipIds(ids) {
  cachedMembershipIds = Array.isArray(ids) ? ids.map(String) : [];
  membershipsKnown = true;
  resolveTenantHydration();
}

export function resetMembershipCache() {
  cachedMembershipIds = null;
  membershipsKnown = false;
}

export function isPlausibleStoredTenantId(id) {
  if (id === null || id === undefined || id === '') {
    return false;
  }

  const normalized = String(id).trim();
  const numeric = Number(normalized);

  return Number.isInteger(numeric) && numeric > 0 && String(numeric) === normalized;
}

export function markTenantHydrationPending() {
  if (!tenantHydrationPromise) {
    tenantHydrationPromise = new Promise((resolve) => {
      tenantHydrationResolve = resolve;
    });
  }
}

export function resolveTenantHydration() {
  if (tenantHydrationResolve) {
    tenantHydrationResolve();
  }

  tenantHydrationPromise = null;
  tenantHydrationResolve = null;
}

export function waitForTenantHydration() {
  if (membershipsKnown) {
    return Promise.resolve();
  }

  if (tenantHydrationPromise) {
    return tenantHydrationPromise;
  }

  return Promise.resolve();
}

export function getMembershipIds() {
  if (!membershipsKnown) {
    return [];
  }

  return cachedMembershipIds ?? [];
}

export function isValidMembershipId(id) {
  if (id === null || id === undefined || id === '') {
    return false;
  }

  if (!membershipsKnown) {
    return false;
  }

  return getMembershipIds().includes(String(id));
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

function stripTenantHeaderKeys(headers = {}) {
  const sanitized = { ...headers };
  delete sanitized[TENANT_HEADER];
  delete sanitized['x-condominium-id'];
  return sanitized;
}

export function scheduleInvalidSelectionRecovery() {
  if (invalidSelectionRecoveryScheduled) {
    return;
  }

  invalidSelectionRecoveryScheduled = true;

  queueMicrotask(async () => {
    invalidSelectionRecoveryScheduled = false;

    try {
      const { useTenantStore } = await import('../stores/tenant');
      const routerModule = await import('../router/index.js');
      const tenantStore = useTenantStore();

      tenantStore.clear();

      const router = routerModule.default;
      if (router.currentRoute.value.path !== '/condominium-select') {
        await router.push('/condominium-select');
      }
    } catch {
      localStorage.removeItem(STORAGE_KEY_ID);
      localStorage.removeItem(STORAGE_KEY_NAME);
    }
  });
}

export function resolveTenantHeaderId() {
  const selectedId = getSelectedCondominiumId();
  if (!selectedId || !isPlausibleStoredTenantId(selectedId)) {
    return null;
  }

  if (!membershipsKnown) {
    return String(selectedId);
  }

  if (!isValidMembershipId(selectedId)) {
    scheduleInvalidSelectionRecovery();
    return null;
  }

  return String(selectedId);
}

export function finalizeTenantHeaders(headers = {}, url, method = 'GET') {
  const merged = stripTenantHeaderKeys(headers);

  if (isExemptRoute(url, method)) {
    return merged;
  }

  const tenantId = resolveTenantHeaderId();
  if (tenantId) {
    merged[TENANT_HEADER] = tenantId;
  }

  return merged;
}

export function applyTenantHeader(headers = {}, url, method = 'GET') {
  return finalizeTenantHeaders(headers, url, method);
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

export function isTenantHeaderMessage(message) {
  if (!message || typeof message !== 'string') {
    return false;
  }

  return /X-Condominium-Id/i.test(message);
}

export function isCondominiumNotFoundHeuristic(message) {
  if (!message || typeof message !== 'string') {
    return false;
  }

  return /condom[ií]nio/i.test(message);
}

export function isTenantErrorResponse(status, message, hadTenantHeader = false) {
  if (!membershipsKnown) {
    return false;
  }

  if (status === 403 && hadTenantHeader) {
    return true;
  }

  if (status === 400) {
    if (hadTenantHeader) {
      return true;
    }

    if (isTenantHeaderMessage(message) || isTenantErrorMessage(message)) {
      return true;
    }
  }

  if (status === 404 && hadTenantHeader) {
    return isTenantErrorMessage(message) || isCondominiumNotFoundHeuristic(message);
  }

  return false;
}

export async function handleTenantHttpError({ status, message, hadTenantHeader = false }) {
  if (!membershipsKnown) {
    return false;
  }

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
  invalidSelectionRecoveryScheduled = false;
  resetMembershipCache();
  resolveTenantHydration();
}
