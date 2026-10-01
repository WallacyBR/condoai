import axios from 'axios';
import router from '../router/index.js';
import {
  areMembershipsKnown,
  finalizeTenantHeaders,
  handleTenantHttpError,
  isExemptRoute,
  requestHadTenantHeader,
  waitForTenantHydration,
} from './tenant';

let auth = null;
const getAuth = async () => {
  if (!auth) {
    const authModule = await import('../services/auth');
    auth = authModule.default?.default || authModule.default || authModule;
  }
  return auth;
};

export const normalizeApiUrl = (url = '') => {
  if (/^https?:\/\//.test(url)) {
    return url;
  }

  const sanitizedUrl = url.startsWith('/') ? url : `/${url}`;
  return sanitizedUrl.startsWith('/api/') ? sanitizedUrl.slice(4) : sanitizedUrl;
};

export const authHeader = () => {
  const token = localStorage.getItem('token');
  if (token) {
    return { headers: { Authorization: `Bearer ${token}` } };
  }

  return {};
};

export const api = axios.create({
  baseURL: '/api'
});

api.interceptors.request.use(async (config) => {
  const token = localStorage.getItem('token');

  config.url = normalizeApiUrl(config.url);

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  const method = config.method || 'GET';

  if (token && !isExemptRoute(config.url, method) && !areMembershipsKnown()) {
    await waitForTenantHydration();
  }

  config.headers = finalizeTenantHeaders(config.headers || {}, config.url, method);

  return config;
});

api.interceptors.response.use(
  response => response,
  async error => {
    const status = error?.response?.status;
    const message = error?.response?.data?.message;
    const hadTenantHeader = requestHadTenantHeader(error.config?.headers || {});

    if (status && status !== 401) {
      const handled = await handleTenantHttpError({ status, message, hadTenantHeader });
      if (handled) {
        return Promise.reject(error);
      }
    }

    if (error?.response?.status !== 401 || error.config?._retry) {
      return Promise.reject(error);
    }

    try {
      const refreshed = await handleApiError(error);

      if (!refreshed) {
        return Promise.reject(error);
      }

      const authInstance = await getAuth();
      const newToken = authInstance.getToken();
      const retryConfig = {
        ...error.config,
        _retry: true,
        headers: {
          ...(error.config?.headers || {}),
        },
      };

      if (newToken) {
        retryConfig.headers.Authorization = `Bearer ${newToken}`;
      }

      retryConfig.headers = finalizeTenantHeaders(
        retryConfig.headers,
        retryConfig.url,
        retryConfig.method || 'GET'
      );

      return api.request(retryConfig);
    } catch (refreshError) {
      return Promise.reject(refreshError);
    }
  }
);

export default api;

// Variável global para controlar se já estamos processando um erro 401
let isHandling401 = false;

export async function handleApiError(error) {
  if (error.status === 401 || error?.response?.status === 401) {
    // Se já estamos tratando um erro 401, evitar loop
    if (isHandling401) {
      return false;
    }

    isHandling401 = true;

    try {
      const authInstance = await getAuth();
      const refreshed = await authInstance.refreshToken();
      if (refreshed) {
        return true;
      }

      await authInstance.logout();
      await router.push({ name: 'login' });
      return false;
    } finally {
      isHandling401 = false;
    }
  }
  throw error;
}

export async function apiRequest(url, options = {}) {
  const authInstance = await getAuth();
  const token = authInstance.getToken();

  const normalizedUrl = normalizeApiUrl(url);
  const fullUrl = normalizedUrl.startsWith('/api') ? normalizedUrl : `/api${normalizedUrl}`;
  const method = options.method || 'GET';

  let defaultHeaders = {
    Accept: 'application/json'
  };

  const isFormData = typeof FormData !== 'undefined' && options.body instanceof FormData;

  if (!isFormData) {
    defaultHeaders['Content-Type'] = 'application/json';
  }

  if (token) {
    defaultHeaders.Authorization = `Bearer ${token}`;
  }

  const buildHeaders = (overrideHeaders = {}) => finalizeTenantHeaders(
    {
      ...defaultHeaders,
      ...(overrideHeaders || {}),
    },
    normalizedUrl,
    method,
  );

  if (token && !isExemptRoute(normalizedUrl, method) && !areMembershipsKnown()) {
    await waitForTenantHydration();
  }

  const parseResponse = async (response) => {
    if (response.status === 204) {
      return null;
    }
    if (typeof response.text === 'function') {
      const text = await response.text();
      return text ? JSON.parse(text) : null;
    }
    if (typeof response.json === 'function') {
      return await response.json();
    }
    return null;
  };

  const executeRequest = async (headers) => {
    return await fetch(fullUrl, {
      ...options,
      headers,
    });
  };

  try {
    let requestHeaders = buildHeaders(options.headers);
    let response = await executeRequest(requestHeaders);

    if (!response.ok) {
      if (response.status === 401) {
        const refreshed = await handleApiError({ status: 401 });
        if (refreshed) {
          const newToken = authInstance.getToken();
          if (newToken) {
            defaultHeaders.Authorization = `Bearer ${newToken}`;
          }

          requestHeaders = buildHeaders(options.headers);

          response = await executeRequest(requestHeaders);

          if (!response.ok) {
            let errorMessage;
            try {
              const errorData = await response.json();
              errorMessage = errorData.message || 'Erro na requisição';
            } catch {
              errorMessage = 'Erro na requisição';
            }

            const handled = await handleTenantHttpError({
              status: response.status,
              message: errorMessage,
              hadTenantHeader: requestHadTenantHeader(requestHeaders),
            });

            if (handled) {
              throw new Error(errorMessage);
            }

            throw new Error(errorMessage);
          }

          return await parseResponse(response);
        }
        return null;
      }

      let errorMessage;
      try {
        const errorData = await response.json();
        errorMessage = errorData.message || 'Erro na requisição';
      } catch {
        errorMessage = 'Erro na requisição';
      }

      const handled = await handleTenantHttpError({
        status: response.status,
        message: errorMessage,
        hadTenantHeader: requestHadTenantHeader(requestHeaders),
      });

      if (handled) {
        throw new Error(errorMessage);
      }

      throw new Error(errorMessage);
    }

    return await parseResponse(response);
  } catch (error) {
    if (error.message === 'Failed to fetch') {
      throw new Error('Erro de conexão com o servidor');
    }
    throw error;
  }
}
