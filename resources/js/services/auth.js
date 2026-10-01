import api from '../utils/api';
import { useUser } from '../composables/useUser';
import { useTenantStore } from '../stores/tenant';
import {
    markTenantHydrationPending,
    resolveTenantHydration,
} from '../utils/tenant';
import { getPasskeyCredential } from '../utils/webauthn';

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
            markTenantHydrationPending();
            this.setupTokenRefresh();
            this.fetchCurrentUser().finally(() => resolveTenantHydration());
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
            this.logout();
            return;
        }

        this.tokenExpirationTime = decoded.exp * 1000;
        const now = Date.now();
        const timeUntilExpiry = this.tokenExpirationTime - now;
        
        // Se o token já expirou, fazer refresh imediatamente
        if (timeUntilExpiry <= 0) {
            this.refreshToken();
            return;
        }
        
        // Refresh 5 minutos antes da expiração
        const refreshTime = timeUntilExpiry - (5 * 60 * 1000);
        
        if (refreshTime > 0) {
            this.refreshTimeout = setTimeout(() => this.refreshToken(), refreshTime);
        } else {
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
                markTenantHydrationPending();

                try {
                    if (response.data.user) {
                        this.setUser(response.data.user);
                        this.hydrateTenant(response.data.user);
                    } else {
                        await this.fetchCurrentUser();
                    }
                } finally {
                    resolveTenantHydration();
                }

                this.syncCurrentCondominium(response.data);
                
                return response.data;
            }
            throw new Error('Token não recebido no login');
        } catch (error) {
            throw error;
        }
    }

    async loginWithPasskey(email = '') {
        const optionsResponse = await api.post('/auth/passkeys/login/options', { email: email || null });
        const credential = await getPasskeyCredential(optionsResponse.data.publicKey);
        const response = await api.post('/auth/passkeys/login/verify', {
            token: optionsResponse.data.token,
            credential
        });

        if (response.data && response.data.authorization && response.data.authorization.token) {
            this.token = response.data.authorization.token;
            localStorage.setItem('token', this.token);
            this.setupTokenRefresh();
            await this.fetchCurrentUser();
            this.syncCurrentCondominium(response.data);

            return response.data;
        }

        throw new Error('Token não recebido no login por passkey');
    }

    async refreshToken() {
        // Se já há um refresh em andamento, aguardar o resultado
        if (this.refreshPromise) {
            return await this.refreshPromise;
        }

        // Evitar múltiplos refreshes simultâneos
        if (this.isRefreshing) {
            return false;
        }

        this.isRefreshing = true;

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
                this.syncCurrentCondominium(response.data);
                await this.fetchCurrentUser();
                return true;
            }
            await this._handleAuthFailure();
            return false;
        } catch (error) {
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
            this.syncCurrentCondominium({ user: response.data });
            return this.hydrateTenant(response.data);
        } catch (error) {
            console.error('Erro ao obter dados do usuário:', error);
            this.setUser(null);
            return { needsSelection: true };
        }
    }

    syncCurrentCondominium(payload) {
        const condominium = payload?.current_condominium || payload?.user?.condominium;

        if (condominium) {
            localStorage.setItem('selectedCondominium', condominium.id);
            localStorage.setItem('selectedCondominiumName', condominium.name);
        }
    }

    async _handleAuthFailure() {
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
        tenantStore.resetMembershipCatalog();
        resolveTenantHydration();
        
        const routerInstance = await getRouter();
        routerInstance.push({ name: 'login' });
    }

    async logout() {
        // Verificar se deve fazer chamada para API
        const shouldCallLogoutApi = this.token && this.isAuthenticated();
        
        if (shouldCallLogoutApi) {
            try {
                await api.post('/auth/logout');
            } catch (error) {
                // O logout local deve continuar mesmo se a API recusar ou estiver indisponível.
            }
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
    async fetchCurrentCondominium() {
        try {
            const response = await api.get('/auth/current-condominium');
            if (response.data && response.data.current_condominium) {
                localStorage.setItem('selectedCondominium', response.data.current_condominium.id);
                localStorage.setItem('selectedCondominiumName', response.data.current_condominium.name);
                return response.data.current_condominium;
            }
            return null;
        } catch (error) {
            return null;
        }
    }
}

export default new AuthService();
