// Utilitário para gerenciamento de cache
class CacheManager {
    constructor() {
        this.cacheNames = {
            static: 'predihub-static-v1',
            dynamic: 'predihub-dynamic-v1',
            api: 'predihub-api-v1'
        };
    }

    // Verificar se o cache está disponível
    isCacheAvailable() {
        return 'caches' in window;
    }

    // Abrir um cache específico
    async openCache(cacheName) {
        if (!this.isCacheAvailable()) {
            throw new Error('Cache API não suportada');
        }
        return await caches.open(cacheName);
    }

    // Adicionar recursos ao cache
    async addToCache(cacheName, resources) {
        try {
            const cache = await this.openCache(cacheName);
            if (Array.isArray(resources)) {
                await cache.addAll(resources);
            } else {
                await cache.add(resources);
            }
            console.log(`Recursos adicionados ao cache: ${cacheName}`);
            return true;
        } catch (error) {
            console.error(`Erro ao adicionar ao cache ${cacheName}:`, error);
            return false;
        }
    }

    // Buscar um recurso no cache
    async getFromCache(request) {
        if (!this.isCacheAvailable()) {
            return null;
        }

        try {
            const cacheNames = await caches.keys();
            for (const cacheName of cacheNames) {
                const cache = await caches.open(cacheName);
                const response = await cache.match(request);
                if (response) {
                    return response;
                }
            }
            return null;
        } catch (error) {
            console.error('Erro ao buscar no cache:', error);
            return null;
        }
    }

    // Salvar resposta no cache
    async putInCache(request, response, cacheName = this.cacheNames.dynamic) {
        if (!this.isCacheAvailable()) {
            return false;
        }

        try {
            const cache = await this.openCache(cacheName);
            await cache.put(request, response.clone());
            console.log('Resposta salva no cache:', cacheName);
            return true;
        } catch (error) {
            console.error('Erro ao salvar no cache:', error);
            return false;
        }
    }

    // Limpar cache específico
    async clearCache(cacheName) {
        if (!this.isCacheAvailable()) {
            return false;
        }

        try {
            await caches.delete(cacheName);
            console.log(`Cache limpo: ${cacheName}`);
            return true;
        } catch (error) {
            console.error(`Erro ao limpar cache ${cacheName}:`, error);
            return false;
        }
    }

    // Limpar todos os caches
    async clearAllCaches() {
        if (!this.isCacheAvailable()) {
            return false;
        }

        try {
            const cacheNames = await caches.keys();
            await Promise.all(
                cacheNames.map(cacheName => caches.delete(cacheName))
            );
            console.log('Todos os caches foram limpos');
            return true;
        } catch (error) {
            console.error('Erro ao limpar todos os caches:', error);
            return false;
        }
    }

    // Obter informações do cache
    async getCacheInfo() {
        if (!this.isCacheAvailable()) {
            return null;
        }

        try {
            const cacheNames = await caches.keys();
            const cacheInfo = {};

            for (const cacheName of cacheNames) {
                const cache = await caches.open(cacheName);
                const keys = await cache.keys();
                cacheInfo[cacheName] = {
                    name: cacheName,
                    size: keys.length,
                    keys: keys.map(key => key.url)
                };
            }

            return cacheInfo;
        } catch (error) {
            console.error('Erro ao obter informações do cache:', error);
            return null;
        }
    }

    // Estratégia Cache First
    async cacheFirst(request, cacheName = this.cacheNames.dynamic) {
        try {
            // Tentar buscar no cache primeiro
            const cachedResponse = await this.getFromCache(request);
            if (cachedResponse) {
                console.log('Resposta encontrada no cache');
                return cachedResponse;
            }

            // Se não estiver no cache, buscar na rede
            const networkResponse = await fetch(request);
            if (networkResponse.ok) {
                // Salvar no cache para uso futuro
                await this.putInCache(request, networkResponse, cacheName);
            }

            return networkResponse;
        } catch (error) {
            console.error('Erro na estratégia Cache First:', error);
            throw error;
        }
    }

    // Estratégia Network First
    async networkFirst(request, cacheName = this.cacheNames.dynamic) {
        try {
            // Tentar buscar na rede primeiro
            const networkResponse = await fetch(request);
            if (networkResponse.ok) {
                // Salvar no cache
                await this.putInCache(request, networkResponse, cacheName);
                return networkResponse;
            }
        } catch (error) {
            console.log('Rede indisponível, tentando cache');
        }

        // Se a rede falhar, tentar o cache
        const cachedResponse = await this.getFromCache(request);
        if (cachedResponse) {
            console.log('Resposta encontrada no cache (fallback)');
            return cachedResponse;
        }

        throw new Error('Recurso não disponível online nem offline');
    }

    // Estratégia Stale While Revalidate
    async staleWhileRevalidate(request, cacheName = this.cacheNames.dynamic) {
        try {
            // Buscar no cache imediatamente
            const cachedResponse = await this.getFromCache(request);
            
            // Buscar na rede em background para atualizar o cache
            const networkPromise = fetch(request).then(async (response) => {
                if (response.ok) {
                    await this.putInCache(request, response, cacheName);
                }
                return response;
            }).catch(error => {
                console.error('Erro ao atualizar cache em background:', error);
            });

            // Retornar cache se disponível, senão aguardar rede
            if (cachedResponse) {
                console.log('Retornando cache enquanto atualiza em background');
                return cachedResponse;
            }

            return await networkPromise;
        } catch (error) {
            console.error('Erro na estratégia Stale While Revalidate:', error);
            throw error;
        }
    }
}

// Instância singleton
const cacheManager = new CacheManager();

export default cacheManager; 