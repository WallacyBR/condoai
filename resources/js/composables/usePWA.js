import { ref, onMounted, onUnmounted } from 'vue';
import { registerSW } from 'virtual:pwa-register';

export function usePWA() {
    const isInstalled = ref(false);
    const isOnline = ref(navigator.onLine);
    const updateSW = ref(null);
    const needRefresh = ref(false);
    const offlineReady = ref(false);

    // Registrar Service Worker
    const registerServiceWorker = () => {
        if ('serviceWorker' in navigator) {
            updateSW.value = registerSW({
                onNeedRefresh() {
                    needRefresh.value = true;
                },
                onOfflineReady() {
                    offlineReady.value = true;
                },
                onRegistered(swRegistration) {
                    console.log('Service Worker registrado:', swRegistration);
                },
                onRegisterError(error) {
                    console.error('Erro ao registrar Service Worker:', error);
                }
            });
        }
    };

    // Verificar se o app está instalado
    const checkInstallation = () => {
        if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
            isInstalled.value = true;
        }
    };

    // Gerenciar estado online/offline
    const handleOnline = () => {
        isOnline.value = true;
    };

    const handleOffline = () => {
        isOnline.value = false;
    };

    // Solicitar permissão para notificações
    const requestNotificationPermission = async () => {
        if ('Notification' in window) {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        }
        return false;
    };

    // Enviar notificação local
    const sendNotification = (title, options = {}) => {
        if ('Notification' in window && Notification.permission === 'granted') {
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
        }
    };

    // Instalar PWA
    const installPWA = () => {
        if (window.deferredPrompt) {
            window.deferredPrompt.prompt();
            window.deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('PWA instalado com sucesso');
                    isInstalled.value = true;
                }
                window.deferredPrompt = null;
            });
        }
    };

    // Atualizar Service Worker
    const updateServiceWorker = () => {
        if (updateSW.value) {
            updateSW.value();
            needRefresh.value = false;
        }
    };

    // Sincronizar dados em background
    const syncInBackground = () => {
        if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
            navigator.serviceWorker.ready.then((registration) => {
                registration.sync.register('background-sync');
            });
        }
    };

    // Verificar conectividade
    const checkConnectivity = () => {
        return new Promise((resolve) => {
            const timeout = setTimeout(() => {
                resolve(false);
            }, 5000);

            fetch('/api/health', { method: 'HEAD' })
                .then(() => {
                    clearTimeout(timeout);
                    resolve(true);
                })
                .catch(() => {
                    clearTimeout(timeout);
                    resolve(false);
                });
        });
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
        });

        // Event listener para app instalado
        window.addEventListener('appinstalled', () => {
            isInstalled.value = true;
            console.log('PWA instalado');
        });
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
        requestNotificationPermission,
        sendNotification,
        installPWA,
        updateServiceWorker,
        syncInBackground,
        checkConnectivity
    };
} 