// Service Worker customizado para PrediHub
const CACHE_NAME = 'predihub-v1';
const STATIC_CACHE = 'predihub-static-v1';
const DYNAMIC_CACHE = 'predihub-dynamic-v1';

// Placeholder para injeção do manifest pelo Workbox
self.__WB_MANIFEST;

// Arquivos para cache estático
const STATIC_FILES = [
    '/',
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/favicon.ico',
    '/pwa-192x192.png',
    '/pwa-512x512.png'
];

// Instalação do Service Worker
self.addEventListener('install', (event) => {
    console.log('Service Worker instalando...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('Cache estático aberto');
                return cache.addAll(STATIC_FILES);
            })
            .catch((error) => {
                console.error('Erro ao instalar cache estático:', error);
            })
    );
    
    // Ativar imediatamente
    self.skipWaiting();
});

// Ativação do Service Worker
self.addEventListener('activate', (event) => {
    console.log('Service Worker ativando...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                            console.log('Removendo cache antigo:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker ativado');
                return self.clients.claim();
            })
    );
});

// Interceptação de requisições
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Ignorar requisições para APIs externas ou que não devem ser cacheadas
    if (request.method !== 'GET' || 
        url.pathname.startsWith('/api/') ||
        url.pathname.includes('chrome-extension') ||
        url.pathname.includes('__webpack_hmr')) {
        return;
    }
    
    event.respondWith(
        caches.match(request)
            .then((response) => {
                // Se encontrou no cache, retorna
                if (response) {
                    return response;
                }
                
                // Se não encontrou, busca na rede
                return fetch(request)
                    .then((networkResponse) => {
                        // Se a resposta é válida, adiciona ao cache dinâmico
                        if (networkResponse && networkResponse.status === 200) {
                            const responseClone = networkResponse.clone();
                            caches.open(DYNAMIC_CACHE)
                                .then((cache) => {
                                    cache.put(request, responseClone);
                                });
                        }
                        return networkResponse;
                    })
                    .catch(() => {
                        // Se falhou na rede e é uma página, retorna página offline
                        if (request.destination === 'document') {
                            return caches.match('/offline');
                        }
                        return new Response('Offline', { status: 503 });
                    });
            })
    );
});

// Sincronização em background
self.addEventListener('sync', (event) => {
    if (event.tag === 'background-sync') {
        console.log('Sincronização em background iniciada');
        event.waitUntil(doBackgroundSync());
    }
});

// Função para sincronização em background
async function doBackgroundSync() {
    try {
        // Aqui você pode implementar a lógica de sincronização
        console.log('Sincronização em background concluída');
    } catch (error) {
        console.error('Erro na sincronização em background:', error);
    }
}

// Tratamento de mensagens
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys()
                .then((cacheNames) => {
                    return Promise.all(
                        cacheNames.map((cacheName) => caches.delete(cacheName))
                    );
                })
        );
    }
});

// Tratamento de erros
self.addEventListener('error', (event) => {
    console.error('Erro no Service Worker:', event.error);
});

self.addEventListener('unhandledrejection', (event) => {
    console.error('Promise rejeitada no Service Worker:', event.reason);
}); 