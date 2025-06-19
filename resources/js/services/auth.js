import { apiRequest } from '../utils/api';
import router from '../router';

class AuthService {
    constructor() {
        this.token = localStorage.getItem('token');
        this.refreshTimeout = null;
        this.tokenExpirationTime = null;
        
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
            return null;
        }
    }

    setupTokenRefresh() {
        if (this.refreshTimeout) {
            clearTimeout(this.refreshTimeout);
        }

        const decoded = this.decodeToken(this.token);
        if (!decoded || !decoded.exp) {
            return;
        }

        this.tokenExpirationTime = decoded.exp * 1000;
        const now = Date.now();
        const timeUntilExpiry = this.tokenExpirationTime - now;
        const refreshTime = timeUntilExpiry - (5 * 60 * 1000);
        
        if (refreshTime > 0) {
            this.refreshTimeout = setTimeout(() => this.refreshToken(), refreshTime);
        } else {
            this.refreshToken();
        }
    }

    async login(credentials) {
        try {
            const response = await apiRequest('/api/auth/login', {
                method: 'POST',
                body: JSON.stringify(credentials)
            });

            if (response && response.token) {
                this.token = response.token;
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
        try {
            const response = await apiRequest('/api/auth/refresh', {
                method: 'POST'
            });

            if (response && response.token) {
                this.token = response.token;
                localStorage.setItem('token', this.token);
                this.setupTokenRefresh();
                return true;
            }
            return false;
        } catch (error) {
            this.logout();
            return false;
        }
    }

    async logout() {
        try {
            await apiRequest('/api/auth/logout', {
                method: 'POST'
            });
        } catch (error) {
            // Ignora erros no logout
        } finally {
            if (this.refreshTimeout) {
                clearTimeout(this.refreshTimeout);
            }
            this.token = null;
            this.tokenExpirationTime = null;
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