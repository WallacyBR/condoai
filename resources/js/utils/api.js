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
        console.log('Erro 401 detectado, tentando refresh do token...');
        const refreshed = await auth.refreshToken();
        if (refreshed) {
            console.log('Token refreshado, retentando requisição...');
            return true;
        }
        
        console.log('Falha no refresh, fazendo logout...');
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
                console.log('Erro 401 na requisição, tentando refresh...');
                const refreshed = await handleApiError({ status: 401 });
                if (refreshed) {
                    // Retentar a requisição apenas uma vez após refresh
                    console.log('Retentando requisição após refresh...');
                    const newToken = auth.getToken();
                    if (newToken) {
                        defaultHeaders['Authorization'] = `Bearer ${newToken}`;
                    }
                    
                    const retryResponse = await fetch(url, {
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