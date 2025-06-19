import axios from 'axios';
import router from '../router';
import auth from '../services/auth';

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

export async function handleApiError(error) {
    if (error.status === 401 || error?.response?.status === 401) {
        const refreshed = await auth.refreshToken();
        if (refreshed) {
            return true;
        }
        
        await auth.logout();
        return false;
    }
    throw error;
}

export async function apiRequest(url, options = {}) {
    const token = auth.getToken();
    
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };

    if (token) {
        defaultHeaders['Authorization'] = `Bearer ${token}`;
    }

    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                ...defaultHeaders,
                ...(options.headers || {})
            }
        });

        if (!response.ok) {
            if (response.status === 401) {
                const refreshed = await handleApiError({ status: 401 });
                if (refreshed) {
                    return apiRequest(url, options);
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