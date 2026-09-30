import api from '../utils/api';
import { useUser } from '../composables/useUser';
import { useTenantStore } from '../stores/tenant';

// Lazy import do router para evitar dependência circular
let router = null;
const getRouter = async () => {
  if (!router) {
    const routerModule = await import('../router');
    router = routerModule.default;
  }
  return router;
};

class AuthService {
    constructor() {
        this.token = localStorage.getItem('token');
        this.refreshTimeout = null;
        this.tokenExpirationTime = null;
        this.isRefreshing = false; // Flag para evitar múltiplos refreshes simultâneos
        this.refreshPromise = null; // Promise para sincronizar múltiplas tentativas de refresh
        const { setUser } = useUser();
        this.setUser = setUser;

        if (this.token) {
            this.setupTokenRefresh();
            this.fetchCurrentUser();
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
            const response = await api.post('/auth/login', credentials);

            if (response.data && response.data.authorization && response.data.authorization.token) {
                this.token = response.data.authorization.token;
                localStorage.setItem('token', this.token);
                this.setupTokenRefresh();

                if (response.data.user) {
                    this.setUser(response.data.user);
                    this.hydrateTenant(response.data.user);
                } else {
                    await this.fetchCurrentUser();
                }
                
                return response.data;
            }
            throw new Error('Token não recebido no login');
        } catch (error) {
            throw error;
        }
    }

    async refreshToken() {
        // Se já há um refresh em andamento, aguardar o resultado
        if (this.refreshPromise) {
            console.log('Refresh já em andamento, aguardando resultado...');
            return await this.refreshPromise;
        }

        // Evitar múltiplos refreshes simultâneos
        if (this.isRefreshing) {
            console.log('Refresh já em andamento, aguardando...');
            return false;
        }

        this.isRefreshing = true;
        console.log('Iniciando refresh do token...');

        // Criar promise para sincronizar múltiplas tentativas
        this.refreshPromise = this._performRefresh();
        
        try {
            return await this.refreshPromise;
        } finally {
            this.refreshPromise = null;
        }
    }

    async _performRefresh() {
        try {
            const response = await api.post('/auth/refresh');

            if (response.data && response.data.authorization && response.data.authorization.token) {
                this.token = response.data.authorization.token;
                localStorage.setItem('token', this.token);
                this.setupTokenRefresh();
                await this.fetchCurrentUser();
                console.log('Token refreshado com sucesso');
                return true;
            }
            console.warn('Resposta inválida no refresh do token');
            await this._handleAuthFailure();
            return false;
        } catch (error) {
            console.error('Erro no refresh do token:', error);
            await this._handleAuthFailure();
            return false;
        } finally {
            this.isRefreshing = false;
        }
    }

    hydrateTenant(user) {
        const tenantStore = useTenantStore();
        return tenantStore.hydrate(user);
    }

    async fetchCurrentUser() {
        try {
            const response = await api.get('/auth/me');
            this.setUser(response.data);
            return this.hydrateTenant(response.data);
        } catch (error) {
            console.error('Erro ao obter dados do usuário:', error);
            return { needsSelection: true };
        }
    }

    async _handleAuthFailure() {
        console.log('Falha na autenticação, limpando estado...');
        if (this.refreshTimeout) {
            clearTimeout(this.refreshTimeout);
        }
        this.token = null;
        this.tokenExpirationTime = null;
        this.isRefreshing = false;
        this.refreshPromise = null;
        localStorage.removeItem('token');
        this.setUser(null);

        const tenantStore = useTenantStore();
        tenantStore.clear();
        tenantStore.setMemberships([]);
        
        const routerInstance = await getRouter();
        routerInstance.push({ name: 'login' });
    }

    async logout() {
        console.log('Fazendo logout...');
        
        // Verificar se deve fazer chamada para API
        const shouldCallLogoutApi = this.token && this.isAuthenticated();
        
        if (shouldCallLogoutApi) {
            try {
                await api.post('/auth/logout');
                console.log('Logout na API realizado com sucesso');
            } catch (error) {
                console.error('Erro no logout (ignorado, continuando com logout local):', error);
            }
        } else {
            console.log('Token inválido/expirado, fazendo apenas logout local');
        }
        
        // Usar método centralizado para limpeza
        await this._handleAuthFailure();
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
