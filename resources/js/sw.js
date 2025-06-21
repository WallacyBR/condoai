// Service Worker para CondoAI PWA
const CACHE_NAME = 'condoai-v1';
const STATIC_CACHE = 'condoai-static-v1';
const DYNAMIC_CACHE = 'condoai-dynamic-v1';

// URLs para cache estático
const STATIC_URLS = [
    '/',
    '/offline',
    '/manifest.json'
];

// Instalação do Service Worker
self.addEventListener('install', (event) => {
    console.log('Service Worker instalado');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('Cache estático aberto');
                return cache.addAll(STATIC_URLS);
            })
            .catch((error) => {
                console.error('Erro ao instalar cache estático:', error);
            })
    );
    self.skipWaiting();
});

// Ativação do Service Worker
self.addEventListener('activate', (event) => {
    console.log('Service Worker ativado');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                        console.log('Removendo cache antigo:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Interceptação de requisições
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Estratégia para requisições da API
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cache da resposta da API
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(DYNAMIC_CACHE).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // Fallback para cache quando offline
                    return caches.match(request);
                })
        );
        return;
    }

    // Estratégia para assets estáticos
    if (request.destination === 'image' || 
        request.destination === 'style' || 
        request.destination === 'script' ||
        request.destination === 'font') {
        event.respondWith(
            caches.match(request)
                .then((response) => {
                    return response || fetch(request)
                        .then((fetchResponse) => {
                            const responseClone = fetchResponse.clone();
                            caches.open(DYNAMIC_CACHE).then((cache) => {
                                cache.put(request, responseClone);
                            });
                            return fetchResponse;
                        });
                })
        );
        return;
    }

    // Estratégia para páginas HTML
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const responseClone = response.clone();
                    caches.open(DYNAMIC_CACHE).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    return caches.match('/offline');
                })
        );
        return;
    }

    // Estratégia padrão: Network First
    event.respondWith(
        fetch(request)
            .then((response) => {
                const responseClone = response.clone();
                caches.open(DYNAMIC_CACHE).then((cache) => {
                    cache.put(request, responseClone);
                });
                return response;
            })
            .catch(() => {
                return caches.match(request);
            })
    );
});

// Gerenciamento de notificações push
self.addEventListener('push', (event) => {
    console.log('Push notification recebida');
    
    const options = {
        body: event.data ? event.data.text() : 'Nova notificação do CondoAI',
        icon: '/pwa-192x192.png',
        badge: '/pwa-192x192.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Ver',
                icon: '/pwa-192x192.png'
            },
            {
                action: 'close',
                title: 'Fechar',
                icon: '/pwa-192x192.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('CondoAI', options)
    );
});

// Clique em notificação
self.addEventListener('notificationclick', (event) => {
    console.log('Notificação clicada');
    
    event.notification.close();

    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

// Sincronização em background
self.addEventListener('sync', (event) => {
    console.log('Sincronização em background:', event.tag);
    
    if (event.tag === 'background-sync') {
        event.waitUntil(
            // Aqui você pode implementar sincronização de dados offline
            console.log('Sincronizando dados em background...')
        );
    }
}); 