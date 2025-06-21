import { ref, onMounted, onUnmounted } from 'vue';
import { registerSW } from 'virtual:pwa-register';

export function usePWA() {
    const isInstalled = ref(false);
    const isOnline = ref(navigator.onLine);
    const updateSW = ref(null);
    const needRefresh = ref(false);
    const offlineReady = ref(false);
    const swRegistration = ref(null);

    // Registrar Service Worker
    const registerServiceWorker = () => {
        if ('serviceWorker' in navigator) {
            try {
                updateSW.value = registerSW({
                    onNeedRefresh() {
                        needRefresh.value = true;
                        console.log('Atualização do Service Worker disponível');
                    },
                    onOfflineReady() {
                        offlineReady.value = true;
                        console.log('Aplicação pronta para uso offline');
                    },
                    onRegistered(registration) {
                        swRegistration.value = registration;
                        console.log('Service Worker registrado:', registration);
                    },
                    onRegisterError(error) {
                        console.error('Erro ao registrar Service Worker:', error);
                    },
                    immediate: true
                });
            } catch (error) {
                console.error('Erro ao configurar Service Worker:', error);
            }
        } else {
            console.warn('Service Worker não suportado neste navegador');
        }
    };

    // Verificar se o app está instalado
    const checkInstallation = () => {
        if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
            isInstalled.value = true;
            console.log('Aplicação instalada como PWA');
        }
    };

    // Gerenciar estado online/offline
    const handleOnline = () => {
        isOnline.value = true;
        console.log('Conexão restaurada');
    };

    const handleOffline = () => {
        isOnline.value = false;
        console.log('Conexão perdida');
    };

    // Solicitar permissão para notificações
    const requestNotificationPermission = async () => {
        if ('Notification' in window) {
            try {
                const permission = await Notification.requestPermission();
                console.log('Permissão de notificação:', permission);
                return permission === 'granted';
            } catch (error) {
                console.error('Erro ao solicitar permissão de notificação:', error);
                return false;
            }
        }
        return false;
    };

    // Enviar notificação local
    const sendNotification = (title, options = {}) => {
        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                const notification = new Notification(title, {
                    icon: '/pwa-192x192.png',
                    badge: '/pwa-192x192.png',
                    ...options
                });

                notification.onclick = () => {
                    window.focus();
                    notification.close();
                };

                return notification;
            } catch (error) {
                console.error('Erro ao enviar notificação:', error);
                return null;
            }
        }
        return null;
    };

    // Instalar PWA
    const installPWA = () => {
        if (window.deferredPrompt) {
            try {
                window.deferredPrompt.prompt();
                window.deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('PWA instalado com sucesso');
                        isInstalled.value = true;
                    } else {
                        console.log('Instalação do PWA cancelada pelo usuário');
                    }
                    window.deferredPrompt = null;
                });
            } catch (error) {
                console.error('Erro ao instalar PWA:', error);
            }
        } else {
            console.log('Prompt de instalação não disponível');
        }
    };

    // Atualizar Service Worker
    const updateServiceWorker = () => {
        if (updateSW.value) {
            try {
                updateSW.value();
                needRefresh.value = false;
                console.log('Service Worker atualizado');
            } catch (error) {
                console.error('Erro ao atualizar Service Worker:', error);
            }
        }
    };

    // Sincronizar dados em background
    const syncInBackground = () => {
        if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
            try {
                navigator.serviceWorker.ready.then((registration) => {
                    registration.sync.register('background-sync');
                    console.log('Sincronização em background registrada');
                });
            } catch (error) {
                console.error('Erro ao registrar sincronização em background:', error);
            }
        }
    };

    // Verificar conectividade
    const checkConnectivity = () => {
        return new Promise((resolve) => {
            const timeout = setTimeout(() => {
                console.log('Timeout na verificação de conectividade');
                resolve(false);
            }, 5000);

            fetch('/api/health', { 
                method: 'HEAD',
                cache: 'no-cache'
            })
                .then(() => {
                    clearTimeout(timeout);
                    console.log('Conectividade verificada com sucesso');
                    resolve(true);
                })
                .catch((error) => {
                    clearTimeout(timeout);
                    console.log('Erro na verificação de conectividade:', error);
                    resolve(false);
                });
        });
    };

    // Limpar cache do Service Worker
    const clearCache = async () => {
        if ('caches' in window) {
            try {
                const cacheNames = await caches.keys();
                await Promise.all(
                    cacheNames.map(cacheName => caches.delete(cacheName))
                );
                console.log('Cache limpo com sucesso');
                return true;
            } catch (error) {
                console.error('Erro ao limpar cache:', error);
                return false;
            }
        }
        return false;
    };

    // Inicializar PWA
    const initPWA = () => {
        registerServiceWorker();
        checkInstallation();

        // Event listeners para online/offline
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        // Event listener para instalação
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPrompt = e;
            console.log('Prompt de instalação disponível');
        });

        // Event listener para app instalado
        window.addEventListener('appinstalled', () => {
            isInstalled.value = true;
            console.log('PWA instalado');
        });

        // Event listener para mudanças no Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                console.log('Mensagem do Service Worker:', event.data);
            });
        }
    };

    // Cleanup
    const cleanup = () => {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
    };

    onMounted(() => {
        initPWA();
    });

    onUnmounted(() => {
        cleanup();
    });

    return {
        isInstalled,
        isOnline,
        needRefresh,
        offlineReady,
        swRegistration,
        requestNotificationPermission,
        sendNotification,
        installPWA,
        updateServiceWorker,
        syncInBackground,
        checkConnectivity,
        clearCache
    };
} 