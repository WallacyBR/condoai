import { apiRequest } from '../utils/api';
import router from '../router';

class AuthService {
    constructor() {
        this.token = localStorage.getItem('token');
        this.refreshTimeout = null;
        this.tokenExpirationTime = null;
        this.isRefreshing = false; // Flag para evitar múltiplos refreshes simultâneos
        
        if (this.token) {
            this.setupTokenRefresh();
        }
    }

    decodeToken(token) {
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
            return JSON.parse(jsonPayload);
        } catch (e) {
            console.error('Erro ao decodificar token:', e);
            return null;
        }
    }

    setupTokenRefresh() {
        if (this.refreshTimeout) {
            clearTimeout(this.refreshTimeout);
        }

        const decoded = this.decodeToken(this.token);
        if (!decoded || !decoded.exp) {
            console.warn('Token inválido ou sem expiração, removendo...');
            this.logout();
            return;
        }

        this.tokenExpirationTime = decoded.exp * 1000;
        const now = Date.now();
        const timeUntilExpiry = this.tokenExpirationTime - now;
        
        // Se o token já expirou, fazer refresh imediatamente
        if (timeUntilExpiry <= 0) {
            console.log('Token expirado, fazendo refresh imediato');
            this.refreshToken();
            return;
        }
        
        // Refresh 5 minutos antes da expiração
        const refreshTime = timeUntilExpiry - (5 * 60 * 1000);
        
        if (refreshTime > 0) {
            console.log(`Agendando refresh do token em ${Math.round(refreshTime / 1000 / 60)} minutos`);
            this.refreshTimeout = setTimeout(() => this.refreshToken(), refreshTime);
        } else {
            console.log('Token expira em menos de 5 minutos, fazendo refresh imediato');
            this.refreshToken();
        }
    }

    async login(credentials) {
        try {
            const response = await apiRequest('/api/auth/login', {
                method: 'POST',
                body: JSON.stringify(credentials)
            });

            if (response && response.authorization && response.authorization.token) {
                this.token = response.authorization.token;
                localStorage.setItem('token', this.token);
                this.setupTokenRefresh();
                return response;
            }
            throw new Error('Token não recebido no login');
        } catch (error) {
            throw error;
        }
    }

    async refreshToken() {
        // Evitar múltiplos refreshes simultâneos
        if (this.isRefreshing) {
            console.log('Refresh já em andamento, aguardando...');
            return false;
        }

        this.isRefreshing = true;
        console.log('Iniciando refresh do token...');

        try {
            const response = await apiRequest('/api/auth/refresh', {
                method: 'POST'
            });

            if (response && response.authorization && response.authorization.token) {
                this.token = response.authorization.token;
                localStorage.setItem('token', this.token);
                this.setupTokenRefresh();
                console.log('Token refreshado com sucesso');
                return true;
            }
            console.warn('Resposta inválida no refresh do token');
            return false;
        } catch (error) {
            console.error('Erro no refresh do token:', error);
            this.logout();
            return false;
        } finally {
            this.isRefreshing = false;
        }
    }

    async logout() {
        console.log('Fazendo logout...');
        try {
            await apiRequest('/api/auth/logout', {
                method: 'POST'
            });
        } catch (error) {
            console.error('Erro no logout:', error);
        } finally {
            if (this.refreshTimeout) {
                clearTimeout(this.refreshTimeout);
            }
            this.token = null;
            this.tokenExpirationTime = null;
            this.isRefreshing = false;
            localStorage.removeItem('token');
            router.push({ name: 'login' });
        }
    }

    isAuthenticated() {
        if (!this.token) return false;
        
        const decoded = this.decodeToken(this.token);
        if (!decoded || !decoded.exp) return false;
        
        const now = Date.now();
        return now < this.tokenExpirationTime;
    }

    getToken() {
        return this.token;
    }
}

export default new AuthService(); 