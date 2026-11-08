import axios from 'axios';
import router from '../router';

// Lazy import do auth para evitar dependência circular
let auth = null;
const getAuth = async () => {
  if (!auth) {
    const authModule = await import('../services/auth');
    auth = authModule.default;
  }
  return auth;
};

export const authHeader = () => {
  const token = localStorage.getItem('token');
  if (token) {
    return { headers: { Authorization: `Bearer ${token}` } };
  } else {
    return {};
  }
};

export const api = axios.create({
  baseURL: '/api'
});

api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;

// Variável global para controlar se já estamos processando um erro 401
let isHandling401 = false;

export async function handleApiError(error) {
    if (error.status === 401 || error?.response?.status === 401) {
        // Se já estamos tratando um erro 401, evitar loop
        if (isHandling401) {
            console.log('Já está tratando erro 401, evitando loop...');
            return false;
        }
        
        isHandling401 = true;
        console.log('Erro 401 detectado, tentando refresh do token...');
        
        try {
            const authInstance = await getAuth();
            const refreshed = await authInstance.refreshToken();
            if (refreshed) {
                console.log('Token refreshado, retentando requisição...');
                return true;
            }
            
            console.log('Falha no refresh, fazendo logout...');
            await authInstance.logout();
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
    
    // Adicionar prefixo /api se não estiver presente
    const fullUrl = url.startsWith('/api') ? url : `/api${url}`;
    
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };

    if (token) {
        defaultHeaders['Authorization'] = `Bearer ${token}`;
    }

    try {
        const response = await fetch(fullUrl, {
            ...options,
            headers: {
                ...defaultHeaders,
                ...(options.headers || {})
            }
        });

        if (!response.ok) {
            if (response.status === 401) {
                console.log('Erro 401 na requisição, tentando refresh...');
                const refreshed = await handleApiError({ status: 401 });
                if (refreshed) {
                    // Retentar a requisição apenas uma vez após refresh
                    console.log('Retentando requisição após refresh...');
                    const newToken = authInstance.getToken();
                    if (newToken) {
                        defaultHeaders['Authorization'] = `Bearer ${newToken}`;
                    }
                    
                    const retryResponse = await fetch(fullUrl, {
                        ...options,
                        headers: {
                            ...defaultHeaders,
                            ...(options.headers || {})
                        }
                    });
                    
                    if (!retryResponse.ok) {
                        let errorMessage;
                        try {
                            const errorData = await retryResponse.json();
                            errorMessage = errorData.message || 'Erro na requisição';
                        } catch {
                            errorMessage = 'Erro na requisição';
                        }
                        throw new Error(errorMessage);
                    }
                    
                    return await retryResponse.json();
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
            
            throw new Error(errorMessage);
        }

        return await response.json();
    } catch (error) {
        if (error.message === 'Failed to fetch') {
            throw new Error('Erro de conexão com o servidor');
        }
        throw error;
    }
} 